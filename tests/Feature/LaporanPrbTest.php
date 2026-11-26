<?php

namespace Tests\Feature;

use App\Models\MasterObat;
use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanPrbTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Laporan',
            'email' => 'admin.laporan@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_can_view_laporan_index_with_three_reports(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.prb.laporan.index'));

        $response->assertOk();
        $response->assertSee('Laporan');
        $response->assertSee('Pasien yang Belum Ambil Obat');
        $response->assertSee('Pasien PRB Tidak Aktif');
        $response->assertSee('Pasien PRB Aktif');
    }

    public function test_belum_ambil_obat_report_filters_by_day_month_and_year(): void
    {
        $pasienNov = Pasien::create([
            'nama' => 'BUDI NOVEMBER',
            'nik' => '3578010000000001',
        ]);
        $prbNov = PasienPrb::create([
            'pasien_id' => $pasienNov->id,
            'diagnosa' => 'Hipertensi',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);
        $prbNov->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-15',
            'tanggal_diambil' => null,
            'diagnosa' => 'Hipertensi',
            'sumber' => 'manual',
        ]);

        $pasienDes = Pasien::create([
            'nama' => 'SITI DESEMBER',
            'nik' => '3578010000000002',
        ]);
        $prbDes = PasienPrb::create([
            'pasien_id' => $pasienDes->id,
            'diagnosa' => 'Diabetes Melitus',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);
        $prbDes->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-12-20',
            'tanggal_diambil' => null,
            'diagnosa' => 'Diabetes Melitus',
            'sumber' => 'manual',
        ]);

        // Sudah diambil -> tidak boleh muncul
        $pasienSudah = Pasien::create([
            'nama' => 'ANDI SUDAH AMBIL',
            'nik' => '3578010000000003',
        ]);
        $prbSudah = PasienPrb::create([
            'pasien_id' => $pasienSudah->id,
            'diagnosa' => 'Jantung',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);
        $prbSudah->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-15',
            'tanggal_diambil' => '2026-11-15',
            'diagnosa' => 'Jantung',
            'sumber' => 'manual',
        ]);

        // Filter by Hari (Tanggal)
        $resHari = $this->actingAs($this->admin)->get(route('admin.prb.laporan.belum-ambil-obat', [
            'tanggal' => '2026-11-15',
        ]));
        $resHari->assertOk();
        $resHari->assertSee('BUDI NOVEMBER');
        $resHari->assertDontSee('SITI DESEMBER');
        $resHari->assertDontSee('ANDI SUDAH AMBIL');

        // Filter by Bulan & Tahun
        $resBulan = $this->actingAs($this->admin)->get(route('admin.prb.laporan.belum-ambil-obat', [
            'bulan' => 12,
            'tahun' => 2026,
        ]));
        $resBulan->assertOk();
        $resBulan->assertSee('SITI DESEMBER');
        $resBulan->assertDontSee('BUDI NOVEMBER');

        // Filter by Tahun saja
        $resTahun = $this->actingAs($this->admin)->get(route('admin.prb.laporan.belum-ambil-obat', [
            'tahun' => 2026,
        ]));
        $resTahun->assertOk();
        $resTahun->assertSee('BUDI NOVEMBER');
        $resTahun->assertSee('SITI DESEMBER');
        $resTahun->assertDontSee('ANDI SUDAH AMBIL');
    }

    public function test_aktif_and_tidak_aktif_reports_filter_by_status_keaktifan(): void
    {
        $pasienAktif = Pasien::create([
            'nama' => 'PASIEN AKTIF PRB',
            'nik' => '3578010000000010',
        ]);
        PasienPrb::create([
            'pasien_id' => $pasienAktif->id,
            'diagnosa' => 'Hipertensi',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);

        $pasienNonaktif = Pasien::create([
            'nama' => 'PASIEN TIDAK AKTIF PRB',
            'nik' => '3578010000000011',
        ]);
        PasienPrb::create([
            'pasien_id' => $pasienNonaktif->id,
            'diagnosa' => 'Asma',
            'status_keaktifan' => 'tidak_aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);

        $resTidakAktif = $this->actingAs($this->admin)->get(route('admin.prb.laporan.tidak-aktif'));
        $resTidakAktif->assertOk();
        $resTidakAktif->assertSee('PASIEN TIDAK AKTIF PRB');
        $resTidakAktif->assertDontSee('PASIEN AKTIF PRB');

        $resAktif = $this->actingAs($this->admin)->get(route('admin.prb.laporan.aktif'));
        $resAktif->assertOk();
        $resAktif->assertSee('PASIEN AKTIF PRB');
        $resAktif->assertDontSee('PASIEN TIDAK AKTIF PRB');
    }

    public function test_can_export_all_three_reports_to_excel(): void
    {
        $pasien = Pasien::create([
            'nama' => 'PASIEN EXCEL PRB',
            'nik' => '3578010000000099',
        ]);
        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'diagnosa' => 'Hipertensi',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-11-01',
        ]);
        $prb->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-25',
            'tanggal_diambil' => null,
            'diagnosa' => 'Hipertensi',
            'sumber' => 'manual',
        ]);

        foreach ([
            route('admin.prb.laporan.belum-ambil-obat.export'),
            route('admin.prb.laporan.tidak-aktif.export'),
            route('admin.prb.laporan.aktif.export'),
        ] as $url) {
            $res = $this->actingAs($this->admin)->get($url);
            $res->assertOk();
            $this->assertStringContainsString(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                $res->headers->get('content-type', '')
            );
        }
    }

    public function test_patient_with_completed_pickup_in_same_month_is_excluded_and_legacy_pending_closes_on_store(): void
    {
        $pasienNarja = Pasien::create([
            'nama' => 'NARJA TEST',
            'nik' => '3578010000000077',
        ]);
        $prbNarja = PasienPrb::create([
            'pasien_id' => $pasienNarja->id,
            'diagnosa' => 'SLE',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);

        // Jadwal lama dengan kode_diagnosa = null & diagnosa = null (mewarisi SLE)
        $legacyPending = $prbNarja->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-18',
            'tanggal_diambil' => null,
            'kode_diagnosa' => null,
            'diagnosa' => null,
            'sumber' => 'manual',
        ]);

        // Simpan pengambilan baru dengan kode_diagnosa = M32, diagnosa = SLE
        $this->actingAs($this->admin)->post(route('admin.prb.riwayat.store', $prbNarja->id), [
            'tanggal_diambil' => '2026-11-25',
            'tanggal_jatuh_tempo' => '2026-12-25',
            'kode_diagnosa' => 'M32',
            'diagnosa' => 'SLE',
        ])->assertRedirect(route('admin.prb.show', $prbNarja->id));

        $legacyPending->refresh();
        $this->assertEquals('2026-11-25', $legacyPending->tanggal_diambil?->format('Y-m-d'));
        $this->assertEquals('M32', $legacyPending->kode_diagnosa);

        // Pasien kedua: punya baris pending Nov tapi juga sudah punya baris diambil Nov (data double lama)
        $pasienDouble = Pasien::create([
            'nama' => 'PASIEN DOUBLE NOV',
            'nik' => '3578010000000078',
        ]);
        $prbDouble = PasienPrb::create([
            'pasien_id' => $pasienDouble->id,
            'diagnosa' => 'Hipertensi',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);
        $prbDouble->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-18',
            'tanggal_diambil' => null,
            'sumber' => 'manual',
        ]);
        $prbDouble->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-25',
            'tanggal_diambil' => '2026-11-25',
            'kode_diagnosa' => 'I10',
            'diagnosa' => 'Hipertensi',
            'sumber' => 'manual',
        ]);

        $resNov = $this->actingAs($this->admin)->get(route('admin.prb.laporan.belum-ambil-obat', [
            'bulan' => 11,
            'tahun' => 2026,
        ]));
        $resNov->assertOk();
        $resNov->assertDontSee('NARJA TEST');
        $resNov->assertDontSee('PASIEN DOUBLE NOV');
    }

    public function test_non_admin_cannot_access_or_see_laporan_menu(): void
    {
        $dokter = User::create([
            'nama' => 'dr. Dokter PRB',
            'nik' => '3578010000000088',
            'password' => Hash::make('password123'),
            'peran' => 'petugas',
            'profesi' => 'Dokter',
            'is_active' => true,
        ]);

        // Sidebar tidak menampilkan link menu Laporan untuk selain admin
        $prbPage = $this->actingAs($dokter)->get(route('admin.prb.index'));
        $prbPage->assertOk();
        $prbPage->assertDontSee(route('admin.prb.laporan.index'));

        // Akses langsung ke route laporan ditolak (403)
        foreach ([
            route('admin.prb.laporan.index'),
            route('admin.prb.laporan.belum-ambil-obat'),
            route('admin.prb.laporan.belum-ambil-obat.export'),
            route('admin.prb.laporan.tidak-aktif'),
            route('admin.prb.laporan.tidak-aktif.export'),
            route('admin.prb.laporan.aktif'),
            route('admin.prb.laporan.aktif.export'),
        ] as $url) {
            $this->actingAs($dokter)->get($url)->assertForbidden();
        }
    }

    public function test_multi_diagnosa_and_multiple_visits_in_same_month_keep_untaken_schedules_visible(): void
    {
        // Kasus 1: 1 pasien punya 2 diagnosa di bulan September
        // - 10 Sep: Diabetes (E11) SUDAH diambil
        // - 20 Sep: Penyakit Jantung (I25) BELUM diambil -> harus tetap muncul!
        $pasienMulti = Pasien::create([
            'nama' => 'PASIEN MULTI DIAGNOSA',
            'nik' => '3578010000000051',
        ]);
        $prbMulti = PasienPrb::create([
            'pasien_id' => $pasienMulti->id,
            'diagnosa' => 'Diabetes Melitus',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-08-01',
        ]);
        $prbMulti->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-09-10',
            'tanggal_diambil' => '2026-09-10',
            'kode_diagnosa' => 'E11',
            'diagnosa' => 'Diabetes Melitus',
            'sumber' => 'manual',
        ]);
        $prbMulti->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-09-20',
            'tanggal_diambil' => null,
            'kode_diagnosa' => 'I25',
            'diagnosa' => 'Penyakit Jantung',
            'sumber' => 'manual',
        ]);

        // Kasus 2: 1 pasien 2x kunjungan diagnosa sama di bulan September
        // - 5 Sep: Hipertensi (I10) SUDAH diambil
        // - 25 Sep: Hipertensi (I10) jadwal baru BELUM diambil -> harus tetap muncul!
        $pasienDuaKali = Pasien::create([
            'nama' => 'PASIEN DUA KALI SEP',
            'nik' => '3578010000000052',
        ]);
        $prbDuaKali = PasienPrb::create([
            'pasien_id' => $pasienDuaKali->id,
            'diagnosa' => 'Hipertensi',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-08-01',
        ]);
        $prbDuaKali->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-09-05',
            'tanggal_diambil' => '2026-09-05',
            'kode_diagnosa' => 'I10',
            'diagnosa' => 'Hipertensi',
            'sumber' => 'manual',
        ]);
        $prbDuaKali->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-09-25',
            'tanggal_diambil' => null,
            'kode_diagnosa' => 'I10',
            'diagnosa' => 'Hipertensi',
            'sumber' => 'manual',
        ]);

        $resSep = $this->actingAs($this->admin)->get(route('admin.prb.laporan.belum-ambil-obat', [
            'bulan' => 9,
            'tahun' => 2026,
        ]));

        $resSep->assertOk();
        $resSep->assertSee('PASIEN MULTI DIAGNOSA');
        $resSep->assertSee('I25 - Penyakit Jantung');
        $resSep->assertSee('PASIEN DUA KALI SEP');
        $resSep->assertSee('25 Sep 2026');
    }

    public function test_pengingat_wa_can_filter_by_month(): void
    {
        $pasienNextMonth = Pasien::create([
            'nama' => 'BONARI BULAN DEPAN',
            'nik' => '3578010000000061',
        ]);
        $prbNext = PasienPrb::create([
            'pasien_id' => $pasienNextMonth->id,
            'diagnosa' => 'Hipertensi',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-07-24',
        ]);
        $nextMonthDate = now()->addMonth()->startOfMonth()->addDays(20);
        $prbNext->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => $nextMonthDate->toDateString(),
            'tanggal_diambil' => null,
            'sumber' => 'manual',
        ]);

        // Default (H-3 & Terlambat): belum muncul
        $resDefault = $this->actingAs($this->admin)->get(route('admin.prb.pengingat'));
        $resDefault->assertOk();
        $resDefault->assertDontSee('BONARI BULAN DEPAN');

        // Dengan filter bulan depan: muncul
        $resMonth = $this->actingAs($this->admin)->get(route('admin.prb.pengingat', [
            'bulan' => $nextMonthDate->month,
        ]));
        $resMonth->assertOk();
        $resMonth->assertSee('BONARI BULAN DEPAN');
    }

    public function test_pengingat_wa_is_synchronized_with_laporan_belum_ambil_obat(): void
    {
        $pasienSudahSusulan = Pasien::create([
            'nama' => 'NARJA SINKRON',
            'nik' => '3578010000000062',
        ]);
        $prbSudah = PasienPrb::create([
            'pasien_id' => $pasienSudahSusulan->id,
            'diagnosa' => 'SLE',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);
        $prbSudah->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-18',
            'tanggal_diambil' => null,
            'kode_diagnosa' => 'M32',
            'diagnosa' => 'SLE',
            'sumber' => 'manual',
        ]);
        $prbSudah->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-25',
            'tanggal_diambil' => '2026-11-25',
            'kode_diagnosa' => 'M32',
            'diagnosa' => 'SLE',
            'sumber' => 'manual',
        ]);

        $pasienBelum = Pasien::create([
            'nama' => 'TESKU SINKRON',
            'nik' => '3578010000000063',
        ]);
        $prbBelum = PasienPrb::create([
            'pasien_id' => $pasienBelum->id,
            'diagnosa' => 'Diabetes Melitus',
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => '2026-10-01',
        ]);
        $prbBelum->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => '2026-11-19',
            'tanggal_diambil' => null,
            'kode_diagnosa' => 'E11',
            'diagnosa' => 'Diabetes Melitus',
            'sumber' => 'manual',
        ]);

        $resPengingat = $this->actingAs($this->admin)->get(route('admin.prb.pengingat', ['bulan' => 11]));
        $resLaporan = $this->actingAs($this->admin)->get(route('admin.prb.laporan.belum-ambil-obat', ['bulan' => 11]));

        $resPengingat->assertOk()->assertSee('TESKU SINKRON')->assertDontSee('NARJA SINKRON');
        $resLaporan->assertOk()->assertSee('TESKU SINKRON')->assertDontSee('NARJA SINKRON');
    }

    public function test_admin_can_delete_master_obat_and_non_admin_is_forbidden(): void
    {
        $obatPrb = MasterObat::create([
            'nama_obat' => 'OBAT TIDAK JELAS PRB',
            'kategori' => 'prb',
            'sumber' => 'import',
            'is_active' => true,
        ]);

        $obatUmum = MasterObat::create([
            'nama_obat' => 'OBAT TIDAK JELAS SIMBOK',
            'kategori' => 'non_prb',
            'sumber' => 'import',
            'is_active' => true,
        ]);

        $dokter = User::create([
            'nama' => 'dr. Dokter Hapus Obat',
            'nik' => '3578010000000090',
            'password' => Hash::make('password123'),
            'peran' => 'petugas',
            'profesi' => 'Dokter',
            'is_active' => true,
        ]);

        // Non-admin tidak boleh melihat tombol/modal hapus dan ditolak (403)
        $this->actingAs($dokter)
            ->get(route('admin.import.obat-prb'))
            ->assertOk()
            ->assertDontSee('Hapus Data Obat?');

        $this->actingAs($dokter)
            ->deleteJson(route('admin.import.master-obat.destroy', $obatPrb->id))
            ->assertForbidden();

        $this->assertDatabaseHas('master_obats', ['id' => $obatPrb->id]);

        // Admin bisa melihat tombol/modal hapus dan menghapus master obat
        $this->actingAs($this->admin)
            ->get(route('admin.import.obat-prb'))
            ->assertOk()
            ->assertSee('Hapus Data Obat?');

        $this->actingAs($this->admin)
            ->deleteJson(route('admin.import.master-obat.destroy', $obatPrb->id))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('master_obats', ['id' => $obatPrb->id]);

        $this->actingAs($this->admin)
            ->deleteJson(route('admin.import.master-obat.destroy', $obatUmum->id))
            ->assertOk();

        $this->assertDatabaseMissing('master_obats', ['id' => $obatUmum->id]);
    }
}
