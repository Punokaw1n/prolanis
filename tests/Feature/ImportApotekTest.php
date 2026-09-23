<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\PemberianObat;
use App\Models\Pemeriksaan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImportApotekTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    private string $poliklinikPath;

    private string $apotekPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Pengguna::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'peran' => 'admin',
        ]);
        $this->poliklinikPath = base_path('dataset/Register PoliKlinik Semua(13820260922134223).xlsx');
        $this->apotekPath = base_path('dataset/Register Apotek(13820260922134107).xlsx');
    }

    public function test_import_apotek_fails_cleanly_if_poli_data_missing(): void
    {
        $file = new UploadedFile($this->apotekPath, 'apotek.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)->post(route('admin.import.apotek.process'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.import.apotek'));
        $response->assertSessionHas('import_summary');

        $summary = session('import_summary');

        // Seharusnya tidak ada obat yang disimpan karena pasien belum di-import
        $this->assertEquals(0, $summary['obat_disimpan']);
        $this->assertGreaterThan(0, $summary['baris_gagal']);
        $this->assertEquals(0, PemberianObat::count());
    }

    public function test_import_apotek_success_after_poli_import(): void
    {
        // 1. Import Poliklinik dulu
        $poliFile = new UploadedFile($this->poliklinikPath, 'poliklinik.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->actingAs($this->admin)->post(route('admin.import.poliklinik.process'), ['file' => $poliFile]);

        $this->assertGreaterThan(0, Pemeriksaan::count());

        // 2. Import Apotek
        $apotekFile = new UploadedFile($this->apotekPath, 'apotek.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $response = $this->actingAs($this->admin)->post(route('admin.import.apotek.process'), [
            'file' => $apotekFile,
        ]);

        $response->assertRedirect(route('admin.import.apotek'));
        $response->assertSessionHas('import_summary');

        $summary = session('import_summary');

        // Obat harus berhasil disimpan (mengingat ada beberapa obat per baris yang dipisah titik koma)
        $this->assertGreaterThan(0, $summary['obat_disimpan']);
        $this->assertGreaterThan(0, PemberianObat::count());
    }
}
