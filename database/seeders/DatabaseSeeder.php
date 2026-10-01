<?php

namespace Database\Seeders;

use App\Models\JadwalLayanan;
use App\Models\Pasien;
use App\Models\Pegawai;
use App\Models\PemberianObat;
use App\Models\Pemeriksaan;
use App\Models\Pengguna;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Pengguna::create([
            'nama' => 'Admin Test',
            'email' => 'admin@prolanis.com',
            'password' => Hash::make('password'),
            'peran' => 'admin',
        ]);

        $pegawai = Pegawai::create([
            'nama' => 'Dr. Budi',
            'jabatan' => 'Dokter Umum',
        ]);

        $pasien = Pasien::create([
            'nik' => '1234567890123456',
            'nama' => 'Pasien Test',
            'tanggal_lahir' => '1960-01-01',
        ]);

        $jadwal = JadwalLayanan::create([
            'tanggal' => now()->toDateString(),
            'dibuat_oleh' => 1,
        ]);

        $pemeriksaan = Pemeriksaan::create([
            'pasien_id' => $pasien->id,
            'jadwal_layanan_id' => $jadwal->id,
            'tanggal_periksa' => now()->toDateString(),
            'nakes_id' => $pegawai->id,
            'berat_badan' => 60.5,
            'tensi_sistol' => 120,
            'tensi_diastol' => 80,
        ]);

        PemberianObat::create([
            'pemeriksaan_id' => $pemeriksaan->id,
            'nama_obat' => 'Metformin 500mg',
        ]);

        $this->call([
            DummyPasienSeeder::class,
        ]);
    }
}
