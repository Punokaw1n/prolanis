<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrbRegisterTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private PasienPrb $pasienPrb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'nama' => 'Petugas Farmasi Test',
            'email' => 'farmasi@prolanis.test',
            'password' => Hash::make('password123'),
            'peran' => 'admin',
            'is_active' => true,
        ]);

        $pasien = Pasien::create([
            'nik' => '3578012345670001',
            'nama' => 'Hartimi BA',
            'no_bpjs' => '0001266058495',
            'alamat' => 'Kalijudan 4/8',
            'no_hp' => '081234567890',
            'tanggal_lahir' => '1960-01-01',
            'status_keaktifan' => 'aktif',
        ]);

        $this->pasienPrb = PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => Carbon::now()->toDateString(),
        ]);
    }

    public function test_guest_cannot_access_register(): void
    {
        $response = $this->get(route('admin.prb.register'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_register_page(): void
    {
        $tanggalAmbil = Carbon::now()->toDateString();

        $riwayat = $this->pasienPrb->riwayatPengambilan()->create([
            'tanggal_diambil' => $tanggalAmbil,
            'tanggal_jatuh_tempo' => Carbon::now()->addDays(30)->toDateString(),
            'catatan' => 'Px baru Juli',
            'sumber' => 'manual',
            'dicatat_oleh' => $this->admin->id,
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Amlodipin 10mg',
            'kategori' => 'prb',
            'signa' => '1 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 30,
            'aturan_minum' => 'Pagi Sesudah Makan',
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.prb.register', [
            'mode' => 'harian',
            'tanggal' => $tanggalAmbil,
        ]));

        $response->assertOk();
        $response->assertSee('Hartimi BA');
        $response->assertSee('0001266058495');
        $response->assertSee('Amlodipin 10mg');
        $response->assertSee('(30)');
        $response->assertSee('Px baru Juli');
    }

    public function test_can_filter_register_by_month(): void
    {
        $augustDate = '2026-08-15';

        $riwayat = $this->pasienPrb->riwayatPengambilan()->create([
            'tanggal_diambil' => $augustDate,
            'tanggal_jatuh_tempo' => '2026-09-15',
            'sumber' => 'manual',
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Bisoprolol 2.5mg',
            'kategori' => 'prb',
            'signa' => '1 x 1',
            'jumlah_hari' => 30,
            'jumlah_obat' => 30,
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.prb.register', [
            'mode' => 'bulan',
            'bulan' => 8,
            'tahun' => 2026,
        ]));

        $response->assertOk();
        $response->assertSee('Bulan Agustus 2026');
        $response->assertSee('Bisoprolol 2.5mg');
    }

    public function test_can_render_cetak_register_view(): void
    {
        $tanggalAmbil = '2026-08-01';

        $riwayat = $this->pasienPrb->riwayatPengambilan()->create([
            'tanggal_diambil' => $tanggalAmbil,
            'tanggal_jatuh_tempo' => '2026-09-01',
            'sumber' => 'manual',
        ]);

        $riwayat->obatPrb()->create([
            'nama_obat' => 'Tiotropium Bromide',
            'kategori' => 'prb',
            'signa' => '1 x 1',
            'jumlah_obat' => 1,
            'sumber' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.prb.register.cetak', [
            'mode' => 'bulan',
            'bulan' => 8,
            'tahun' => 2026,
        ]));

        $response->assertOk();
        $response->assertSee('REGISTER PASIEN RUJUK BALIK');
        $response->assertSee('BULAN : AGUSTUS 2026');
        $response->assertSee('Tiotropium Bromide');
        $response->assertSee('(1)');
    }
}
