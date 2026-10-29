<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\RiwayatPengambilanObatPrb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_cannot_be_accessed_without_login()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_logout()
    {
        $user = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_pegawai_can_login_with_nik_and_birthdate_password_format_dmyyyy()
    {
        $pegawai = User::create([
            'nama' => 'Bidan Siti',
            'nik' => '3505105605970001',
            'tanggal_lahir' => '1997-05-16',
            'password' => Hash::make('16051997'),
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => '3505105605970001',
            'password' => '16051997',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($pegawai);
    }

    public function test_pegawai_can_login_with_nik_and_birthdate_with_dashes_or_spaces()
    {
        $pegawai = User::create([
            'nama' => 'Perawat Zuly',
            'nik' => '3505106507930002',
            'tanggal_lahir' => '1993-07-25',
            'password' => Hash::make('25071993'),
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        // Login dengan NIK berformat spasi dan password berformat strip (25-07-1993)
        $response = $this->post('/login', [
            'username' => '3505 1065 0793 0002',
            'password' => '25-07-1993',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($pegawai);
    }

    public function test_sidebar_displays_user_jabatan_when_authenticated(): void
    {
        $user = User::create([
            'nama' => 'Arofah,S.M',
            'nik' => '3505105605970099',
            'password' => Hash::make('password'),
            'jabatan' => 'PJ TU',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Arofah,S.M');
        $response->assertSee('PJ TU');
        $response->assertDontSee('<p class="text-xs text-gray-500 truncate capitalize">admin</p>', false);
    }

    public function test_admin_can_see_all_sidebar_menus(): void
    {
        $admin = User::create([
            'nama' => 'dr. Ageng Rusbaya',
            'nik' => '3505105605970098',
            'password' => Hash::make('password'),
            'jabatan' => 'Kepala Puskesmas',
            'peran' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Petugas');
        $response->assertSee('Prolanis');
        $response->assertSee('Informed Consent');
        $response->assertSee('Log Aktivitas');
        $response->assertSee('Program Rujuk Balik (PRB)');
    }

    public function test_non_admin_hides_petugas_prolanis_consent_and_log_menus(): void
    {
        $petugas = User::create([
            'nama' => 'dr. Mujahidah',
            'nik' => '3505105605970097',
            'password' => Hash::make('password'),
            'jabatan' => 'Dokter Umum',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($petugas)->get('/admin/dashboard');

        $response->assertOk();
        // Visible menus
        $response->assertSee('dr. Mujahidah');
        $response->assertSee('Dokter Umum');
        $response->assertSee('Pasien');
        $response->assertSee('Program Rujuk Balik (PRB)');

        // Hidden menus for non-admin
        $response->assertDontSee(route('admin.petugas.index'));
        $response->assertDontSee(route('admin.jadwal.index'));
        $response->assertDontSee(route('admin.persetujuan.index'));
        $response->assertDontSee(route('admin.log.index'));

        // Direct access to admin routes is forbidden (403)
        $this->actingAs($petugas)->get('/admin/petugas')->assertForbidden();
        $this->actingAs($petugas)->get('/admin/jadwal')->assertForbidden();
        $this->actingAs($petugas)->get('/admin/persetujuan')->assertForbidden();
        $this->actingAs($petugas)->get('/admin/log-aktivitas')->assertForbidden();
    }

    public function test_dokter_can_see_prb_and_pengingat_except_farmasi(): void
    {
        $dokter = User::create([
            'nama' => 'dr. Dewi Hariyati',
            'nik' => '3505105605970096',
            'password' => Hash::make('password'),
            'jabatan' => 'Dokter Umum',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($dokter)->get('/admin/dashboard');

        $response->assertOk();
        // Dokter sees PRB submenus including Pengingat WA
        $response->assertSee(route('admin.prb.index'));
        $response->assertSee(route('admin.prb.register'));
        $response->assertSee(route('admin.prb.pengingat'));
        $response->assertSee(route('admin.import.prb'));

        // But dokter DOES NOT see Farmasi PRB
        $response->assertDontSee(route('admin.prb.farmasi.index'));

        // Direct access: can access PRB index and Pengingat WA, cannot access Farmasi PRB
        $this->actingAs($dokter)->get('/admin/prb')->assertOk();
        $this->actingAs($dokter)->get('/admin/prb/pengingat')->assertOk();
        $this->actingAs($dokter)->get('/admin/prb/farmasi')->assertForbidden();
    }

    public function test_farmasi_can_see_farmasi_prb_but_cannot_see_or_access_pengingat_wa(): void
    {
        // 1. Uji untuk Apoteker
        $apoteker = User::create([
            'nama' => 'Apt. Widyariningsih, S.Farm',
            'nik' => '3505105605970095',
            'password' => Hash::make('password'),
            'jabatan' => 'Apoteker',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $responseApoteker = $this->actingAs($apoteker)->get('/admin/dashboard');

        $responseApoteker->assertOk();
        // Apoteker sees PRB & Farmasi PRB
        $responseApoteker->assertSee(route('admin.prb.index'));
        $responseApoteker->assertSee(route('admin.prb.register'));
        $responseApoteker->assertSee(route('admin.prb.farmasi.index'));
        $responseApoteker->assertSee(route('admin.import.prb'));

        // Apoteker DOES NOT see Pengingat WA
        $responseApoteker->assertDontSee(route('admin.prb.pengingat'));

        // Direct access: can access PRB & Farmasi PRB, but Pengingat WA is forbidden (403)
        $this->actingAs($apoteker)->get('/admin/prb')->assertOk();
        $this->actingAs($apoteker)->get('/admin/prb/farmasi')->assertOk();
        $this->actingAs($apoteker)->get('/admin/prb/pengingat')->assertForbidden();

        // 2. Uji untuk Asisten Apoteker
        $asistenApoteker = User::create([
            'nama' => 'Dewi Asisten, A.Md.Farm',
            'nik' => '3505105605970093',
            'password' => Hash::make('password'),
            'jabatan' => 'Asisten Apoteker',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $responseAsisten = $this->actingAs($asistenApoteker)->get('/admin/dashboard');
        $responseAsisten->assertOk();
        $responseAsisten->assertSee(route('admin.prb.farmasi.index'));
        $responseAsisten->assertDontSee(route('admin.prb.pengingat'));

        $this->actingAs($asistenApoteker)->get('/admin/prb/pengingat')->assertForbidden();
    }

    public function test_general_staff_cannot_see_prb_menu(): void
    {
        $staff = User::create([
            'nama' => 'Arofah,S.M',
            'nik' => '3505105605970094',
            'password' => Hash::make('password'),
            'jabatan' => 'PJ TU',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get('/admin/dashboard');

        $response->assertOk();
        // Arofah sees Dashboard and Pasien
        $response->assertSee('Arofah,S.M');
        $response->assertSee('PJ TU');
        $response->assertSee('Pasien');

        // Arofah DOES NOT see PRB menu
        $response->assertDontSee('Program Rujuk Balik (PRB)');
        $response->assertDontSee(route('admin.prb.index'));
        $response->assertDontSee(route('admin.prb.farmasi.index'));

        // Direct access is forbidden
        $this->actingAs($staff)->get('/admin/prb')->assertForbidden();
        $this->actingAs($staff)->get('/admin/prb/farmasi')->assertForbidden();
    }

    public function test_only_dokter_and_admin_can_see_and_access_catat_pengambilan_in_prb(): void
    {
        $pasien = Pasien::create([
            'nik' => '3505101111110001',
            'nama' => 'Pasien PRB Uji',
            'tanggal_lahir' => '1980-01-01',
            'status_keaktifan' => 'aktif',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => now()->toDateString(),
        ]);

        // 1. Dokter
        $dokter = User::create([
            'nama' => 'dr. Dokter PRB',
            'nik' => '3505101111110002',
            'password' => Hash::make('password'),
            'jabatan' => 'Dokter Umum',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $responseDokter = $this->actingAs($dokter)->get(route('admin.prb.show', $prb->id));
        $responseDokter->assertOk();
        $responseDokter->assertSee('+ Catat Pengambilan');
        $responseDokter->assertSee(route('admin.prb.riwayat.create', $prb->id));

        $this->actingAs($dokter)->get(route('admin.prb.riwayat.create', $prb->id))->assertOk();

        // 2. Farmasi (Apoteker)
        $apoteker = User::create([
            'nama' => 'Apt. Apoteker Uji',
            'nik' => '3505101111110003',
            'password' => Hash::make('password'),
            'jabatan' => 'Apoteker',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        $responseApoteker = $this->actingAs($apoteker)->get(route('admin.prb.show', $prb->id));
        $responseApoteker->assertOk();
        // Apoteker can view patient details but CANNOT see "+ Catat Pengambilan" button
        $responseApoteker->assertDontSee('+ Catat Pengambilan');
        $responseApoteker->assertDontSee(route('admin.prb.riwayat.create', $prb->id));

        // Direct access to create riwayat is forbidden for farmasi
        $this->actingAs($apoteker)->get(route('admin.prb.riwayat.create', $prb->id))->assertForbidden();

        // 3. Admin
        $admin = User::create([
            'nama' => 'Admin Sistem',
            'nik' => '3505101111110004',
            'password' => Hash::make('password'),
            'jabatan' => 'Admin',
            'peran' => 'admin',
            'is_active' => true,
        ]);

        $responseAdmin = $this->actingAs($admin)->get(route('admin.prb.show', $prb->id));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('+ Catat Pengambilan');
        $this->actingAs($admin)->get(route('admin.prb.riwayat.create', $prb->id))->assertOk();
    }

    public function test_dokter_can_edit_and_delete_riwayat_when_status_farmasi_is_belum_but_hidden_when_sudah(): void
    {
        $pasien = Pasien::create([
            'nik' => '3505102222220001',
            'nama' => 'Pasien Farmasi Uji',
            'tanggal_lahir' => '1985-05-05',
            'status_keaktifan' => 'aktif',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => now()->toDateString(),
        ]);

        $riwayat = RiwayatPengambilanObatPrb::create([
            'pasien_prb_id' => $prb->id,
            'tanggal_jatuh_tempo' => now()->toDateString(),
            'status_farmasi' => 'belum',
            'sumber' => 'manual',
        ]);

        $dokter = User::create([
            'nama' => 'dr. Dokter Farmasi Test',
            'nik' => '3505102222220002',
            'password' => Hash::make('password'),
            'jabatan' => 'Dokter Umum',
            'peran' => 'petugas',
            'is_active' => true,
        ]);

        // 1. Saat status_farmasi = belum:
        // Dokter bisa melihat tombol edit dan hapus
        $responseDokterBelum = $this->actingAs($dokter)->get(route('admin.prb.show', $prb->id));
        $responseDokterBelum->assertOk();
        $responseDokterBelum->assertSee(route('admin.prb.riwayat.edit', $riwayat->id));
        $this->actingAs($dokter)->get(route('admin.prb.riwayat.edit', $riwayat->id))->assertOk();

        // 2. Saat status_farmasi = sudah:
        $riwayat->update(['status_farmasi' => 'sudah']);

        // Dokter TIDAK BISA melihat tombol edit dan hapus (hidden)
        $responseDokterSudah = $this->actingAs($dokter)->get(route('admin.prb.show', $prb->id));
        $responseDokterSudah->assertOk();
        $responseDokterSudah->assertDontSee(route('admin.prb.riwayat.edit', $riwayat->id));

        // Akses langsung edit ditolak dan diredirect dengan pesan error
        $responseEdit = $this->actingAs($dokter)->get(route('admin.prb.riwayat.edit', $riwayat->id));
        $responseEdit->assertRedirect(route('admin.prb.show', $prb->id));
        $responseEdit->assertSessionHas('error');

        // Akses langsung hapus ditolak dan record tidak terhapus
        $responseDelete = $this->actingAs($dokter)->delete(route('admin.prb.riwayat.destroy', $riwayat->id));
        $responseDelete->assertRedirect(route('admin.prb.show', $prb->id));
        $responseDelete->assertSessionHas('error');
        $this->assertDatabaseHas('riwayat_pengambilan_obat_prbs', ['id' => $riwayat->id]);
    }
}
