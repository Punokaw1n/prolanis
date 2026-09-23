<?php

namespace Tests\Feature;

use App\Models\Pasien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasienPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pasien_can_view_login_form()
    {
        $response = $this->get(route('pasien.login'));
        $response->assertStatus(200);
        $response->assertSee('Nomor Induk Kependudukan (NIK)');
    }

    public function test_pasien_can_login_with_valid_credentials()
    {
        $pasien = Pasien::create([
            'nik' => '3578264306780003',
            'nama' => 'ERNAWATI',
            'tanggal_lahir' => '1981-06-05',
        ]);

        $response = $this->post(route('pasien.login.process'), [
            'nik' => '3578264306780003',
            'tanggal_lahir' => '1981-06-05',
        ]);

        $response->assertRedirect(route('pasien.dashboard'));
        $this->assertAuthenticatedAs($pasien, 'pasien');
    }

    public function test_pasien_cannot_login_with_invalid_credentials()
    {
        Pasien::create([
            'nik' => '3578264306780003',
            'nama' => 'ERNAWATI',
            'tanggal_lahir' => '1981-06-05',
        ]);

        $response = $this->post(route('pasien.login.process'), [
            'nik' => '3578264306780003',
            'tanggal_lahir' => '1990-01-01', // wrong dob
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertGuest('pasien');
    }

    public function test_pasien_can_view_dashboard()
    {
        $pasien = Pasien::create([
            'nik' => '3578264306780003',
            'nama' => 'ERNAWATI',
            'tanggal_lahir' => '1981-06-05',
        ]);

        // Create riwayat
        $pasien->pemeriksaans()->create([
            'tanggal_periksa' => '2023-01-01',
            'berat_badan' => 60,
            'tensi_sistol' => 120,
            'tensi_diastol' => 80,
        ]);

        $response = $this->actingAs($pasien, 'pasien')->get(route('pasien.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('ERNAWATI');
        $response->assertSee('120'); // Sistol
        $response->assertSee('80'); // Diastol
        $response->assertSee('tensiChart');
        $response->assertSee('bbChart');
    }
}
