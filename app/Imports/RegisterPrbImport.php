<?php

namespace App\Imports;

use App\Models\MasterObat;
use App\Models\Pasien;
use App\Models\PasienPrb;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RegisterPrbImport implements ToArray, WithCalculatedFormulas
{
    /** @var array{pasien_baru: int, pasien_existing: int, riwayat_baru: int, riwayat_skip: int, obat_baru: int, baris_gagal: int, errors: list<string>} */
    public array $summary = [
        'pasien_baru' => 0,
        'pasien_existing' => 0,
        'riwayat_baru' => 0,
        'riwayat_skip' => 0,
        'obat_baru' => 0,
        'baris_gagal' => 0,
        'errors' => [],
    ];

    /** @var array<string, bool> */
    private array $cachedMasterObat = [];

    /** @var array<int, list<string>> */
    private array $dropdownDrugsByRow = [];

    public function __construct(?string $filePath = null)
    {
        if ($filePath && file_exists($filePath)) {
            $this->loadDropdownDrugs($filePath);
        }
    }

    private function loadDropdownDrugs(string $filePath): void
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getSheetByName('PRB 2026') ?? $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            for ($r = 3; $r <= $highestRow; $r++) {
                $cell = $sheet->getCell("G$r");
                if ($cell->hasDataValidation()) {
                    $formula = trim($cell->getDataValidation()->getFormula1(), '"\'');
                    if ($formula !== '') {
                        $items = array_filter(array_map('trim', explode(',', $formula)));
                        if (! empty($items)) {
                            $this->dropdownDrugsByRow[$r] = array_values($items);
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Abaikan dan gunakan fallback nilai sel
        }
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function array(array $rows): void
    {
        if (count($rows) < 3) {
            throw ValidationException::withMessages([
                'file' => 'File tidak memiliki data pasien yang cukup untuk diimpor.',
            ]);
        }

        // Validasi header di Row 0 dan Row 1
        $headerRow0 = strtolower(implode(' ', array_filter(array_map('strval', $rows[0] ?? []))));
        $headerRow1 = strtolower(implode(' ', array_filter(array_map('strval', $rows[1] ?? []))));
        $combinedHeader = $headerRow0.' '.$headerRow1;

        if (! str_contains($combinedHeader, 'nik') || ! str_contains($combinedHeader, 'kartu bpjs') || ! str_contains($combinedHeader, 'nama pasien')) {
            throw ValidationException::withMessages([
                'file' => 'Format file tidak sesuai! Pastikan Anda mengunggah file Register Pasien PRB dengan kolom NIK, No. Kartu BPJS, Nama Pasien, dan Matriks Bulanan.',
            ]);
        }

        // Iterasi mulai baris data (index 2 / Baris 3 di Excel)
        foreach ($rows as $index => $row) {
            if ($index < 2) {
                continue; // Lewati baris header 1 dan 2
            }

            $rowNumber = $index + 1;

            $nama = trim((string) ($row[3] ?? ''));
            $nikRaw = trim((string) ($row[1] ?? ''));
            $bpjsRaw = trim((string) ($row[2] ?? ''));

            // Abaikan baris kosong di bagian bawah template
            if ($nama === '' && $nikRaw === '' && $bpjsRaw === '') {
                continue;
            }

            try {
                $this->processRow($row, $rowNumber);
            } catch (\Throwable $e) {
                $this->summary['baris_gagal']++;
                $this->summary['errors'][] = "Baris {$rowNumber} (".($nama ?: 'Tanpa Nama')."): {$e->getMessage()}";
            }
        }
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function processRow(array $row, int $rowNumber): void
    {
        $nik = preg_replace('/[^0-9]/', '', (string) ($row[1] ?? ''));
        $nama = trim((string) ($row[3] ?? ''));

        if (empty($nik) || strlen($nik) !== 16) {
            throw new \Exception('NIK tidak valid atau tidak 16 digit angka: '.($nik ?: '(kosong)'));
        }

        if (empty($nama)) {
            throw new \Exception('Nama pasien tidak boleh kosong.');
        }

        $noBpjs = preg_replace('/[^0-9]/', '', (string) ($row[2] ?? ''));
        $alamat = trim((string) ($row[4] ?? ''));
        $jkRaw = strtoupper(trim((string) ($row[8] ?? '')));
        $jk = in_array($jkRaw, ['L', 'P']) ? $jkRaw : ($jkRaw === 'LAKI-LAKI' ? 'L' : ($jkRaw === 'PEREMPUAN' ? 'P' : null));
        $noHp = $this->normalizePhoneNumber($row[9] ?? null);

        // Tanggal Lahir (parse atau ambil dari NIK jika format teks umur seperti '72 th')
        $tanggalLahir = $this->parseDate($row[7] ?? null) ?? $this->extractBirthDateFromNik($nik);

        // Tanggal Pertama PRB
        $tanggalPertamaPrb = $this->parseDate($row[5] ?? null);

        // 1. Cek / Buat Pasien
        $pasien = Pasien::where('nik', $nik)->first();

        $dataPasien = [
            'nama' => $nama,
            'no_bpjs' => $noBpjs ?: null,
            'alamat' => $alamat ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jk,
            'no_hp' => $noHp,
        ];

        if ($pasien) {
            if (empty($pasien->tipe_pasien)) {
                $dataPasien['tipe_pasien'] = 'PRB';
            }
            $pasien->update(array_filter($dataPasien, fn ($v) => ! is_null($v) && $v !== ''));
            $this->summary['pasien_existing']++;
        } else {
            $dataPasien['nik'] = $nik;
            $dataPasien['tipe_pasien'] = 'PRB';
            $dataPasien['status_keaktifan'] = 'aktif';
            $pasien = Pasien::create($dataPasien);
            $this->summary['pasien_baru']++;
        }

        // Diagnosa PRB (Kolom K = $row[10])
        $diagnosa = $this->extractDiagnosisCode($row[10] ?? null);

        // 2. Hubungkan ke Pasien PRB
        $pasienPrb = PasienPrb::firstOrCreate(
            ['pasien_id' => $pasien->id],
            [
                'status_keaktifan' => 'aktif',
                'diagnosa' => $diagnosa,
                'tanggal_terdaftar' => $tanggalPertamaPrb ?? Carbon::create(2026, 1, 1)->toDateString(),
            ]
        );

        if ($diagnosa && empty($pasienPrb->diagnosa)) {
            $pasienPrb->update(['diagnosa' => $diagnosa]);
        }

        // 3. Parsing Seluruh Obat PRB (Ambil dari Dropdown Data Validation jika ada)
        $rawDrugs = $this->dropdownDrugsByRow[$rowNumber] ?? [(string) ($row[6] ?? '')];
        $parsedObats = [];
        foreach ($rawDrugs as $drugItem) {
            foreach ($this->parseObatPrb($drugItem) as $parsed) {
                $parsedObats[] = $parsed;
            }
        }

        // 4. Matriks Riwayat Pengambilan Bulanan (Januari s.d. Desember 2026)
        $tglTahunLalu = $this->parseDate($row[11] ?? null);
        $lastAmbilDate = $tglTahunLalu;

        for ($month = 1; $month <= 12; $month++) {
            $colDateIdx = 12 + ($month - 1) * 2;
            $colStatusIdx = 13 + ($month - 1) * 2;

            $rawDate = $row[$colDateIdx] ?? null;
            $rawStatus = strtoupper(trim((string) ($row[$colStatusIdx] ?? '')));

            $isRujuk = (strtolower(trim((string) $rawDate)) === 'rujuk' || str_contains($rawStatus, 'RUJUK'));
            $tglAmbil = $this->parseDate($rawDate, 2026, $month);

            // Perkiraan jatuh tempo: 30 hari setelah pengambilan sebelumnya
            $tglJatuhTempo = $lastAmbilDate ? Carbon::parse($lastAmbilDate)->addDays(30)->toDateString() : Carbon::create(2026, $month, 1)->toDateString();

            if ($tglAmbil) {
                $lastAmbilDate = $tglAmbil;

                // Cek duplikat riwayat (dicek per tanggal dan per kode diagnosa jika ada)
                $existingRiwayat = $pasienPrb->riwayatPengambilan()
                    ->whereDate('tanggal_diambil', $tglAmbil)
                    ->when($diagnosa, function ($q) use ($diagnosa) {
                        return $q->where('kode_diagnosa', $diagnosa);
                    })
                    ->first();

                if (! $existingRiwayat) {
                    $riwayat = $pasienPrb->riwayatPengambilan()->create([
                        'tanggal_jatuh_tempo' => $tglJatuhTempo,
                        'tanggal_diambil' => $tglAmbil,
                        'kode_diagnosa' => $diagnosa,
                        'diagnosa' => ! empty($row[10]) ? trim((string) $row[10]) : null,
                        'sumber' => 'import',
                        'catatan' => $isRujuk ? 'Dirujuk kembali ke RS/FKRTL' : null,
                        'dicatat_oleh' => auth()->id() ?? null,
                    ]);

                    $this->summary['riwayat_baru']++;

                    // Simpan obat ke riwayat
                    foreach ($parsedObats as $obatData) {
                        $riwayat->obatPrb()->create([
                            'nama_obat' => $obatData['nama'],
                            'kategori' => 'prb',
                            'signa' => $obatData['signa'],
                            'jumlah_hari' => $obatData['jumlah_hari'],
                            'jumlah_obat' => $obatData['jumlah_obat'],
                            // 'aturan_minum' => $obatData['aturan_minum'] ?? null,
                            'sumber' => 'import',
                        ]);
                        $this->summary['obat_baru']++;

                        if (! isset($this->cachedMasterObat[$obatData['nama']])) {
                            MasterObat::firstOrCreate(
                                ['nama_obat' => $obatData['nama'], 'kategori' => 'prb'],
                                ['sumber' => 'import', 'is_active' => true]
                            );
                            $this->cachedMasterObat[$obatData['nama']] = true;
                        }
                    }
                } else {
                    $this->summary['riwayat_skip']++;
                }
            } elseif ($isRujuk) {
                // Catat status rujukan jika ada
                $existingRujuk = $pasienPrb->riwayatPengambilan()
                    ->whereYear('tanggal_jatuh_tempo', 2026)
                    ->whereMonth('tanggal_jatuh_tempo', $month)
                    ->when($diagnosa, function ($q) use ($diagnosa) {
                        return $q->where('kode_diagnosa', $diagnosa);
                    })
                    ->whereNotNull('catatan')
                    ->first();

                if (! $existingRujuk) {
                    $pasienPrb->riwayatPengambilan()->create([
                        'tanggal_jatuh_tempo' => $tglJatuhTempo,
                        'tanggal_diambil' => null,
                        'kode_diagnosa' => $diagnosa,
                        'diagnosa' => ! empty($row[10]) ? trim((string) $row[10]) : null,
                        'sumber' => 'import',
                        'catatan' => 'Dirujuk kembali ke FKRTL (Rumah Sakit)',
                        'dicatat_oleh' => auth()->id() ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * @return list<array{nama: string, signa: string, jumlah_obat: int, jumlah_hari: int, aturan_minum: string, kategori: string}>
     */
    private function parseObatPrb(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $lines = preg_split('/[\r\n]+|\s*\+\s*/', $raw);
        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $qty = 30;
            if (preg_match('/\((\d+)[^\)]*\)/', $line, $m)) {
                $qty = (int) $m[1];
                $line = trim(str_replace($m[0], '', $line));
            }

            $signa = '1 x 1';
            if (preg_match('/(\d+\s*[xX]\s*[\d\/\.]+)/', $line, $m)) {
                $signa = trim($m[1]);
                $line = trim(str_replace($m[0], '', $line));
            }

            $namaObat = trim($line, ' ,;-');
            if ($namaObat === '') {
                $namaObat = 'Obat PRB Rutin';
            }

            $result[] = [
                'nama' => $namaObat,
                'signa' => $signa,
                'jumlah_obat' => $qty,
                'jumlah_hari' => min($qty, 30),
                // 'aturan_minum' => 'Sesudah Makan',
                'kategori' => 'prb',
            ];
        }

        return $result;
    }

    private function parseDate(mixed $val, ?int $expectedYear = null, ?int $expectedMonth = null): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        if (is_numeric($val) && $val > 1000) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject((float) $val))->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        $val = trim((string) $val);
        if ($val === '' || strtolower($val) === 'rujuk') {
            return null;
        }

        // Koreksi typo umum di dataset
        if ($val === '25/9226') {
            return '2026-09-25';
        }
        if ($val === '17/77/26') {
            return '2026-07-17';
        }
        if ($val === '10/126') {
            return '2026-10-10';
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $val, $m)) {
            $d = (int) $m[1];
            $mo = (int) $m[2];
            $y = (int) $m[3];
            if ($y < 100) {
                $y = ($y > 50) ? (1900 + $y) : (2000 + $y);
            }
            try {
                return Carbon::createFromDate($y, $mo, $d)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($val)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractBirthDateFromNik(string $nik): ?string
    {
        $cleanNik = preg_replace('/[^0-9]/', '', $nik);
        if (strlen($cleanNik) !== 16) {
            return null;
        }

        $day = (int) substr($cleanNik, 6, 2);
        $month = (int) substr($cleanNik, 8, 2);
        $year2 = (int) substr($cleanNik, 10, 2);

        if ($day > 40) {
            $day -= 40;
        }

        $year = ($year2 > 26) ? (1900 + $year2) : (2000 + $year2);

        try {
            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return Carbon::createFromDate($year, $month, $day)->toDateString();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function normalizePhoneNumber(mixed $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/[\s\-\(\)\+]+/', '', (string) $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone ?: null;
    }

    private function extractDiagnosisCode(mixed $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $raw = trim((string) $raw);
        if ($raw === '' || $raw === '-') {
            return null;
        }

        $textMap = [
            'ASTHMA BRONCHIALE' => 'J45',
            'REZIDUAL SKIZOFRENIA' => 'F20.5',
            'HHD' => 'I11.9',
            'DC' => 'I50',
        ];

        $upperRaw = strtoupper($raw);
        if (isset($textMap[$upperRaw])) {
            return $textMap[$upperRaw];
        }

        if (preg_match('/^([A-Z]\d{2}(?:\.\d+)?)\s*-\s*/i', $raw, $m)) {
            return strtoupper($m[1]);
        }

        $clean = str_replace(['/', '\\'], '.', $raw);

        preg_match_all('/([A-Za-z]\d{2}(?:[0-9]|\.\d+)?)/', $clean, $matches);

        if (! empty($matches[1])) {
            $codes = [];
            foreach ($matches[1] as $c) {
                $c = strtoupper($c);
                if (preg_match('/^([A-Z]\d{2})(\d)$/', $c, $cm)) {
                    $c = $cm[1].'.'.$cm[2];
                }
                $codes[] = $c;
            }
            $uniqueCodes = array_unique($codes);

            return implode(', ', $uniqueCodes);
        }

        return strtoupper($raw);
    }
}
