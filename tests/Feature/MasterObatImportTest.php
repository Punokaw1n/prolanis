<?php

namespace Tests\Feature;

use App\Imports\MasterObatPrbImport;
use App\Imports\MasterObatUmumImport;
use App\Models\MasterObat;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MasterObatImportTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama' => 'Admin Farmasi',
            'email' => 'farmasi@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_can_view_import_obat_prb_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.import.obat-prb'));
        $response->assertStatus(200);
        $response->assertSee('Import Master Obat PRB');
    }

    public function test_can_view_import_obat_umum_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.import.obat-umum'));
        $response->assertStatus(200);
        $response->assertSee('Import Master Obat Umum (SIMBOK)');
        $response->assertSee(route('admin.import.obat-umum.template'));
        $response->assertSee('Download Template Excel');
    }

    public function test_import_obat_prb_via_array(): void
    {
        $import = new MasterObatPrbImport;
        $import->array([
            ['no' => 1, 'nama_barang' => 'Acarbose 100mg'],
            ['no' => 2, 'nama_barang' => 'Amlodipin 10 mg'],
            ['no' => 3, 'nama_barang' => 'Amlodipin 10 mg'], // duplicate test
        ]);

        $this->assertEquals(2, $import->summary['obat_baru']);
        $this->assertEquals(1, $import->summary['obat_existing']);
        $this->assertEquals(2, MasterObat::prb()->count());
    }

    public function test_import_obat_umum_via_array(): void
    {
        $import = new MasterObatUmumImport;
        $import->array([
            ['no' => 1, 'nama_komponen' => 'ALLOPURINOL TABLET 100 MG'],
            ['no' => 2, 'nama_komponen' => 'AMBROXOL 30 MG TABLET'],
            ['no' => 3, 'nama_komponen' => 'ALLOPURINOL TABLET 100 MG'], // duplicate test
        ]);

        $this->assertEquals(2, $import->summary['obat_baru']);
        $this->assertEquals(1, $import->summary['obat_existing']);
        $this->assertEquals(2, MasterObat::nonPrb()->count());
    }

    public function test_import_obat_prb_rejects_wrong_headers(): void
    {
        Excel::fake();

        $file = UploadedFile::fake()->create('wrong.xlsx', 100);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.import.obat-prb.process'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_obat_umum_rejects_wrong_headers(): void
    {
        Excel::fake();

        $file = UploadedFile::fake()->create('wrong.xlsx', 100);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.import.obat-umum.process'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_can_download_template_obat_umum(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.import.obat-umum.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=obat-umum-simbok.xlsx');
    }
}
