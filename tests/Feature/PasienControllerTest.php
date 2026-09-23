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
        $response->assertSee('Pasien A');
        $response->assertSee('1234567890123456');
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
}
