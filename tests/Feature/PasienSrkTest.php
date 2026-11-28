<?php

namespace Tests\Feature;

use App\Models\PasienSrk;
use App\Models\PasienSrkTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
        $this->assertDatabaseCount('log_aktivitas', 0);
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
        $this->assertDatabaseMissing('log_aktivitas', [
            'aksi' => 'HUBUNGI_SRK',
        ]);
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

    public function test_admin_can_export_pasien_srk_filtered_by_status(): void
    {
        PasienSrk::create([
            'nama' => 'Pasien Sudah',
            'no_hp' => '628120000010',
            'terakhir_dihubungi' => now(),
        ]);

        PasienSrk::create([
            'nama' => 'Pasien Belum',
            'no_hp' => '628120000020',
            'terakhir_dihubungi' => null,
        ]);

        $resSudah = $this->actingAs($this->admin)->get(route('admin.srk.export', ['status' => 'sudah']));
        $resSudah->assertOk();
        $resSudah->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tempFileSudah = tempnam(sys_get_temp_dir(), 'srk_sudah_');
        file_put_contents($tempFileSudah, $resSudah->streamedContent());
        $rowsSudah = IOFactory::load($tempFileSudah)->getActiveSheet()->toArray();
        @unlink($tempFileSudah);

        $this->assertCount(2, $rowsSudah); // Header + 1 row
        $this->assertEquals('Pasien Sudah', $rowsSudah[1][1]);
        $this->assertEquals('Sudah Dihubungi', $rowsSudah[1][3]);

        $resBelum = $this->actingAs($this->admin)->get(route('admin.srk.export', ['status' => 'belum']));
        $resBelum->assertOk();

        $tempFileBelum = tempnam(sys_get_temp_dir(), 'srk_belum_');
        file_put_contents($tempFileBelum, $resBelum->streamedContent());
        $rowsBelum = IOFactory::load($tempFileBelum)->getActiveSheet()->toArray();
        @unlink($tempFileBelum);

        $this->assertCount(2, $rowsBelum); // Header + 1 row
        $this->assertEquals('Pasien Belum', $rowsBelum[1][1]);
        $this->assertEquals('Belum Dihubungi', $rowsBelum[1][3]);
        $this->assertDatabaseCount('log_aktivitas', 0);
    }
}
