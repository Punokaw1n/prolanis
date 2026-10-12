<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasienControllerTest extends TestCase
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
            'peran' => 'admin',
        ]);
    }

    public function test_admin_can_view_pasien_index()
    {
        Pasien::create([
            'nik' => '1234567890123456',
            'nama' => 'Pasien A',
            'tanggal_lahir' => '1990-01-01',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pasien.index'));

        $response->assertStatus(200);
        $response->assertSee('Mulai Pencarian');
    }

    public function test_admin_can_toggle_pasien_status()
    {
        $pasien = Pasien::create([
            'nik' => '1234567890123456',
            'nama' => 'Pasien A',
            'tanggal_lahir' => '1990-01-01',
            'status_keaktifan' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.pasien.toggle-status', $pasien->id), [
            'alasan' => 'Meninggal Dunia',
        ]);

        $response->assertRedirect();
        $this->assertEquals('tidak_aktif', $pasien->fresh()->status_keaktifan);
        $this->assertEquals('Meninggal Dunia', $pasien->fresh()->alasan_tidak_aktif);
        $this->assertNotNull($pasien->fresh()->tanggal_tidak_aktif);

        // Toggle back to active
        $response = $this->actingAs($this->admin)->put(route('admin.pasien.toggle-status', $pasien->id));
        $response->assertRedirect();
        $this->assertEquals('aktif', $pasien->fresh()->status_keaktifan);
        $this->assertNull($pasien->fresh()->alasan_tidak_aktif);
        $this->assertNull($pasien->fresh()->tanggal_tidak_aktif);
    }

    public function test_admin_can_search_pasien_by_name()
    {
        Pasien::create(['nik' => '111', 'nama' => 'Budi', 'tanggal_lahir' => '1990-01-01']);
        Pasien::create(['nik' => '222', 'nama' => 'Siti', 'tanggal_lahir' => '1990-01-01']);

        $response = $this->actingAs($this->admin)->get(route('admin.pasien.index', ['search' => 'Budi']));

        $response->assertStatus(200);
        $response->assertSee('Budi');
        $response->assertDontSee('Siti');
    }

    public function test_admin_can_view_pasien_detail()
    {
        $pasien = Pasien::create([
            'nik' => '1234567890123456',
            'nama' => 'Pasien A',
            'tanggal_lahir' => '1990-01-01',
            'pekerjaan' => 'Petani',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pasien.show', $pasien->id));

        $response->assertStatus(200);
        $response->assertSee('Pasien A');
        $response->assertSee('1234567890123456');
        $response->assertSee('Petani');
    }

    public function test_admin_can_filter_pasien_by_status_without_search_query()
    {
        Pasien::create(['nik' => '111', 'nama' => 'Pasien Aktif', 'tanggal_lahir' => '1990-01-01', 'status_keaktifan' => 'aktif']);
        Pasien::create(['nik' => '222', 'nama' => 'Pasien Nonaktif', 'tanggal_lahir' => '1990-01-01', 'status_keaktifan' => 'tidak_aktif']);

        // 1. Initial page load (no filter and no search) -> Shows empty state
        $response = $this->actingAs($this->admin)->get(route('admin.pasien.index'));
        $response->assertStatus(200);
        $response->assertSee('Mulai Pencarian');
        $response->assertDontSee('Pasien Aktif');
        $response->assertDontSee('Pasien Nonaktif');

        // 2. Filter Aktif -> Immediately shows active patients
        $response = $this->actingAs($this->admin)->get(route('admin.pasien.index', ['status' => 'aktif']));
        $response->assertStatus(200);
        $response->assertSee('Pasien Aktif');
        $response->assertDontSee('Pasien Nonaktif');

        // 3. Filter Tidak Aktif -> Immediately shows inactive patients
        $response = $this->actingAs($this->admin)->get(route('admin.pasien.index', ['status' => 'tidak_aktif']));
        $response->assertStatus(200);
        $response->assertSee('Pasien Nonaktif');
        $response->assertDontSee('Pasien Aktif');

        // 4. Filter Semua Status -> Immediately shows all patients
        $response = $this->actingAs($this->admin)->get(route('admin.pasien.index', ['status' => 'semua']));
        $response->assertStatus(200);
        $response->assertSee('Pasien Aktif');
        $response->assertSee('Pasien Nonaktif');
    }
}
