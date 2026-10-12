<?php

namespace Tests\Feature;

use App\Imports\PrbImport;
use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\Pengguna;
use App\Models\RiwayatPengambilanObatPrb;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrbEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama' => 'Admin Penguji',
            'email' => 'admin@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Langkah 31: Uji alur end-to-end
     * 1. Import data PRB
     * 2. Tandai riwayat sebagai jatuh tempo hari ini/terlambat
     * 3. Buka dashboard pengingat
     * 4. Hubungi via WA (tandai diingatkan)
     * 5. Catat pengambilan obat baru (dengan kategori obat PRB & Non-PRB) dan tentukan jatuh tempo berikutnya
     * 6. Pastikan pasien hilang dari dashboard pengingat begitu tanggal_diambil terisi
     */
    public function test_complete_prb_end_to_end_lifecycle(): void
    {
        // === LANGKAH 1: Simulasi Import Data PRB ===
        $import = new PrbImport;
        $import->array([
            [
                'nik' => '3507111122220001',
                'nama' => 'Ibu Siti Khadijah',
                'no_index' => 'RM-PRB-001',
                'no_kartu' => '0001234567890',
                'tanggal' => Carbon::now()->subDays(30)->format('d-m-Y'),
                'tanggal_lahir' => '15-05-1965',
                'jk' => 'P',
                'hp' => '08123456789',
                'alamat' => 'Jl. Merpati Putih No. 12',
                'kelurahan' => 'Kepanjen',
                'kecamatan' => 'Kepanjen',
                'kota' => 'Malang',
                'keluhan' => 'Ambil obat PRB rutin',
            ],
        ]);

        $this->assertEquals(1, $import->summary['pasien_baru']);
        $this->assertEquals(1, $import->summary['riwayat_baru']);

        $pasien = Pasien::where('nik', '3507111122220001')->first();
        $this->assertNotNull($pasien);
        $this->assertEquals('Ibu Siti Khadijah', $pasien->nama);

        $pasienPrb = PasienPrb::where('pasien_id', $pasien->id)->first();
        $this->assertNotNull($pasienPrb);
        $this->assertEquals('aktif', $pasienPrb->status_keaktifan);

        // === LANGKAH 2: Tandai riwayat sebagai jatuh tempo (misal terlambat 2 hari) ===
        // Buat jadwal pengambilan obat yang sudah lewat tanggal jatuh temponya
        $riwayatJatuhTempo = RiwayatPengambilanObatPrb::create([
            'pasien_prb_id' => $pasienPrb->id,
            'tanggal_jatuh_tempo' => Carbon::now()->subDays(2)->toDateString(),
            'tanggal_diambil' => null, // Belum diambil
            'sumber' => 'manual',
            'dicatat_oleh' => $this->admin->id,
        ]);

        // Pastikan accessor model bekerja sesuai spesifikasi
        $this->assertEquals('Belum Ambil (Terlambat)', $riwayatJatuhTempo->status['label']);
        $this->assertEquals(2, $riwayatJatuhTempo->selisih_hari);

        // === LANGKAH 3: Buka Dashboard Pengingat WA ===
        $responseDashboard = $this->actingAs($this->admin)->get(route('admin.prb.pengingat'));
        $responseDashboard->assertStatus(200);
        $responseDashboard->assertSee('Ibu Siti Khadijah');
        $responseDashboard->assertSee('3507111122220001');
        $responseDashboard->assertSee('Telat 2 Hari');
        // Pastikan nomor WhatsApp ternormalisasi ke format 62xxx
        $responseDashboard->assertSee('wa.me/628123456789');

        // === LANGKAH 4: Hubungi via WA (Tandai sudah diingatkan) ===
        $this->assertNull($riwayatJatuhTempo->terakhir_diingatkan);

        $responseTandai = $this->actingAs($this->admin)->postJson(
            route('admin.prb.pengingat.tandai', $riwayatJatuhTempo->id)
        );

        $responseTandai->assertStatus(200);
        $responseTandai->assertJson(['success' => true]);

        $riwayatJatuhTempo->refresh();
        $this->assertNotNull($riwayatJatuhTempo->terakhir_diingatkan);

        // === LANGKAH 5: Catat Pengambilan Obat Baru (Obat PRB & Non-PRB) + Jatuh Tempo Berikutnya ===
        $tanggalAmbilHariIni = Carbon::now()->toDateString();
        $tanggalJatuhTempoBerikutnya = Carbon::now()->addDays(30)->toDateString();

        $responseCatat = $this->actingAs($this->admin)->post(
            route('admin.prb.riwayat.store', $pasienPrb->id),
            [
                'tanggal_diambil' => $tanggalAmbilHariIni,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempoBerikutnya,
                'obat' => [
                    [
                        'nama' => 'Amlodipine 10mg',
                        'kategori' => 'prb',
                    ],
                    [
                        'nama' => 'Metformin 500mg',
                        'kategori' => 'prb',
                    ],
                    [
                        'nama' => 'Vitamin B Kompleks',
                        'kategori' => 'non_prb',
                    ],
                ],
            ]
        );

        $responseCatat->assertRedirect(route('admin.prb.show', $pasienPrb->id));
        $responseCatat->assertSessionHas('success');

        // Verifikasi riwayat yang sebelumnya pending kini terisi tanggal_diambil
        $riwayatJatuhTempo->refresh();
        $this->assertEquals($tanggalAmbilHariIni, $riwayatJatuhTempo->tanggal_diambil->toDateString());

        // Verifikasi rincian obat tersimpan sesuai kategori
        $this->assertCount(3, $riwayatJatuhTempo->obatPrb);
        $this->assertEquals(2, $riwayatJatuhTempo->obatPrb()->where('kategori', 'prb')->count());
        $this->assertEquals(1, $riwayatJatuhTempo->obatPrb()->where('kategori', 'non_prb')->count());

        // Verifikasi riwayat siklus berikutnya dibuat dengan benar
        $riwayatBerikutnya = RiwayatPengambilanObatPrb::where('pasien_prb_id', $pasienPrb->id)
            ->whereNull('tanggal_diambil')
            ->first();

        $this->assertNotNull($riwayatBerikutnya);
        $this->assertEquals($tanggalJatuhTempoBerikutnya, $riwayatBerikutnya->tanggal_jatuh_tempo->toDateString());
        $this->assertEquals('Belum Jatuh Tempo', $riwayatBerikutnya->status['label']);

        // === LANGKAH 6: Pastikan Pasien HILANG dari Dashboard Pengingat ===
        $responseDashboardAfter = $this->actingAs($this->admin)->get(route('admin.prb.pengingat'));
        $responseDashboardAfter->assertStatus(200);
        $responseDashboardAfter->assertDontSee('Telat 2 Hari');
        $responseDashboardAfter->assertSee('Tidak ada pasien PRB yang perlu dihubungi');
    }

    /**
     * Uji fitur nonaktifkan pasien PRB dan dampaknya ke dashboard pengingat
     */
    public function test_deactivated_prb_patient_is_excluded_from_reminders(): void
    {
        $pasien = Pasien::create([
            'nik' => '3507999988880001',
            'nama' => 'Bapak Bambang Nonaktif',
            'tanggal_lahir' => '1960-01-01',
            'no_hp' => '08987654321',
        ]);

        $pasienPrb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => Carbon::now()->subMonths(2)->toDateString(),
        ]);

        // Buat riwayat terlambat
        RiwayatPengambilanObatPrb::create([
            'pasien_prb_id' => $pasienPrb->id,
            'tanggal_jatuh_tempo' => Carbon::now()->subDay()->toDateString(),
            'tanggal_diambil' => null,
            'sumber' => 'manual',
        ]);

        // Saat aktif, pasien harus muncul di dashboard pengingat
        $response1 = $this->actingAs($this->admin)->get(route('admin.prb.pengingat'));
        $response1->assertSee('Bapak Bambang Nonaktif');

        // Nonaktifkan pasien PRB
        $responseToggle = $this->actingAs($this->admin)->put(route('admin.prb.toggle-status', $pasienPrb->id));
        $responseToggle->assertRedirect();
        $this->assertEquals('tidak_aktif', $pasienPrb->fresh()->status_keaktifan);

        // Setelah dinonaktifkan, pasien TIDAK boleh muncul di pengingat meskipun telat
        $response2 = $this->actingAs($this->admin)->get(route('admin.prb.pengingat'));
        $response2->assertDontSee('Bapak Bambang Nonaktif');

        // Filter di index PRB
        $responseIndexAktif = $this->actingAs($this->admin)->get(route('admin.prb.index', ['status' => 'aktif']));
        $responseIndexAktif->assertDontSee('Bapak Bambang Nonaktif');

        $responseIndexTidakAktif = $this->actingAs($this->admin)->get(route('admin.prb.index', ['status' => 'tidak_aktif']));
        $responseIndexTidakAktif->assertSee('Bapak Bambang Nonaktif');
    }

    /**
     * Uji sinkronisasi otomatis saat pasien dinonaktifkan dari modul Prolanis utama
     */
    public function test_prb_status_syncs_when_patient_deactivated_from_prolanis(): void
    {
        $pasien = Pasien::create([
            'nik' => '3507555566660001',
            'nama' => 'Ibu Wati Sync',
            'tanggal_lahir' => '1970-03-15',
            'status_keaktifan' => 'aktif',
        ]);

        $pasienPrb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => Carbon::now()->toDateString(),
        ]);

        // Nonaktifkan dari controller Pasien Prolanis
        $this->actingAs($this->admin)->put(route('admin.pasien.toggle-status', $pasien->id), [
            'alasan' => 'Meninggal Dunia',
        ]);

        $this->assertEquals('tidak_aktif', $pasien->fresh()->status_keaktifan);
        // Status PRB juga harus otomatis sinkron menjadi tidak_aktif
        $this->assertEquals('tidak_aktif', $pasienPrb->fresh()->status_keaktifan);
    }
}
