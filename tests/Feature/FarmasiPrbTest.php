<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FarmasiPrbTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Farmasi',
            'email' => 'admin.farmasi@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_can_access_farmasi_prb_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.prb.farmasi.index'));

        $response->assertStatus(200);
        $response->assertSee('Farmasi PRB');
        $response->assertSee('Tampilkan Data');
        $response->assertSee('No Antrean');
        $response->assertSee('Tgl Resep');
        $response->assertSee('Type Kunjungan');
        $response->assertSee('Poli/Klinik');
        $response->assertSee('Nama Pasien');
    }

    public function test_farmasi_prb_displays_prb_patient_with_doctor_prescribed_medicine(): void
    {
        $pasien = Pasien::create([
            'nama' => 'RINAWATI',
            'nik' => '3578012345670001',
            'no_rm' => '03.0646/01',
            'alamat' => 'JL KALISARI DAMEN 62, SBY',
            'tanggal_lahir' => '1980-06-15',
            'jenis_kelamin' => 'P',
            'tipe_pasien' => 'BPJS-Jamkesda',
            'no_bpjs' => '0001234567890',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
            'diagnosa' => 'Hipertensi',
        ]);

        $tanggal = '2026-10-24';

        $riwayat = $prb->riwayatPengambilan()->create([
            'tanggal_diambil' => $tanggal,
            'tanggal_jatuh_tempo' => $tanggal,
            'catatan' => 'Pusing dan tensi tinggi',
            'dicatat_oleh' => $this->admin->id,
            'sumber' => 'manual',
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Amlodipine 10mg',
            'kategori' => 'prb',
            'signa' => '1 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 30,
            'aturan_minum' => 'Sesudah makan pagi',
            'sumber' => 'manual',
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Candesartan 8mg',
            'kategori' => 'prb',
            'signa' => '1 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 30,
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.prb.farmasi.index', ['tanggal' => $tanggal]));

        $response->assertStatus(200);
        $response->assertSee('RINAWATI');
        $response->assertSee('Admin Farmasi');
        $response->assertSee(route('admin.prb.farmasi.show', $riwayat->id));
    }

    public function test_can_view_resep_detail_json(): void
    {
        $pasien = Pasien::create([
            'nama' => 'DERRIXON YAP',
            'nik' => '3578012345670002',
            'no_rm' => '02.1768/02',
            'alamat' => 'BABATAN PANTAI UTARA 7/5',
            'tanggal_lahir' => '2018-04-10',
            'jenis_kelamin' => 'L',
            'tipe_pasien' => 'Umum',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);

        $riwayat = $prb->riwayatPengambilan()->create([
            'tanggal_diambil' => '2026-10-24',
            'tanggal_jatuh_tempo' => '2026-10-24',
            'dicatat_oleh' => $this->admin->id,
            'sumber' => 'manual',
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Paracetamol Sirup',
            'kategori' => 'non_prb',
            'signa' => '3 x 1 cth',
            'jumlah_hari' => 3,
            'jumlah_obat' => 1,
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.prb.farmasi.show', $riwayat->id));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $riwayat->id,
                'obat_prb' => [
                    [
                        'nama_obat' => 'Paracetamol Sirup',
                    ],
                ],
            ],
        ]);
    }

    public function test_can_access_kunjungan_obat_page(): void
    {
        $pasien = Pasien::create([
            'nama' => 'RINAWATI',
            'nik' => '3578012345670003',
            'no_rm' => '03.0646/01',
            'tanggal_lahir' => '1980-06-15',
            'jenis_kelamin' => 'P',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);

        $riwayat = $prb->riwayatPengambilan()->create([
            'tanggal_diambil' => '2026-10-24',
            'tanggal_jatuh_tempo' => '2026-10-24',
            'dicatat_oleh' => $this->admin->id,
            'sumber' => 'manual',
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Ambroxol 30mg',
            'kategori' => 'non_prb',
            'signa' => '3 x 1',
            'jumlah_hari' => 10,
            'jumlah_obat' => 30,
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.prb.farmasi.show', $riwayat->id));

        $response->assertStatus(200);
        $response->assertSee('Kunjungan Obat');
        $response->assertSee('Action');
        $response->assertSee('Beri Obat');
        $response->assertSee('AMBROXOL 30MG');
        $response->assertSee('Stok Kosong');
        $response->assertSee('Nota Resep / Cetak');
        $response->assertSee('Cetak Lembar Resep Dokter');
        $response->assertSee('Set Semua Ada (Lengkap)');
        $response->assertSee('Reset Semua');
        $response->assertDontSee('Total: Rp.');
        $response->assertDontSee('Stylus');
        $response->assertDontSee('Catatan Stok Kosong');
    }

    public function test_can_submit_beri_obat_lengkap(): void
    {
        $pasien = Pasien::create([
            'nama' => 'BUDI SANTOSO',
            'nik' => '3578012345670004',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);

        $riwayat = $prb->riwayatPengambilan()->create([
            'tanggal_diambil' => '2026-10-24',
            'tanggal_jatuh_tempo' => '2026-10-24',
            'dicatat_oleh' => $this->admin->id,
            'sumber' => 'manual',
        ]);

        $obat1 = $riwayat->obatPrb()->create([
            'nama_obat' => 'Amlodipine 10mg',
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.prb.farmasi.beri-obat', $riwayat->id), [
            'obat' => [
                [
                    'id' => $obat1->id,
                    'status_pemberian' => 'sudah',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.prb.farmasi.index', ['tanggal' => '2026-10-24']));

        $riwayat->refresh();
        $this->assertEquals('sudah', $riwayat->status_farmasi);
        $this->assertEquals('Sudah', $riwayat->status_farmasi_badge['label']);
    }

    public function test_can_submit_beri_obat_tidak_lengkap_karena_stok_kosong(): void
    {
        $pasien = Pasien::create([
            'nama' => 'SITI AMINAH',
            'nik' => '3578012345670005',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);

        $riwayat = $prb->riwayatPengambilan()->create([
            'tanggal_diambil' => '2026-10-24',
            'tanggal_jatuh_tempo' => '2026-10-24',
            'dicatat_oleh' => $this->admin->id,
            'sumber' => 'manual',
        ]);

        $obat1 = $riwayat->obatPrb()->create([
            'nama_obat' => 'Amlodipine 10mg',
            'sumber' => 'manual',
        ]);

        $obat2 = $riwayat->obatPrb()->create([
            'nama_obat' => 'Candesartan 16mg',
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.prb.farmasi.beri-obat', $riwayat->id), [
            'obat' => [
                [
                    'id' => $obat1->id,
                    'status_pemberian' => 'sudah',
                ],
                [
                    'id' => $obat2->id,
                    'status_pemberian' => 'kosong',
                    'catatan_farmasi' => 'Stok habis di gudang',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.prb.farmasi.index', ['tanggal' => '2026-10-24']));

        $riwayat->refresh();
        $this->assertEquals('tidak_lengkap', $riwayat->status_farmasi);
        $this->assertEquals('Sudah Diberikan Tidak Lengkap', $riwayat->status_farmasi_badge['label']);
    }
}
