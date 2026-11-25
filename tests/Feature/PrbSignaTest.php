<?php

namespace Tests\Feature;

use App\Models\MasterObat;
use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\Pengguna;
use App\Models\RiwayatPengambilanObatPrb;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrbSignaTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private PasienPrb $pasienPrb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama' => 'Dokter Penguji',
            'email' => 'dokter@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);

        $pasien = Pasien::create([
            'nik' => '3507123456780001',
            'nama' => 'Bapak Hartono Signa',
            'tanggal_lahir' => '1965-08-17',
            'status_keaktifan' => 'aktif',
        ]);

        $this->pasienPrb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => Carbon::now()->toDateString(),
        ]);
    }

    public function test_can_store_riwayat_with_signa_and_calculated_qty_and_auto_registers_master_obat(): void
    {
        $tanggalAmbil = Carbon::now()->toDateString();
        $tanggalJatuhTempo = Carbon::now()->addDays(30)->toDateString();

        $response = $this->actingAs($this->admin)->post(
            route('admin.prb.riwayat.store', $this->pasienPrb->id),
            [
                'tanggal_diambil' => $tanggalAmbil,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'obat' => [
                    [
                        'nama' => 'Glimepiride 2mg Baru',
                        'kategori' => 'prb',
                        'signa' => '1 x 1',
                        'jumlah_hari' => 30,
                        'jumlah_obat' => 30,
                        'aturan_minum' => 'Sebelum Makan',
                    ],
                    [
                        'nama' => 'Metformin 500mg',
                        'kategori' => 'prb',
                        'signa' => '3 x 1',
                        'jumlah_hari' => 30,
                        'jumlah_obat' => 90,
                        'aturan_minum' => 'Sesudah Makan',
                    ],
                ],
            ]
        );

        $response->assertRedirect(route('admin.prb.show', $this->pasienPrb->id));
        $response->assertSessionHas('success');

        // Verifikasi obat tersimpan lengkap dengan signa & qty
        $this->assertDatabaseHas('obat_prbs', [
            'nama_obat' => 'Glimepiride 2mg Baru',
            'kategori' => 'prb',
            'signa' => '1 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 30,
            'aturan_minum' => 'Sebelum Makan',
        ]);

        $this->assertDatabaseHas('obat_prbs', [
            'nama_obat' => 'Metformin 500mg',
            'kategori' => 'prb',
            'signa' => '3 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 90,
            'aturan_minum' => 'Sesudah Makan',
        ]);

        // Verifikasi obat baru otomatis masuk ke katalog MasterObat
        $this->assertDatabaseHas('master_obats', [
            'nama_obat' => 'Glimepiride 2mg Baru',
            'kategori' => 'prb',
            'sumber' => 'manual',
            'is_active' => true,
        ]);

        // Verifikasi tampilan show memuat signa & qty
        $showResponse = $this->actingAs($this->admin)->get(route('admin.prb.show', $this->pasienPrb->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Glimepiride 2mg Baru');
        $showResponse->assertSee('1 x 1');
        $showResponse->assertSee('30 butir');
        $showResponse->assertSee('Sebelum Makan');
        $showResponse->assertSee('3 x 1');
        $showResponse->assertSee('90 butir');
    }

    public function test_can_update_riwayat_with_modified_signa_and_qty(): void
    {
        $riwayat = RiwayatPengambilanObatPrb::create([
            'pasien_prb_id' => $this->pasienPrb->id,
            'tanggal_jatuh_tempo' => Carbon::now()->toDateString(),
            'tanggal_diambil' => Carbon::now()->toDateString(),
            'sumber' => 'manual',
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Amlodipine 5mg',
            'kategori' => 'prb',
            'signa' => '1 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 30,
            'aturan_minum' => 'Malam Hari',
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.prb.riwayat.update', $riwayat->id),
            [
                'tanggal_jatuh_tempo' => Carbon::now()->toDateString(),
                'tanggal_diambil' => Carbon::now()->toDateString(),
                'obat' => [
                    [
                        'nama' => 'Amlodipine 5mg',
                        'kategori' => 'prb',
                        'signa' => '2 x 1',
                        'jumlah_hari' => 30,
                        'jumlah_obat' => 60,
                        'aturan_minum' => 'Pagi Hari',
                    ],
                ],
            ]
        );

        $response->assertRedirect(route('admin.prb.show', $this->pasienPrb->id));

        $this->assertDatabaseHas('obat_prbs', [
            'riwayat_pengambilan_obat_prb_id' => $riwayat->id,
            'nama_obat' => 'Amlodipine 5mg',
            'signa' => '2 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 60,
            'aturan_minum' => 'Pagi Hari',
        ]);
    }

    public function test_can_download_resep_pdf_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.prb.resep.download-pdf'),
            [
                'pasien_nama' => 'Bapak Hartono Signa',
                'pasien_umur' => '59 Thn',
                'pasien_no_rm' => '3507123456780001',
                'pasien_alamat' => 'Surabaya Barat',
                'dokter' => 'dr. Diah Miryati',
                'sip' => 'SIP. 198508212011012009',
                'kota' => 'Surabaya',
                'tanggal' => '09/11/2026',
                'catatan' => 'Minum obat secara teratur.',
                'filter' => 'all',
                'format_nomor' => 'romawi',
                'obat_list' => [
                    [
                        'nama' => 'ACARBOS 50mg',
                        'kategori' => 'prb',
                        'signa' => '1 x 1 (Sesudah Makan)',
                        'jumlah_obat' => 30,
                    ],
                    [
                        'nama' => 'Trihexyphendyil 2 mg',
                        'kategori' => 'prb',
                        'signa' => '1 x 0.5',
                        'jumlah_obat' => 15,
                    ],
                ],
            ]
        );

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type', ''));
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition', ''));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_cannot_store_riwayat_when_diagnosa_is_still_lainnya(): void
    {
        $this->pasienPrb->update(['diagnosa' => 'LAINNYA']);

        $response = $this->actingAs($this->admin)->post(
            route('admin.prb.riwayat.store', $this->pasienPrb->id),
            [
                'tanggal_diambil' => Carbon::now()->toDateString(),
                'tanggal_jatuh_tempo' => Carbon::now()->addDays(30)->toDateString(),
                'kode_diagnosa' => '',
                'diagnosa' => 'LAINNYA',
            ]
        );

        $response->assertSessionHasErrors(['diagnosa']);
        $this->assertDatabaseCount('riwayat_pengambilan_obat_prbs', 0);

        $validResponse = $this->actingAs($this->admin)->post(
            route('admin.prb.riwayat.store', $this->pasienPrb->id),
            [
                'tanggal_diambil' => Carbon::now()->toDateString(),
                'tanggal_jatuh_tempo' => Carbon::now()->addDays(30)->toDateString(),
                'kode_diagnosa' => 'I10',
                'diagnosa' => 'Hipertensi',
            ]
        );

        $validResponse->assertRedirect(route('admin.prb.show', $this->pasienPrb->id));
        $this->pasienPrb->refresh();
        $this->assertEquals('Hipertensi', $this->pasienPrb->diagnosa);
    }

    public function test_cannot_update_riwayat_when_diagnosa_is_still_lainnya(): void
    {
        $this->pasienPrb->update(['diagnosa' => 'LAINNYA']);

        $riwayat = $this->pasienPrb->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => Carbon::now()->toDateString(),
            'tanggal_diambil' => Carbon::now()->toDateString(),
            'diagnosa' => 'LAINNYA',
            'sumber' => 'import',
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.prb.riwayat.update', $riwayat->id),
            [
                'tanggal_jatuh_tempo' => Carbon::now()->toDateString(),
                'tanggal_diambil' => Carbon::now()->toDateString(),
                'kode_diagnosa' => '',
                'diagnosa' => 'Lainnya',
            ]
        );

        $response->assertSessionHasErrors(['diagnosa']);

        $validResponse = $this->actingAs($this->admin)->put(
            route('admin.prb.riwayat.update', $riwayat->id),
            [
                'tanggal_jatuh_tempo' => Carbon::now()->toDateString(),
                'tanggal_diambil' => Carbon::now()->toDateString(),
                'kode_diagnosa' => 'E11.8',
                'diagnosa' => 'Diabetes melitus tipe 2 dengan komplikasi yang tidak dirinci',
            ]
        );

        $validResponse->assertRedirect(route('admin.prb.show', $this->pasienPrb->id));
        $riwayat->refresh();
        $this->assertEquals('E11.8', $riwayat->kode_diagnosa);
        $this->assertEquals('Diabetes melitus tipe 2 dengan komplikasi yang tidak dirinci', $riwayat->diagnosa);
    }

    public function test_replacing_lainnya_keeps_legacy_null_rows_as_lainnya_without_duplicating_new_diagnosa(): void
    {
        $this->pasienPrb->update(['diagnosa' => 'LAINNYA']);

        $legacy1 = $this->pasienPrb->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-09-09',
            'tanggal_diambil' => '2026-09-09',
            'kode_diagnosa' => null,
            'diagnosa' => null,
            'sumber' => 'import',
        ]);
        $legacy2 = $this->pasienPrb->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-10-09',
            'tanggal_diambil' => null,
            'kode_diagnosa' => null,
            'diagnosa' => null,
            'sumber' => 'import',
        ]);

        $this->actingAs($this->admin)->post(
            route('admin.prb.riwayat.store', $this->pasienPrb->id),
            [
                'tanggal_diambil' => '2026-11-25',
                'tanggal_jatuh_tempo' => '2026-12-25',
                'kode_diagnosa' => 'E11',
                'diagnosa' => 'Diabetes Melitus',
            ]
        )->assertRedirect(route('admin.prb.show', $this->pasienPrb->id));

        $legacy1->refresh();
        $legacy2->refresh();

        // Riwayat lama tetap berlabel LAINNYA (tidak berubah jadi Diabetes Melitus)
        $this->assertNull($legacy1->kode_diagnosa);
        $this->assertEquals('LAINNYA', $legacy1->diagnosa);
        $this->assertNull($legacy2->kode_diagnosa);
        $this->assertEquals('LAINNYA', $legacy2->diagnosa);

        // Grup diagnosa terpisah tegas antara LAINNYA dan E11 (tidak ada duplikat Diabetes Melitus)
        $groupKeys = $this->pasienPrb->riwayatPengambilan()->get()->pluck('diagnosa_group_key')->unique()->values()->all();
        sort($groupKeys);
        $this->assertEquals(['E11', 'LAINNYA'], $groupKeys);
    }
}
