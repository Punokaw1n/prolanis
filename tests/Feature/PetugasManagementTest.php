<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PetugasManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Sistem',
            'email' => 'admin@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_create_petugas_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.petugas.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Petugas');
        $response->assertSee('Jenis Kelamin');
        $response->assertSee('Pendidikan Terakhir');
        $response->assertSee('Jabatan Profesi');
        $response->assertSee('Kepala Puskesmas');
        $response->assertSee('Dokter Gigi');
        $response->assertSee('Pranata Laboratorium Kesehatan');
    }

    public function test_admin_can_store_manual_petugas_with_full_fields(): void
    {
        $payload = [
            'nama' => 'drg. Maya Anggraini',
            'nik' => '3578012505920001',
            'nip' => '199205252020122001',
            'tanggal_lahir' => '1992-05-25',
            'jenis_kelamin' => 'P',
            'pendidikan' => 'S1 Kedokteran Gigi',
            'email' => 'maya.gigi@prolanis.test',
            'no_bpjs' => '0001928374650',
            'no_hp' => '081234567890',
            'jabatan' => 'Dokter Gigi',
            'peran' => 'petugas',
            'alamat' => 'Jl. Mawar No. 12, Surabaya',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.petugas.store'), $payload);

        $response->assertRedirect(route('admin.petugas.index'));
        $response->assertSessionHas('success');

        // Verifikasi User pengguna
        $user = User::where('nik', '3578012505920001')->first();
        $this->assertNotNull($user);
        $this->assertEquals('drg. Maya Anggraini', $user->nama);
        $this->assertEquals('199205252020122001', $user->nip);
        $this->assertEquals('Dokter Gigi', $user->jabatan);
        $this->assertEquals('petugas', $user->peran);
        $this->assertTrue(Hash::check('25051992', $user->password));

        // Verifikasi sinkronisasi tabel Pegawai
        $pegawai = Pegawai::where('nik', '3578012505920001')->first();
        $this->assertNotNull($pegawai);
        $this->assertEquals('P', $pegawai->jenis_kelamin);
        $this->assertEquals('S1 Kedokteran Gigi', $pegawai->pendidikan);
        $this->assertEquals('0001928374650', $pegawai->no_bpjs);
    }

    public function test_admin_can_store_non_pns_petugas_without_nip(): void
    {
        $payload = [
            'nama' => 'Bambang Supriyadi',
            'nik' => '3578021008950002',
            'nip' => null, // Non-PNS tanpa NIP
            'tanggal_lahir' => '1995-08-10',
            'jenis_kelamin' => 'L',
            'pendidikan' => 'SMA Sederajat',
            'email' => null,
            'no_bpjs' => null,
            'no_hp' => '089876543210',
            'jabatan' => 'Driver',
            'peran' => 'petugas',
            'alamat' => 'Jl. Melati No. 5',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.petugas.store'), $payload);

        $response->assertRedirect(route('admin.petugas.index'));
        $response->assertSessionHas('success');

        $user = User::where('nik', '3578021008950002')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->nip);
        $this->assertEquals('Driver', $user->jabatan);
    }

    public function test_admin_can_edit_and_update_petugas(): void
    {
        $user = User::create([
            'nama' => 'Siti Nurhaliza, S.Kep',
            'nik' => '3578031503900003',
            'nip' => '199003152015032001',
            'tanggal_lahir' => '1990-03-15',
            'no_hp' => '081112223334',
            'jabatan' => 'Perawat',
            'peran' => 'petugas',
            'password' => Hash::make('15031990'),
            'is_active' => true,
        ]);

        Pegawai::create([
            'nama' => 'Siti Nurhaliza, S.Kep',
            'nik' => '3578031503900003',
            'jenis_kelamin' => 'P',
            'pendidikan' => 'D3 Keperawatan',
            'jabatan' => 'Perawat',
            'no_hp' => '081112223334',
        ]);

        // Buka halaman edit
        $editPage = $this->actingAs($this->admin)->get(route('admin.petugas.edit', $user->id));
        $editPage->assertStatus(200);
        $editPage->assertSee('Siti Nurhaliza, S.Kep');

        // Update jabatan & pendidikan
        $updatePayload = [
            'nama' => 'Siti Nurhaliza, S.Kep., Ns',
            'nik' => '3578031503900003',
            'nip' => '199003152015032001',
            'tanggal_lahir' => '1990-03-15',
            'jenis_kelamin' => 'P',
            'pendidikan' => 'S1 Keperawatan & Profesi Ners',
            'email' => 'siti.ners@prolanis.test',
            'no_bpjs' => '0009988776655',
            'no_hp' => '081112223334',
            'jabatan' => 'Perawat',
            'peran' => 'petugas',
            'alamat' => 'Alamat baru',
        ];

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.petugas.update', $user->id), $updatePayload);
        $updateResponse->assertRedirect(route('admin.petugas.index'));

        $user->refresh();
        $this->assertEquals('Siti Nurhaliza, S.Kep., Ns', $user->nama);
        $this->assertEquals('siti.ners@prolanis.test', $user->email);

        $pegawai = Pegawai::where('nik', '3578031503900003')->first();
        $this->assertEquals('S1 Keperawatan & Profesi Ners', $pegawai->pendidikan);
    }
}
