<?php

namespace Tests\Feature;

use App\Models\ObatPrb;
use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\Pemeriksaan;
use App\Models\Pengguna;
use App\Models\RiwayatPengambilanObatPrb;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterPrbImportTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private string $excelPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama' => 'Admin Penguji PRB',
            'email' => 'admin-prb@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);

        $this->excelPath = base_path('Dataset/Register Pasien Rujuk Balik (PRB).xlsx');
    }

    public function test_can_view_register_prb_import_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.import.prb'));

        $response->assertStatus(200);
        $response->assertSee('Import Data PRB');
        $response->assertSee('Upload File Excel');
    }

    public function test_import_register_prb_excel_successfully(): void
    {
        $this->assertFileExists($this->excelPath);

        $file = new UploadedFile(
            $this->excelPath,
            'Register Pasien Rujuk Balik (PRB).xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('admin.import.prb.process'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.import.prb'));
        $response->assertSessionHas('import_summary');

        $summary = session('import_summary');
        // Dari 114 baris, ada 5 pasien yang terduplikasi barisnya di Excel asli (Ridawati, Kartika, Jiu Yuliana, Hutami BA, Nur Chamalah)
        // Importer secara cerdas mendeteksi NIK yang sama sehingga hanya membuat 109 pasien unik dan 5 pasien diperbarui.
        $this->assertEquals(109, $summary['pasien_baru']);
        $this->assertEquals(5, $summary['pasien_existing']);
        $this->assertGreaterThan(400, $summary['riwayat_baru']);
        $this->assertGreaterThan(400, $summary['obat_baru']);
        $this->assertEquals(0, $summary['baris_gagal']);

        // Pastikan tabel Pasien dan PasienPrb terisi pasien unik
        $this->assertEquals(109, Pasien::count());
        $this->assertEquals(109, PasienPrb::count());
        $this->assertGreaterThan(400, RiwayatPengambilanObatPrb::count());
        $this->assertGreaterThan(400, ObatPrb::count());

        // Pastikan TIDAK ADA data yang masuk ke tabel pemeriksaan Prolanis (terpisah total!)
        $this->assertEquals(0, Pemeriksaan::count());

        // Verifikasi salah satu pasien spesifik
        $sunarmi = Pasien::where('nama', 'Sunarmi')->first();
        $this->assertNotNull($sunarmi);
        $this->assertEquals('9106016801580001', $sunarmi->nik);
        $this->assertEquals('0002379009813', $sunarmi->no_bpjs);
        $this->assertEquals('PRB', $sunarmi->tipe_pasien);

        $prbSunarmi = $sunarmi->pasienPrb;
        $this->assertNotNull($prbSunarmi);
        $this->assertEquals('aktif', $prbSunarmi->status_keaktifan);
        $this->assertNotEmpty($prbSunarmi->diagnosa);
        $this->assertEquals($prbSunarmi->diagnosa, $sunarmi->diagnosa);
        // Kode diagnosa berupa kode ICD bersih (misal I11.9, E11.8, dsb)
        $this->assertMatchesRegularExpression('/^[A-Z]\d{2}(\.\d+)?(,\s*[A-Z]\d{2}(\.\d+)?)*$/', $prbSunarmi->diagnosa);
        $this->assertGreaterThan(5, $prbSunarmi->riwayatPengambilan()->count());

        // Verifikasi bahwa ke-5 obat dari dropdown (Data Validation) Sunarmi tercatat lengkap
        $firstRiwayat = $prbSunarmi->riwayatPengambilan()->first();
        $this->assertCount(5, $firstRiwayat->obatPrb);
        $this->assertContains('Biso 5mg', $firstRiwayat->obatPrb->pluck('nama_obat')->toArray());
        $this->assertContains('Diltiazem 30mg', $firstRiwayat->obatPrb->pluck('nama_obat')->toArray());
        $this->assertContains('Glime 1mg', $firstRiwayat->obatPrb->pluck('nama_obat')->toArray());
        $this->assertContains('Cande 1mg', $firstRiwayat->obatPrb->pluck('nama_obat')->toArray());
        $this->assertContains('HCT', $firstRiwayat->obatPrb->pluck('nama_obat')->toArray());
    }

    public function test_import_same_file_twice_prevents_duplicate_records(): void
    {
        $file1 = new UploadedFile(
            $this->excelPath,
            'Register Pasien Rujuk Balik (PRB).xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($this->admin)->post(route('admin.import.prb.process'), [
            'file' => $file1,
        ]);

        $totalPasienFirst = Pasien::count();
        $totalRiwayatFirst = RiwayatPengambilanObatPrb::count();

        // Import kedua kalinya
        $file2 = new UploadedFile(
            $this->excelPath,
            'Register Pasien Rujuk Balik (PRB).xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('admin.import.prb.process'), [
            'file' => $file2,
        ]);

        $response->assertSessionHas('import_summary');
        $summary = session('import_summary');

        // Jumlah pasien dan riwayat tidak bertambah ganda
        $this->assertEquals($totalPasienFirst, Pasien::count());
        $this->assertEquals($totalRiwayatFirst, RiwayatPengambilanObatPrb::count());
        $this->assertEquals(0, $summary['pasien_baru']);
        $this->assertEquals(114, $summary['pasien_existing']);
        $this->assertGreaterThan(0, $summary['riwayat_skip']);
    }

    public function test_rejects_invalid_file_format(): void
    {
        $fakeFile = UploadedFile::fake()->create('invalid_file.xlsx', 100);

        $response = $this->actingAs($this->admin)->post(route('admin.import.prb.process'), [
            'file' => $fakeFile,
        ]);

        $response->assertSessionHasErrors(['file']);
    }
}
