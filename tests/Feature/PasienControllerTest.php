<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\PasienPrb;
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

    public function test_admin_can_update_pasien_data_successfully(): void
    {
        $pasien = Pasien::create([
            'nik' => '3501010101900001',
            'nama' => 'Budi Santoso',
            'tanggal_lahir' => '1990-01-01',
            'no_hp' => null,
            'no_bpjs' => null,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.pasien.update', $pasien->id), [
            'nama' => 'Budi Santoso S.Kom',
            'nik' => '3501010101900002',
            'no_hp' => '081234567890',
            'no_bpjs' => '0001234567890',
            'no_rm' => 'RM-9901',
            'tanggal_lahir' => '1990-01-02',
            'jenis_kelamin' => 'L',
            'pekerjaan' => 'PNS',
            'alamat' => 'Jl. Merdeka No. 10',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $updated = $pasien->fresh();
        $this->assertEquals('Budi Santoso S.Kom', $updated->nama);
        $this->assertEquals('3501010101900002', $updated->nik);
        $this->assertEquals('6281234567890', $updated->no_hp); // Normalized
        $this->assertEquals('0001234567890', $updated->no_bpjs);
        $this->assertEquals('RM-9901', $updated->no_rm);
        $this->assertEquals('1990-01-02', $updated->tanggal_lahir);
        $this->assertEquals('L', $updated->jenis_kelamin);
        $this->assertEquals('PNS', $updated->pekerjaan);
        $this->assertEquals('Jl. Merdeka No. 10', $updated->alamat);
    }

    public function test_update_pasien_validates_unique_nik(): void
    {
        Pasien::create([
            'nik' => '3501010101900001',
            'nama' => 'Pasien Pertama',
            'tanggal_lahir' => '1990-01-01',
        ]);

        $pasienDua = Pasien::create([
            'nik' => '3501010101900002',
            'nama' => 'Pasien Kedua',
            'tanggal_lahir' => '1992-02-02',
        ]);

        // Attempt to update Pasien Kedua with Pasien Pertama's NIK
        $response = $this->actingAs($this->admin)->put(route('admin.pasien.update', $pasienDua->id), [
            'nama' => 'Pasien Kedua Edit',
            'nik' => '3501010101900001', // Duplicate!
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertEquals('Pasien Kedua', $pasienDua->fresh()->nama);
    }

    public function test_update_pasien_allows_same_nik_for_self(): void
    {
        $pasien = Pasien::create([
            'nik' => '3501010101900001',
            'nama' => 'Pasien Asli',
            'tanggal_lahir' => '1990-01-01',
            'no_hp' => null,
        ]);

        // Update phone number without changing NIK
        $response = $this->actingAs($this->admin)->put(route('admin.pasien.update', $pasien->id), [
            'nama' => 'Pasien Asli',
            'nik' => '3501010101900001',
            'no_hp' => '08987654321',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertEquals('628987654321', $pasien->fresh()->no_hp);
    }

    public function test_admin_can_update_diagnosa_for_prb_pasien(): void
    {
        $pasien = Pasien::create([
            'nik' => '3501010101900001',
            'nama' => 'Pasien PRB Test',
            'tanggal_lahir' => '1990-01-01',
            'tipe_pasien' => 'PRB',
        ]);

        $prb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'tanggal_terdaftar' => '2024-01-01',
            'status_keaktifan' => 'aktif',
            'diagnosa' => 'I11.9',
        ]);

        $this->assertEquals('I11.9', $pasien->diagnosa);

        $response = $this->actingAs($this->admin)->put(route('admin.pasien.update', $pasien->id), [
            'nama' => 'Pasien PRB Test',
            'nik' => '3501010101900001',
            'diagnosa' => 'E11.8',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertEquals('E11.8', $prb->fresh()->diagnosa);
        $this->assertEquals('E11.8', $pasien->fresh()->diagnosa);
    }
}
