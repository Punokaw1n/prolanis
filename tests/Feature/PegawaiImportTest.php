<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PegawaiImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $excelPath;

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

        $this->excelPath = base_path('Dataset/DATA-PEGAWAI.xlsx');
    }

    public function test_admin_can_view_petugas_index_with_import_modal(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.petugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Import Pegawai');
        $response->assertSee('Import Data Pegawai');
    }

    public function test_import_data_pegawai_excel_successfully(): void
    {
        $this->assertFileExists($this->excelPath);

        $file = new UploadedFile(
            $this->excelPath,
            'DATA-PEGAWAI.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('admin.petugas.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.petugas.index'));
        $response->assertSessionHas('import_summary');
        $response->assertSessionHas('success');

        $summary = session('import_summary');
        $this->assertEquals(48, $summary['petugas_baru']);
        $this->assertEquals(0, $summary['petugas_existing']);
        $this->assertEquals(0, $summary['baris_gagal']);

        // Total user di database = 1 admin awal + 48 pegawai import = 49 akun
        $this->assertEquals(49, User::count());
        $this->assertEquals(48, Pegawai::count());

        // 1. Verifikasi Kepala Puskesmas (Role Admin)
        $kapus = User::where('nama', 'dr. Ageng Rusbaya')->first();
        $this->assertNotNull($kapus);
        $this->assertEquals('3578272509890003', $kapus->nik);
        $this->assertEquals('198909252014021002', $kapus->nip);
        $this->assertEquals('Kepala Puskesmas', $kapus->jabatan);
        $this->assertEquals('admin', $kapus->peran);
        $this->assertEquals('1989-09-25', $kapus->tanggal_lahir);
        // Password default dari tanggal lahir: 25091989
        $this->assertTrue(Hash::check('25091989', $kapus->password));

        // 2. Verifikasi Dokter Umum (Role Petugas)
        $dokter = User::where('nama', 'dr. Mujahidah')->first();
        $this->assertNotNull($dokter);
        $this->assertEquals('3578094105700003', $dokter->nik);
        $this->assertEquals('197005012006042001', $dokter->nip);
        $this->assertEquals('Dokter Umum', $dokter->jabatan);
        $this->assertEquals('petugas', $dokter->peran);
        $this->assertEquals('1970-05-01', $dokter->tanggal_lahir);
        $this->assertTrue(Hash::check('01051970', $dokter->password));

        // 3. Verifikasi Pegawai Non-PNS (Tanpa NIP)
        $nonPns = User::where('nama', 'dr. Ika Prihatiningsih')->first();
        $this->assertNotNull($nonPns);
        $this->assertEquals('3529016105900008', $nonPns->nik);
        $this->assertNull($nonPns->nip);
        $this->assertEquals('Dokter Umum', $nonPns->jabatan);
        $this->assertEquals('petugas', $nonPns->peran);
        $this->assertTrue(Hash::check('21051990', $nonPns->password));

        // 4. Verifikasi Driver Non-PNS
        $driver = User::where('nama', 'Imdadussadad Dhofiry')->first();
        $this->assertNotNull($driver);
        $this->assertEquals('3578092009990001', $driver->nik);
        $this->assertNull($driver->nip);
        $this->assertEquals('Driver', $driver->jabatan);
        $this->assertEquals('petugas', $driver->peran);

        // 5. Verifikasi Sinkronisasi ke Tabel Pegawai
        $pegawaiKapus = Pegawai::where('nik', '3578272509890003')->first();
        $this->assertNotNull($pegawaiKapus);
        $this->assertEquals('dr. Ageng Rusbaya', $pegawaiKapus->nama);
        $this->assertEquals('Kepala Puskesmas', $pegawaiKapus->jabatan);
        $this->assertEquals('L', $pegawaiKapus->jenis_kelamin);
    }

    public function test_import_same_pegawai_file_twice_prevents_duplicate_accounts(): void
    {
        $file1 = new UploadedFile(
            $this->excelPath,
            'DATA-PEGAWAI.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($this->admin)->post(route('admin.petugas.import'), [
            'file' => $file1,
        ]);

        $this->assertEquals(49, User::count());

        // Import kedua kalinya
        $file2 = new UploadedFile(
            $this->excelPath,
            'DATA-PEGAWAI.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('admin.petugas.import'), [
            'file' => $file2,
        ]);

        $response->assertSessionHas('import_summary');
        $response->assertSessionHas('info');
        $summary = session('import_summary');

        // Jumlah akun tidak bertambah ganda
        $this->assertEquals(49, User::count());
        $this->assertEquals(0, $summary['petugas_baru']);
        $this->assertEquals(48, $summary['petugas_existing']);
    }

    public function test_import_data_pegawai_via_ajax_returns_json(): void
    {
        $file = new UploadedFile(
            $this->excelPath,
            'DATA-PEGAWAI.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->postJson(route('admin.petugas.import'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => 'success',
            'summary' => [
                'petugas_baru' => 48,
                'petugas_existing' => 0,
                'baris_gagal' => 0,
            ],
        ]);

        // Upload ulang via AJAX menghasilkan type 'info'
        $fileAgain = new UploadedFile(
            $this->excelPath,
            'DATA-PEGAWAI.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $responseAgain = $this->actingAs($this->admin)->postJson(route('admin.petugas.import'), [
            'file' => $fileAgain,
        ]);

        $responseAgain->assertStatus(200);
        $responseAgain->assertJson([
            'status' => 'success',
            'type' => 'info',
            'summary' => [
                'petugas_baru' => 0,
                'petugas_existing' => 48,
                'baris_gagal' => 0,
            ],
        ]);
    }

    public function test_rejects_invalid_file_extension(): void
    {
        $fakeFile = UploadedFile::fake()->create('invalid.txt', 100);

        $response = $this->actingAs($this->admin)->post(route('admin.petugas.import'), [
            'file' => $fakeFile,
        ]);

        $response->assertSessionHasErrors(['file']);
    }
}
