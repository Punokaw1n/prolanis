<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\Pengguna::create([
            'nama' => 'Admin Test',
            'email' => 'admin@prolanis.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'peran' => 'admin'
        ]);

        $pegawai = \App\Models\Pegawai::create([
            'nama' => 'Dr. Budi',
            'jabatan' => 'Dokter Umum'
        ]);

        $pasien = \App\Models\Pasien::create([
            'nik' => '1234567890123456',
            'nama' => 'Pasien Test',
            'tanggal_lahir' => '1960-01-01'
        ]);

        $jadwal = \App\Models\JadwalLayanan::create([
            'tanggal' => now()->toDateString(),
            'dibuat_oleh' => 1
        ]);

        $pemeriksaan = \App\Models\Pemeriksaan::create([
            'pasien_id' => $pasien->id,
            'jadwal_layanan_id' => $jadwal->id,
            'tanggal_periksa' => now()->toDateString(),
            'nakes_id' => $pegawai->id,
            'berat_badan' => 60.5,
            'tensi_sistol' => 120,
            'tensi_diastol' => 80
        ]);

        \App\Models\PemberianObat::create([
            'pemeriksaan_id' => $pemeriksaan->id,
            'nama_obat' => 'Metformin 500mg'
        ]);
    }
}
