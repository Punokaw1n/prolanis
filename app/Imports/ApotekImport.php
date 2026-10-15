<?php

namespace App\Imports;

use App\Models\Pasien;
use App\Models\PemberianObat;
use App\Models\Pemeriksaan;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ApotekImport implements ToArray, WithHeadingRow
{
    /** @var array{obat_disimpan: int, baris_gagal: int, errors: list<string>} */
    public array $summary = [
        'obat_disimpan' => 0,
        'baris_gagal' => 0,
        'errors' => [],
    ];

    public function array(array $rows): void
    {
        // Validasi awal: Jika ada NIK yang kosong atau tidak tepat 16 digit, batalkan!
        foreach ($rows as $index => $row) {
            $nik = preg_replace('/[^0-9]/', '', $row['nik'] ?? $row['no_bpjs'] ?? $row['no_kartu'] ?? '');

            $isInvalid = empty($nik) || strlen($nik) !== 16;

            if ($isInvalid) {
                $nama = trim($row['nama'] ?? 'Tanpa Nama');
                $rowNumber = $index + 2;

                $alasan = empty($nik) ? 'kosong' : (strlen($nik) < 16 ? 'kurang dari 16 digit' : 'lebih dari 16 digit');

                throw ValidationException::withMessages([
                    'file' => "Upload dibatalkan! NIK/No BPJS {$alasan} pada pasien '{$nama}' di baris {$rowNumber} (NIK: {$nik}). Harap perbaiki NIK di file Excel (pastikan cell berformat Text dan tepat 16 digit).",
                ]);
            }
        }

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
        // === Ambil & bersihkan NIK dari karakter non-angka (seperti petik) ===
        // Cari dari berbagai kemungkinan nama kolom (nik, no_bpjs, no_kartu, dll)
        $nik = preg_replace('/[^0-9]/', '', $row['nik'] ?? $row['no_bpjs'] ?? $row['no_kartu'] ?? '');
        if (empty($nik)) {
            $nama = trim($row['nama'] ?? 'Tanpa Nama');
            $this->summary['baris_gagal']++;
            $this->summary['errors'][] = "Baris {$rowNumber}: NIK/No BPJS kosong untuk pasien '{$nama}', baris dilewati.";

            return;
        }

        // === Parse tanggal (format DD-MM-YYYY) ===
        $tanggal = $this->parseDate($row['tanggal'] ?? '');
        if (! $tanggal) {
            $this->summary['baris_gagal']++;
            $this->summary['errors'][] = "Baris {$rowNumber}: Format tanggal tidak valid '{$row['tanggal']}'.";

            return;
        }

        // === Cek Pasien ===
        $pasien = Pasien::where('nik', $nik)->first();
        if (! $pasien) {
            $nama = trim($row['nama'] ?? 'Tanpa Nama');
            $this->summary['baris_gagal']++;
            $this->summary['errors'][] = "Baris {$rowNumber}: Pasien '{$nama}' dengan NIK {$nik} tidak ditemukan di database. Pastikan data Poliklinik diimport terlebih dahulu.";

            return;
        }

        // === Cek Pemeriksaan (Kunjungan) ===
        $pemeriksaan = Pemeriksaan::where('pasien_id', $pasien->id)
            ->where('tanggal_periksa', $tanggal)
            ->first();

        if (! $pemeriksaan) {
            $nama = trim($row['nama'] ?? 'Tanpa Nama');
            $this->summary['baris_gagal']++;
            $this->summary['errors'][] = "Baris {$rowNumber}: Kunjungan tanggal {$tanggal} untuk pasien '{$nama}' (NIK {$nik}) tidak ditemukan.";

            return;
        }

        // === Proses Obat ===
        // Cari dari berbagai kemungkinan nama kolom (obat, nama_obat, daftar_obat, dll)
        $obatString = trim($row['obat'] ?? $row['nama_obat'] ?? $row['obat_yang_diberikan'] ?? '');
        if (empty($obatString)) {
            // Jika kosong, tidak usah tambah obat, bukan error besar tapi tidak ada obat yg ditambah
            return;
        }

        // Split berdasarkan ';'
        $obatList = explode(';', $obatString);
        foreach ($obatList as $namaObat) {
            $namaObat = trim($namaObat);
            if (! empty($namaObat)) {
                // Gunakan firstOrCreate untuk menghindari duplikasi jika file di-import dua kali
                $created = PemberianObat::firstOrCreate([
                    'pemeriksaan_id' => $pemeriksaan->id,
                    'nama_obat' => $namaObat,
                ], [
                    'tanggal_diberikan' => $tanggal,
                ]);

                if ($created->wasRecentlyCreated) {
                    $this->summary['obat_disimpan']++;
                }
            }
        }
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
}
