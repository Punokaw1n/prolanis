<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogAktivitasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Sistem',
            'nik' => '3505109999990001',
            'password' => Hash::make('password'),
            'jabatan' => 'Kepala Tata Usaha',
            'peran' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::create([
            'nama' => 'dr. Dokter Medis',
            'nik' => '3505109999990002',
            'password' => Hash::make('password'),
            'jabatan' => 'Dokter Umum',
            'peran' => 'petugas',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_log_aktivitas(): void
    {
        $this->get(route('admin.log.index'))->assertRedirect(route('login'));
        $this->get(route('admin.log.export'))->assertRedirect(route('login'));
        $this->post(route('admin.log.prune'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_log_aktivitas(): void
    {
        // Hanya role admin yang boleh mengakses menu Log Aktivitas
        $this->actingAs($this->petugas)->get(route('admin.log.index'))->assertForbidden();
        $this->actingAs($this->petugas)->get(route('admin.log.export'))->assertForbidden();
        $this->actingAs($this->petugas)->post(route('admin.log.prune'))->assertForbidden();
    }

    public function test_admin_can_view_log_aktivitas_and_summary_statistics(): void
    {
        LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'LOGIN',
            'deskripsi' => 'Admin login ke sistem',
            'created_at' => now()->subDays(40),
        ]);

        LogAktivitas::create([
            'user_id' => $this->petugas->id,
            'aksi' => 'SIMPAN_RESEP',
            'deskripsi' => 'Dokter menyimpan resep obat',
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.log.index'));

        $response->assertOk();
        $response->assertSee('Log Aktivitas');
        $response->assertSee('Refresh');
        $response->assertSee('Backup Excel');
        $response->assertSee('Bersihkan Log Lama');
        $response->assertSee('SIMPAN_RESEP');
        $response->assertSee('Admin login ke sistem');
        $response->assertSee('dr. Dokter Medis');
    }

    public function test_admin_can_export_log_aktivitas_to_excel(): void
    {
        LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'TAMBAH_PASIEN',
            'deskripsi' => 'Menambahkan pasien baru',
            'created_at' => now()->subDays(35),
        ]);

        LogAktivitas::create([
            'user_id' => $this->petugas->id,
            'aksi' => 'LOGIN',
            'deskripsi' => 'Petugas login',
            'created_at' => now(),
        ]);

        // Export All
        $response = $this->actingAs($this->admin)->get(route('admin.log.export', ['periode' => 'all']));
        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));

        // Export Lebih dari 30 hari
        $responseOld = $this->actingAs($this->admin)->get(route('admin.log.export', ['periode' => '30_hari']));
        $responseOld->assertOk();
    }

    public function test_admin_can_prune_logs_older_than_30_days_while_preserving_recent_logs(): void
    {
        // 1. Log lama (> 30 hari)
        $oldLog = LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'OLD_ACTION',
            'deskripsi' => 'Log lama yang harus dibersihkan',
            'created_at' => now()->subDays(45),
        ]);

        // 2. Log baru (< 30 hari)
        $recentLog = LogAktivitas::create([
            'user_id' => $this->petugas->id,
            'aksi' => 'RECENT_ACTION',
            'deskripsi' => 'Log baru yang harus dipertahankan',
            'created_at' => now()->subDays(2),
        ]);

        $this->assertDatabaseHas('log_aktivitas', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('log_aktivitas', ['id' => $recentLog->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.log.prune'), [
            'periode' => '30_hari',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Log lama terhapus
        $this->assertDatabaseMissing('log_aktivitas', ['id' => $oldLog->id]);

        // Log baru tetap ada
        $this->assertDatabaseHas('log_aktivitas', ['id' => $recentLog->id]);

        // Tercatat log baru untuk aksi pembersihan
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'BERSIHKAN_LOG',
        ]);
    }

    public function test_admin_pruning_all_logs_requires_kosongkan_confirmation(): void
    {
        LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'TEST_LOG',
            'deskripsi' => 'Log uji',
            'created_at' => now(),
        ]);

        // Konfirmasi salah -> gagal
        $responseFail = $this->actingAs($this->admin)->post(route('admin.log.prune'), [
            'periode' => 'semua',
            'konfirmasi_semua' => 'SALAH',
            'password' => 'password',
        ]);
        $responseFail->assertSessionHas('error');
        $this->assertDatabaseCount('log_aktivitas', 1);

        // Konfirmasi benar "KOSONGKAN" -> sukses
        $responseSuccess = $this->actingAs($this->admin)->post(route('admin.log.prune'), [
            'periode' => 'semua',
            'konfirmasi_semua' => 'KOSONGKAN',
            'password' => 'password',
        ]);
        $responseSuccess->assertSessionHas('success');

        // Seluruh log lama terhapus, hanya menyisakan log aktivitas baru BERSIHKAN_LOG
        $this->assertDatabaseMissing('log_aktivitas', ['aksi' => 'TEST_LOG']);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'BERSIHKAN_LOG']);
    }

    public function test_admin_pruning_requires_valid_password(): void
    {
        LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'TEST_LOG_PASS',
            'deskripsi' => 'Uji proteksi password',
            'created_at' => now()->subDays(45),
        ]);

        // 1. Password salah
        $responseWrongPass = $this->actingAs($this->admin)->post(route('admin.log.prune'), [
            'periode' => '30_hari',
            'password' => 'password-yang-salah',
        ]);
        $responseWrongPass->assertSessionHas('error');
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'TEST_LOG_PASS']);

        // 2. Password kosong
        $responseEmptyPass = $this->actingAs($this->admin)->post(route('admin.log.prune'), [
            'periode' => '30_hari',
            'password' => '',
        ]);
        $responseEmptyPass->assertSessionHasErrors('password');
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'TEST_LOG_PASS']);
    }

    public function test_admin_can_prune_logs_matching_active_filter_only(): void
    {
        $logUpdate1 = LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'UPDATE_PETUGAS',
            'deskripsi' => 'Memperbarui data petugas: dr. Dewi Hariyati.',
            'created_at' => now(),
        ]);

        $logUpdate2 = LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'UPDATE_PETUGAS',
            'deskripsi' => 'Memperbarui data petugas: dr. Dewi Hariyati kedua.',
            'created_at' => now(),
        ]);

        $logLogin = LogAktivitas::create([
            'user_id' => $this->petugas->id,
            'aksi' => 'LOGIN',
            'deskripsi' => 'Petugas login ke sistem',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.log.prune'), [
            'periode' => 'sesuai_filter',
            'filter_aksi' => 'UPDATE_PETUGAS',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Log dengan aksi UPDATE_PETUGAS harus terhapus
        $this->assertDatabaseMissing('log_aktivitas', ['id' => $logUpdate1->id]);
        $this->assertDatabaseMissing('log_aktivitas', ['id' => $logUpdate2->id]);

        // Log lain (LOGIN) harus tetap utuh
        $this->assertDatabaseHas('log_aktivitas', ['id' => $logLogin->id]);

        // Log aktivitas pembersihan tercatat
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'BERSIHKAN_LOG',
        ]);
    }

    public function test_admin_can_export_logs_matching_active_filter(): void
    {
        LogAktivitas::create([
            'user_id' => $this->admin->id,
            'aksi' => 'UPDATE_PETUGAS',
            'deskripsi' => 'Data untuk export filter',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.log.export', [
            'periode' => 'sesuai_filter',
            'filter_aksi' => 'UPDATE_PETUGAS',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
    }
}
