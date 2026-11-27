<?php

namespace Tests\Feature;

use App\Models\PasienSrk;
use App\Models\PasienSrkTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PasienSrkTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $dokter;

    private User $farmasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin SRK',
            'username' => 'admin_srk',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'jabatan' => 'Admin',
            'is_active' => true,
        ]);

        $this->dokter = User::create([
            'nama' => 'Dokter SRK',
            'username' => 'dokter_srk',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'jabatan' => 'Dokter Umum',
            'is_active' => true,
        ]);

        $this->farmasi = User::create([
            'nama' => 'Farmasi SRK',
            'username' => 'farmasi_srk',
            'password' => bcrypt('password'),
            'peran' => 'petugas',
            'jabatan' => 'Apoteker',
            'is_active' => true,
        ]);
    }

    public function test_only_admin_can_access_pasien_srk_menu(): void
    {
        $this->actingAs($this->admin)->get(route('admin.srk.index'))->assertOk();
        $this->actingAs($this->dokter)->get(route('admin.srk.index'))->assertForbidden();
        $this->actingAs($this->farmasi)->get(route('admin.srk.index'))->assertForbidden();
    }

    public function test_admin_can_import_pasien_srk_without_mixing_pasien_table(): void
    {
        $csv = "Nama,Nomor_HP\nBudi Santoso,081234567890\nSiti Aminah,6281398765432\nBaris Kosong,\n";
        $file = UploadedFile::fake()->createWithContent('pasien-srk.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('admin.srk.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.srk.index'));
        $this->assertDatabaseHas('pasien_srk', [
            'nama' => 'Budi Santoso',
            'no_hp' => '6281234567890',
        ]);
        $this->assertDatabaseHas('pasien_srk', [
            'nama' => 'Siti Aminah',
            'no_hp' => '6281398765432',
        ]);
        $this->assertDatabaseCount('pasien_srk', 2);
        $this->assertDatabaseCount('pasien', 0);
        $this->assertDatabaseCount('pasien_prbs', 0);
    }

    public function test_admin_can_download_template_update_pesan_and_mark_contacted(): void
    {
        $this->actingAs($this->admin)->get(route('admin.srk.template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $srk = PasienSrk::create([
            'nama' => 'Ani Wijaya',
            'no_hp' => '628111222333',
        ]);

        $this->actingAs($this->admin)->post(route('admin.srk.pesan.update'), [
            'isi_pesan' => 'Halo {nama}, silakan datang ke PKM Kalijudan.',
        ])->assertRedirect(route('admin.srk.index'));

        $this->assertEquals('Halo {nama}, silakan datang ke PKM Kalijudan.', PasienSrkTemplate::currentText());

        $indexRes = $this->actingAs($this->admin)->get(route('admin.srk.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('https://wa.me/628111222333?text='.urlencode('Halo Ani Wijaya, silakan datang ke PKM Kalijudan.'), false);

        $this->actingAs($this->admin)->postJson(route('admin.srk.tandai', $srk->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $srk->refresh();
        $this->assertNotNull($srk->terakhir_dihubungi);
    }

    public function test_admin_can_delete_single_and_all_pasien_srk(): void
    {
        $first = PasienSrk::create(['nama' => 'Kontak 1', 'no_hp' => '628120000001']);
        PasienSrk::create(['nama' => 'Kontak 2', 'no_hp' => '628120000002']);

        $this->actingAs($this->admin)->delete(route('admin.srk.destroy', $first->id))
            ->assertRedirect(route('admin.srk.index'));
        $this->assertDatabaseCount('pasien_srk', 1);

        $this->actingAs($this->admin)->delete(route('admin.srk.destroy-all'))
            ->assertRedirect(route('admin.srk.index'));
        $this->assertDatabaseCount('pasien_srk', 0);
    }
}
