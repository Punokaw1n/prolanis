<?php

namespace App\Imports;

use App\Models\Pasien;
use App\Models\PasienPrb;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PrbImport implements ToArray, WithHeadingRow
{
    /** @var array{pasien_baru: int, pasien_existing: int, riwayat_baru: int, riwayat_skip: int, baris_gagal: int, errors: list<string>} */
    public array $summary = [
        'pasien_baru' => 0,
        'pasien_existing' => 0,
        'riwayat_baru' => 0,
        'riwayat_skip' => 0,
        'baris_gagal' => 0,
        'errors' => [],
    ];

    public function array(array $rows): void
    {
        // Validasi awal
        foreach ($rows as $index => $row) {
            $nik = preg_replace('/[^0-9]/', '', $row['nik'] ?? '');
            $isInvalid = empty($nik) || strlen($nik) < 16 || str_ends_with($nik, '0');
            
            if ($isInvalid) {
                $nama = trim($row['nama'] ?? 'Tanpa Nama');
                $rowNumber = $index + 2;
                
                $alasan = 'tidak valid';
                if (empty($nik)) $alasan = 'kosong';
                elseif (strlen($nik) < 16) $alasan = 'kurang dari 16 digit';
                elseif (str_ends_with($nik, '0')) $alasan = 'berakhiran angka 0';
                
                throw ValidationException::withMessages([
                    'file' => "Upload dibatalkan! NIK {$alasan} pada pasien '{$nama}' di baris {$rowNumber} (NIK: {$nik}). Harap perbaiki NIK di file Excel.",
                ]);
            }
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

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
        // === Filter Keluhan (hanya ambil jika mengandung "ambil obat prb") ===
        $keluhan = strtolower(trim($row['keluhan'] ?? ''));
        if (!str_contains($keluhan, 'ambil obat prb')) {
            $this->summary['riwayat_skip']++;
            return;
        }

        // === Filter Poli (LANSIA / UMUM) ===
        $poli = strtoupper(trim($row['poli'] ?? $row['klinik'] ?? ''));
        if (!in_array($poli, ['LANSIA', 'UMUM'])) {
            $this->summary['riwayat_skip']++;
            return;
        }

        $nik = preg_replace('/[^0-9]/', '', $row['nik'] ?? '');

        // === Parse tanggal periksa ===
        $tanggalPeriksa = $this->parseDate($row['tanggal'] ?? '');
        if (! $tanggalPeriksa) {
            $this->summary['baris_gagal']++;
            $this->summary['errors'][] = "Baris {$rowNumber}: Format tanggal tidak valid '{$row['tanggal']}'.";
            return;
        }

        // === Parse tanggal lahir ===
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? '');

        // === Cek/Buat Pasien ===
        $pasien = Pasien::where('nik', $nik)->first();

        $noRm = trim($row['no_index'] ?? '');
        
        $dataPasien = [
            'no_rm' => $noRm,
            'nama' => trim($row['nama'] ?? ''),
            'alamat' => trim(($row['alamat'] ?? '').', '.($row['kelurahan'] ?? '').', '.($row['kecamatan'] ?? '').', '.($row['kota'] ?? '')),
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => trim($row['jk'] ?? ''),
            'pendidikan' => trim($row['pendidikan'] ?? ''),
            'pekerjaan' => trim($row['pekerjaan'] ?? ''),
            'no_hp' => preg_replace('/[^0-9]/', '', $row['hp'] ?? ''),
            'no_bpjs' => preg_replace('/[^0-9]/', '', $row['no_kartu'] ?? ''),
            'tipe_pasien' => 'PRB',
        ];

        // Bersihkan array kosong
        $dataPasien = array_filter($dataPasien, function ($val) {
            return $val !== '' && $val !== null && $val !== ', , , ';
        });

        if ($pasien) {
            $pasien->update($dataPasien);
            $this->summary['pasien_existing']++;
        } else {
            $dataPasien['nik'] = $nik;
            $pasien = Pasien::create($dataPasien);
            $this->summary['pasien_baru']++;
        }

        // === Daftarkan ke Program PRB ===
        $pasienPrb = PasienPrb::firstOrCreate(
            ['pasien_id' => $pasien->id],
            [
                'status_keaktifan' => 'aktif',
                'tanggal_terdaftar' => $tanggalPeriksa // gunakan tanggal visit sebagai tgl daftar jika baru
            ]
        );

        // === Cek duplikat riwayat PRB ===
        $existingRiwayat = $pasienPrb->riwayatPengambilan()
            ->where('tanggal_diambil', $tanggalPeriksa)
            ->where('sumber', 'import')
            ->exists();

        if ($existingRiwayat) {
            $this->summary['riwayat_skip']++;
            return;
        }

        // === Buat Riwayat Baru ===
        $pasienPrb->riwayatPengambilan()->create([
            'tanggal_diambil' => $tanggalPeriksa,
            'tanggal_jatuh_tempo' => null, // Harus diisi petugas nanti untuk jadwal berikutnya
            'sumber' => 'import',
            'dicatat_oleh' => auth()->id() ?? null,
        ]);

        $this->summary['riwayat_baru']++;
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;
        $value = trim((string) $value);

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            try { return Carbon::createFromFormat('d-m-Y', $value)->toDateString(); } catch (\Throwable) { return null; }
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            try { return Carbon::parse($value)->toDateString(); } catch (\Throwable) { return null; }
        }
        if (is_numeric($value)) {
            try { return Carbon::createFromTimestamp(($value - 25569) * 86400)->toDateString(); } catch (\Throwable) { return null; }
        }
        return null;
    }
}
