<?php

namespace App\Imports;

use App\Models\Pasien;
use App\Models\Pegawai;
use App\Models\Pemeriksaan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PoliklinikImport implements ToArray, WithHeadingRow
{
    /** @var array{pasien_baru: int, pasien_existing: int, kunjungan_baru: int, kunjungan_skip: int, baris_gagal: int, errors: list<string>} */
    public array $summary = [
        'pasien_baru' => 0,
        'pasien_existing' => 0,
        'kunjungan_baru' => 0,
        'kunjungan_skip' => 0,
        'baris_gagal' => 0,
        'errors' => [],
    ];

    public function array(array $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena index 0-based dan header row

            try {
                $this->processRow($row, $rowNumber);
            } catch (\Throwable $e) {
                $this->summary['baris_gagal']++;
                $this->summary['errors'][] = "Baris {$rowNumber}: {$e->getMessage()}";
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function processRow(array $row, int $rowNumber): void
    {
        // === Ambil & trim NIK ===
        $nik = trim($row['nik'] ?? '');
        if (empty($nik)) {
            $this->summary['baris_gagal']++;
            $this->summary['errors'][] = "Baris {$rowNumber}: NIK kosong, baris dilewati.";

            return;
        }

        // === Parse tanggal periksa (format DD-MM-YYYY) ===
        $tanggalPeriksa = $this->parseDate($row['tanggal'] ?? '');
        if (! $tanggalPeriksa) {
            $this->summary['baris_gagal']++;
            $this->summary['errors'][] = "Baris {$rowNumber}: Format tanggal tidak valid '{$row['tanggal']}'.";

            return;
        }

        // === Parse tanggal lahir ===
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? '');

        // === Cek/Buat Pasien (dedup via NIK) ===
        $pasien = Pasien::where('nik', $nik)->first();

        $noRm = trim($row['no_index'] ?? '');
        $tipePasien = trim($row['tipe_pasien'] ?? $row['type_pasien'] ?? $row['jenis_pasien'] ?? $row['kategori'] ?? $row['kelompok'] ?? $row['asuransi'] ?? $row['jenis_peserta'] ?? $row['peserta'] ?? $row['cara_bayar'] ?? '');

        // Coba ekstrak tipe pasien dari No RM jika kolom spesifik kosong (misal: "01.2112/00/CKG25/PROLANIS DM")
        if (empty($tipePasien) && str_contains(strtoupper($noRm), 'PROLANIS')) {
            if (str_contains(strtoupper($noRm), 'PROLANIS DM')) {
                $tipePasien = 'PROLANIS DM';
            } elseif (str_contains(strtoupper($noRm), 'PROLANIS HT')) {
                $tipePasien = 'PROLANIS HT';
            } else {
                $tipePasien = 'PROLANIS';
            }
        } elseif (empty($tipePasien) && str_contains(strtoupper($noRm), 'PRB')) {
            $tipePasien = 'PRB';
        }

        $dataPasien = [
            'no_rm' => $noRm,
            'nama' => trim($row['nama'] ?? ''),
            'alamat' => trim(($row['alamat'] ?? '').', '.($row['kelurahan'] ?? '').', '.($row['kecamatan'] ?? '').', '.($row['kota'] ?? '')),
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => trim($row['jk'] ?? ''),
            'pendidikan' => trim($row['pendidikan'] ?? ''),
            'pekerjaan' => trim($row['pekerjaan'] ?? ''),
            'no_hp' => trim($row['hp'] ?? ''),
            'no_bpjs' => trim($row['no_kartu'] ?? ''),
            'tipe_pasien' => $tipePasien,
        ];

        // Bersihkan elemen array yang kosong agar tidak menimpa data valid di database dengan string kosong
        $dataPasien = array_filter($dataPasien, function ($val) {
            return $val !== '' && $val !== null && $val !== ', , , ';
        });

        if ($pasien) {
            // Update data pasien dengan data terbaru dari Excel (koreksi nama, alamat, dll)
            $pasien->update($dataPasien);
            $this->summary['pasien_existing']++;
        } else {
            // Buat pasien baru
            $dataPasien['nik'] = $nik;
            $pasien = Pasien::create($dataPasien);
            $this->summary['pasien_baru']++;
        }

        // === Cek duplikat kunjungan (pasien_id + tanggal_periksa) ===
        $existingPemeriksaan = Pemeriksaan::where('pasien_id', $pasien->id)
            ->where('tanggal_periksa', $tanggalPeriksa)
            ->exists();

        if ($existingPemeriksaan) {
            $this->summary['kunjungan_skip']++;

            return;
        }

        // === Cek/Buat Nakes (pegawai) ===
        $nakesId = null;
        $nakesNama = trim($row['nakes'] ?? '');
        if (! empty($nakesNama)) {
            $nakes = Pegawai::firstOrCreate(
                ['nama' => $nakesNama],
                ['jabatan' => 'Dokter']
            );
            $nakesId = $nakes->id;
        }

        // === Buat Pemeriksaan ===
        Pemeriksaan::create([
            'pasien_id' => $pasien->id,
            'tanggal_periksa' => $tanggalPeriksa,
            'keluhan' => trim($row['keluhan'] ?? ''),
            'kode_icd' => trim($row['icd_x'] ?? ''),
            'nama_diagnosa' => trim($row['keterangan'] ?? ''),
            'berat_badan' => $this->toFloat($row['berat_badan'] ?? null),
            'tinggi_badan' => $this->toFloat($row['tinggi_badan'] ?? null),
            'tensi_sistol' => $this->toInt($row['tensi_atas'] ?? null),
            'tensi_diastol' => $this->toInt($row['tensi_bawah'] ?? null),
            'respiratory_rate' => $this->toInt($row['respiratory_rate'] ?? null),
            'heart_rate' => $this->toInt($row['heart_rate'] ?? null),
            'temperature' => $this->toFloat($row['temperature'] ?? null),
            'nakes_id' => $nakesId,
        ]);

        $this->summary['kunjungan_baru']++;
    }

    /**
     * Parse tanggal dengan format DD-MM-YYYY (teks, bukan native Excel date).
     */
    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim((string) $value);

        // Format DD-MM-YYYY
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            try {
                return Carbon::createFromFormat('d-m-Y', $value)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        // Format YYYY-MM-DD (fallback jika Excel sudah convert)
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            try {
                return Carbon::parse($value)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        // Coba parse sebagai serial number Excel
        if (is_numeric($value)) {
            try {
                return Carbon::createFromTimestamp(($value - 25569) * 86400)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
