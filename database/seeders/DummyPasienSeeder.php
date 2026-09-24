<?php

namespace Database\Seeders;

use App\Models\Pasien;
use App\Models\Pemeriksaan;
use Illuminate\Database\Seeder;

class DummyPasienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pasien Hipertensi (Tensi Tinggi, BB Cenderung Naik)
        $pasienHipertensi = Pasien::updateOrCreate(
            ['nik' => '9999999999999991'],
            [
                'no_rm' => 'DUMMY.001',
                'nama' => 'BAPAK HIPERTENSI',
                'tanggal_lahir' => '1965-01-01',
                'jenis_kelamin' => 'L',
                'pekerjaan' => 'Wiraswasta',
                'no_hp' => '08111111111',
            ]
        );

        $dates = [
            '2026-05-10', '2026-06-12', '2026-07-15', '2026-08-16', '2026-09-20',
        ];

        $sistols = [145, 150, 160, 155, 165];
        $diastols = [90, 95, 100, 95, 105];
        $bbs = [75, 76.5, 78, 77, 80];

        foreach ($dates as $index => $date) {
            Pemeriksaan::updateOrCreate(
                [
                    'pasien_id' => $pasienHipertensi->id,
                    'tanggal_periksa' => $date,
                ],
                [
                    'tensi_sistol' => $sistols[$index],
                    'tensi_diastol' => $diastols[$index],
                    'berat_badan' => $bbs[$index],
                    'tinggi_badan' => 165,
                    'temperature' => 36.5,
                    'heart_rate' => 88,
                    'keluhan' => 'Sering pusing dan tengkuk terasa berat',
                    'nama_diagnosa' => 'Hipertensi Primer',
                    'kode_icd' => 'I10',
                ]
            );
        }

        // 2. Pasien Hipotensi (Tensi Rendah, BB Cenderung Turun)
        $pasienHipotensi = Pasien::updateOrCreate(
            ['nik' => '9999999999999992'],
            [
                'no_rm' => 'DUMMY.002',
                'nama' => 'IBU HIPOTENSI',
                'tanggal_lahir' => '1980-12-31',
                'jenis_kelamin' => 'P',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'no_hp' => '08222222222',
            ]
        );

        $sistolsLow = [90, 85, 95, 88, 92];
        $diastolsLow = [60, 55, 65, 58, 62];
        $bbsLow = [55, 53, 51, 50, 49];

        foreach ($dates as $index => $date) {
            Pemeriksaan::updateOrCreate(
                [
                    'pasien_id' => $pasienHipotensi->id,
                    'tanggal_periksa' => $date,
                ],
                [
                    'tensi_sistol' => $sistolsLow[$index],
                    'tensi_diastol' => $diastolsLow[$index],
                    'berat_badan' => $bbsLow[$index],
                    'tinggi_badan' => 158,
                    'temperature' => 36.2,
                    'heart_rate' => 72,
                    'keluhan' => 'Lemas, mudah lelah, mata berkunang-kunang',
                    'nama_diagnosa' => 'Hipotensi',
                    'kode_icd' => 'I95',
                ]
            );
        }

        // 3. Pasien Normal/Sembuh (Awal Tinggi, Akhir Normal)
        $pasienNormal = Pasien::updateOrCreate(
            ['nik' => '9999999999999993'],
            [
                'no_rm' => 'DUMMY.003',
                'nama' => 'BAPAK SEMBUH',
                'tanggal_lahir' => '1975-08-17',
                'jenis_kelamin' => 'L',
                'pekerjaan' => 'Pegawai Swasta',
                'no_hp' => '08333333333',
            ]
        );

        $sistolsNormal = [155, 140, 130, 125, 118];
        $diastolsNormal = [100, 95, 85, 80, 78];
        $bbsNormal = [85, 82, 80, 78, 75]; // BB turun karena diet

        foreach ($dates as $index => $date) {
            Pemeriksaan::updateOrCreate(
                [
                    'pasien_id' => $pasienNormal->id,
                    'tanggal_periksa' => $date,
                ],
                [
                    'tensi_sistol' => $sistolsNormal[$index],
                    'tensi_diastol' => $diastolsNormal[$index],
                    'berat_badan' => $bbsNormal[$index],
                    'tinggi_badan' => 170,
                    'temperature' => 36.6,
                    'heart_rate' => 80,
                    'keluhan' => $index < 2 ? 'Pusing' : 'Merasa lebih baik, kontrol rutin',
                    'nama_diagnosa' => 'Hipertensi Terkontrol',
                    'kode_icd' => 'I10',
                ]
            );
        }
    }
}
