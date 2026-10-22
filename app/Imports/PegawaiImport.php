<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PegawaiImport implements ToArray, WithCalculatedFormulas
{
    /** @var array{petugas_baru: int, petugas_existing: int, baris_gagal: int, errors: list<string>} */
    public array $summary = [
        'petugas_baru' => 0,
        'petugas_existing' => 0,
        'baris_gagal' => 0,
        'errors' => [],
    ];

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function array(array $rows): void
    {
        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'File tidak memiliki data pegawai yang cukup untuk diimpor.',
            ]);
        }

        // Cari baris header (misal di baris 5 / index 4)
        $headerRowIndex = null;
        $colMap = [
            'nama' => 1,
            'jenis_kelamin' => 2,
            'nip' => 3,
            'jabatan' => 4,
            'jabatan_fungsional' => 5,
            'nik' => 8,
            'ttl' => 9,
            'alamat_ktp' => 10,
            'alamat_domisili' => 11,
            'no_hp' => 12,
            'no_bpjs' => 13,
            'pendidikan' => 14,
            'email' => 21,
        ];

        foreach (array_slice($rows, 0, 10, true) as $rIdx => $rData) {
            $rowStr = strtolower(implode(' ', array_filter(array_map('strval', $rData))));
            if (str_contains($rowStr, 'nama') && (str_contains($rowStr, 'nik') || str_contains($rowStr, 'nip'))) {
                $headerRowIndex = $rIdx;
                // Petakan index kolom secara dinamis
                foreach ($rData as $cIdx => $cVal) {
                    $cStr = strtolower(trim((string) $cVal));
                    if ($cStr === 'nama' || $cStr === 'nama pegawai' || $cStr === 'nama lengkap') {
                        $colMap['nama'] = $cIdx;
                    } elseif ($cStr === 'nik' || str_contains($cStr, 'nik')) {
                        $colMap['nik'] = $cIdx;
                    } elseif ($cStr === 'nip' || str_contains($cStr, 'nip')) {
                        $colMap['nip'] = $cIdx;
                    } elseif (str_contains($cStr, 'kelamin') || str_contains($cStr, 'gender')) {
                        $colMap['jenis_kelamin'] = $cIdx;
                    } elseif (str_contains($cStr, 'fungsional')) {
                        $colMap['jabatan_fungsional'] = $cIdx;
                    } elseif ($cStr === 'jabatan' || str_contains($cStr, 'jabatan')) {
                        $colMap['jabatan'] = $cIdx;
                    } elseif (str_contains($cStr, 'lahir') || str_contains($cStr, 'ttl')) {
                        $colMap['ttl'] = $cIdx;
                    } elseif (str_contains($cStr, 'domisili')) {
                        $colMap['alamat_domisili'] = $cIdx;
                    } elseif (str_contains($cStr, 'ktp') || str_contains($cStr, 'alamat')) {
                        $colMap['alamat_ktp'] = $cIdx;
                    } elseif (str_contains($cStr, 'hp') || str_contains($cStr, 'telepon') || str_contains($cStr, 'wa')) {
                        $colMap['no_hp'] = $cIdx;
                    } elseif (str_contains($cStr, 'bpjs')) {
                        $colMap['no_bpjs'] = $cIdx;
                    } elseif (str_contains($cStr, 'pendidikan')) {
                        $colMap['pendidikan'] = $cIdx;
                    } elseif (str_contains($cStr, 'email')) {
                        $colMap['email'] = $cIdx;
                    }
                }
                break;
            }
        }

        if ($headerRowIndex === null) {
            throw ValidationException::withMessages([
                'file' => 'Format file tidak sesuai! Pastikan file Excel memuat kolom NAMA, NIK, NIP, JABATAN, dan TEMPAT/TGL LAHIR.',
            ]);
        }

        // Mulai memproses baris data di bawah header
        $startDataIndex = $headerRowIndex + 1;

        foreach ($rows as $index => $row) {
            if ($index < $startDataIndex) {
                continue;
            }

            $rowNumber = $index + 1;

            $nama = trim((string) ($row[$colMap['nama']] ?? ''));
            $nikRaw = trim((string) ($row[$colMap['nik']] ?? ''));
            $nipRaw = trim((string) ($row[$colMap['nip']] ?? ''));

            // Abaikan baris kosong
            if ($nama === '' && $nikRaw === '' && $nipRaw === '') {
                continue;
            }

            try {
                $this->processRow($row, $rowNumber, $colMap);
            } catch (\Throwable $e) {
                $this->summary['baris_gagal']++;
                $this->summary['errors'][] = "Baris {$rowNumber} (".($nama ?: 'Tanpa Nama')."): {$e->getMessage()}";
            }
        }
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int>  $colMap
     */
    private function processRow(array $row, int $rowNumber, array $colMap): void
    {
        $nama = trim((string) ($row[$colMap['nama']] ?? ''));
        if (empty($nama)) {
            throw new \Exception('Nama pegawai wajib diisi.');
        }

        // Bersihkan NIK
        $nik = preg_replace('/[^0-9]/', '', (string) ($row[$colMap['nik']] ?? ''));
        if (empty($nik) || strlen($nik) !== 16) {
            throw new \Exception("NIK tidak valid ('{$row[$colMap['nik']]}'). NIK harus 16 digit angka.");
        }

        // Bersihkan NIP (opsional untuk non-PNS)
        $nip = preg_replace('/[^0-9]/', '', (string) ($row[$colMap['nip']] ?? ''));
        if (empty($nip) || strlen($nip) < 9) {
            $nip = null;
        }

        // Jabatan
        $jabatan = trim((string) ($row[$colMap['jabatan']] ?? ''));
        if (empty($jabatan)) {
            $jabatan = trim((string) ($row[$colMap['jabatan_fungsional']] ?? 'Staf Puskesmas'));
        }

        // Tanggal Lahir dari TTL
        $rawTtl = (string) ($row[$colMap['ttl']] ?? '');
        $tanggalLahir = self::parseTanggalLahir($rawTtl);

        // Alamat (utamakan domisili, fallback ke KTP)
        $alamat = trim((string) ($row[$colMap['alamat_domisili']] ?? ''));
        if (empty($alamat)) {
            $alamat = trim((string) ($row[$colMap['alamat_ktp']] ?? ''));
        }

        // Kontak HP
        $noHp = $this->normalizePhoneNumber((string) ($row[$colMap['no_hp']] ?? ''));

        // Email
        $rawEmail = trim((string) ($row[$colMap['email']] ?? ''));
        $email = filter_var($rawEmail, FILTER_VALIDATE_EMAIL) ? strtolower($rawEmail) : null;

        // Jenis Kelamin
        $rawJk = strtoupper(trim((string) ($row[$colMap['jenis_kelamin']] ?? '')));
        $jenisKelamin = str_contains($rawJk, 'PEREMPUAN') || str_contains($rawJk, 'WANITA') || $rawJk === 'P' ? 'P' : (str_contains($rawJk, 'LAKI') || $rawJk === 'L' ? 'L' : null);

        // Pendidikan
        $pendidikan = trim((string) ($row[$colMap['pendidikan']] ?? ''));

        // No BPJS
        $noBpjs = trim((string) ($row[$colMap['no_bpjs']] ?? ''));

        // Tentukan peran sistem (admin untuk manajemen/pimpinan, petugas untuk klinis & operasional)
        $peran = $this->determineRole($jabatan);

        // Password default dari tanggal lahir: DDMMYYYY (misal: 25091989), fallback ke '12345678'
        $defaultPassword = $tanggalLahir
            ? Carbon::parse($tanggalLahir)->format('dmY')
            : '12345678';

        // 1. Simpan/Update Akun Pengguna (User)
        $user = User::where('nik', $nik)->first();

        if ($user) {
            $user->update([
                'nama' => $nama,
                'nip' => $nip ?: $user->nip,
                'jabatan' => $jabatan,
                'tanggal_lahir' => $tanggalLahir ?: $user->tanggal_lahir,
                'alamat' => $alamat ?: $user->alamat,
                'no_hp' => $noHp ?: $user->no_hp,
                'email' => $email ?: $user->email,
            ]);
            $this->summary['petugas_existing']++;
        } else {
            // Hindari duplikasi email jika ada user lain yang menggunakan email sama
            $emailToUse = $email;
            if ($emailToUse && User::where('email', $emailToUse)->exists()) {
                $emailToUse = null;
            }

            // Hindari duplikasi NIP jika ada user lain yang menggunakan NIP sama
            $nipToUse = $nip;
            if ($nipToUse && User::where('nip', $nipToUse)->exists()) {
                $nipToUse = null;
            }

            User::create([
                'nama' => $nama,
                'nik' => $nik,
                'nip' => $nipToUse,
                'tanggal_lahir' => $tanggalLahir,
                'alamat' => $alamat ?: null,
                'no_hp' => $noHp ?: null,
                'jabatan' => $jabatan,
                'email' => $emailToUse,
                'peran' => $peran,
                'password' => Hash::make($defaultPassword),
                'is_active' => true,
            ]);
            $this->summary['petugas_baru']++;
        }

        // 2. Sinkronkan ke tabel legacy Pegawai
        Pegawai::updateOrCreate(
            ['nik' => $nik],
            [
                'nama' => $nama,
                'jabatan' => $jabatan,
                'alamat' => $alamat ?: null,
                'tanggal_lahir' => $tanggalLahir,
                'jenis_kelamin' => $jenisKelamin,
                'pendidikan' => $pendidikan ?: null,
                'no_hp' => $noHp ?: null,
                'no_bpjs' => $noBpjs ?: null,
            ]
        );
    }

    /**
     * Tentukan peran (admin / petugas) berdasarkan nama jabatan
     */
    private function determineRole(string $jabatan): string
    {
        $adminKeywords = [
            'kepala puskesmas',
            'pj tu',
            'tata usaha',
            'administrator kesehatan',
            'analis data',
        ];

        $lower = strtolower($jabatan);
        foreach ($adminKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return 'admin';
            }
        }

        return 'petugas';
    }

    /**
     * Parsing tanggal lahir dari teks gabungan TEMPAT, TGL LAHIR
     */
    public static function parseTanggalLahir(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $bulanMap = [
            'januari' => 1, 'jan' => 1, 'january' => 1,
            'februari' => 2, 'febuari' => 2, 'feb' => 2, 'february' => 2,
            'maret' => 3, 'mar' => 3, 'march' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6, 'june' => 6,
            'juli' => 7, 'jul' => 7, 'july' => 7,
            'agustus' => 8, 'agu' => 8, 'agt' => 8, 'aug' => 8, 'august' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10, 'october' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12, 'december' => 12,
        ];

        // Ganti koma, garis miring, tanda hubung dengan spasi
        $clean = preg_replace('/[,\/\-]+/', ' ', $raw);

        // Pola Hari NamaBulan Tahun (e.g. 25 September 1989, 30-Maret-1973, 20 Febuari 1987)
        if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})/', $clean, $m)) {
            $day = (int) $m[1];
            $monthName = strtolower($m[2]);
            $year = (int) $m[3];

            if (isset($bulanMap[$monthName])) {
                $month = $bulanMap[$monthName];

                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Pola DD-MM-YYYY atau DD/MM/YYYY
        if (preg_match('/(\d{1,2})\s*[\/\-\.]\s*(\d{1,2})\s*[\/\-\.]\s*(\d{4})/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Normalisasi nomor telepon ke format lokal Indonesia
     */
    private function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '62')) {
            $clean = '0'.substr($clean, 2);
        }

        return $clean ?: null;
    }
}
