<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\JadwalLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class JadwalLayananTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Pengguna::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'peran' => 'admin'
        ]);
    }

    public function test_admin_can_view_jadwal_index()
    {
        $response = $this->actingAs($this->admin)->get('/admin/jadwal');
        $response->assertStatus(200);
        $response->assertViewIs('admin.jadwal.index');
    }

    public function test_admin_can_create_jadwal()
    {
        $response = $this->actingAs($this->admin)->post('/admin/jadwal', [
            'tanggal' => '2026-10-15',
            'keterangan' => 'Prolanis Oktober',
        ]);

        $response->assertRedirect('/admin/jadwal');
        $this->assertDatabaseHas('jadwal_layanan', [
            'tanggal' => '2026-10-15',
            'keterangan' => 'Prolanis Oktober',
            'dibuat_oleh' => $this->admin->id,
        ]);
    }

    public function test_admin_can_update_jadwal()
    {
        $jadwal = JadwalLayanan::create([
            'tanggal' => '2026-09-01',
            'keterangan' => 'Test',
            'dibuat_oleh' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/jadwal/{$jadwal->id}", [
            'tanggal' => '2026-09-02',
            'keterangan' => 'Updated',
        ]);

        $response->assertRedirect('/admin/jadwal');
        $this->assertDatabaseHas('jadwal_layanan', [
            'id' => $jadwal->id,
            'tanggal' => '2026-09-02',
            'keterangan' => 'Updated',
        ]);
    }

    public function test_admin_can_delete_jadwal()
    {
        $jadwal = JadwalLayanan::create([
            'tanggal' => '2026-09-01',
            'keterangan' => 'Test',
            'dibuat_oleh' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/jadwal/{$jadwal->id}");

        $response->assertRedirect('/admin/jadwal');
        $this->assertDatabaseMissing('jadwal_layanan', [
            'id' => $jadwal->id,
        ]);
    }
}
