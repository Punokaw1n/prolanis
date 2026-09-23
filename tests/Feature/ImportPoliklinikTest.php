<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\Pemeriksaan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImportPoliklinikTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    private string $excelPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Pengguna::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'peran' => 'admin',
        ]);
        $this->excelPath = base_path('dataset/Register PoliKlinik Semua(13820260922134223).xlsx');
    }

    public function test_import_poliklinik_creates_pasien_and_pemeriksaan(): void
    {
        $this->assertFileExists($this->excelPath);

        $file = new UploadedFile($this->excelPath, 'poliklinik.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)->post(route('admin.import.poliklinik.process'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.import.poliklinik'));
        $response->assertSessionHas('import_summary');

        // Pastikan data masuk ke database
        $this->assertGreaterThan(0, Pasien::count());
        $this->assertGreaterThan(0, Pemeriksaan::count());
    }

    public function test_import_same_file_twice_does_not_create_duplicates(): void
    {
        $file1 = new UploadedFile($this->excelPath, 'poliklinik.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->actingAs($this->admin)->post(route('admin.import.poliklinik.process'), ['file' => $file1]);

        $pasienCountAfterFirst = Pasien::count();
        $pemeriksaanCountAfterFirst = Pemeriksaan::count();

        // Import kedua
        $file2 = new UploadedFile($this->excelPath, 'poliklinik.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $response = $this->actingAs($this->admin)->post(route('admin.import.poliklinik.process'), ['file' => $file2]);

        $response->assertSessionHas('import_summary');
        $summary = session('import_summary');

        // Jumlah pasien dan pemeriksaan TIDAK bertambah
        $this->assertEquals($pasienCountAfterFirst, Pasien::count());
        $this->assertEquals($pemeriksaanCountAfterFirst, Pemeriksaan::count());

        // Semua kunjungan harus di-skip pada import kedua
        $this->assertEquals(0, $summary['kunjungan_baru']);
        $this->assertGreaterThan(0, $summary['kunjungan_skip']);
    }
}
