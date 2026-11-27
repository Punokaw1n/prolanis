<?php

namespace App\Imports;

use App\Models\PasienSrk;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PasienSrkImport implements ToCollection, WithHeadingRow
{
    /** @var array{imported: int, existing: int, skipped: int} */
    public array $summary = [
        'imported' => 0,
        'existing' => 0,
        'skipped' => 0,
    ];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            $nama = trim((string) (
                $row['nama']
                ?? $row['nama_pasien']
                ?? $row['nama_lengkap']
                ?? $this->findByKeyPattern($rowArray, ['nama', 'name'])
                ?? ''
            ));

            $rawPhone = trim((string) (
                $row['no_telepon']
                ?? $row['no_hp']
                ?? $row['nomor_hp']
                ?? $row['telepon']
                ?? $row['nomor_telepon']
                ?? $row['no_handphone']
                ?? $row['nomor_handphone']
                ?? $row['no_wa']
                ?? $row['nomor_wa']
                ?? $row['no_telp']
                ?? $row['telp']
                ?? $row['hp']
                ?? $row['wa']
                ?? $row['handphone']
                ?? $row['phone']
                ?? $this->findByKeyPattern($rowArray, ['hp', 'telp', 'telepon', 'wa', 'phone', 'handphone', 'kontak'])
                ?? ''
            ));

            $noHp = $this->normalizePhone($rawPhone);

            if ($nama === '' || $noHp === null || strlen($noHp) < 8) {
                $this->summary['skipped']++;

                continue;
            }

            $record = PasienSrk::firstOrCreate([
                'nama' => $nama,
                'no_hp' => $noHp,
            ]);

            if ($record->wasRecentlyCreated) {
                $this->summary['imported']++;
            } else {
                $this->summary['existing']++;
            }
        }
    }

    /**
     * @param  array<string|int, mixed>  $row
     * @param  array<int, string>  $keywords
     */
    private function findByKeyPattern(array $row, array $keywords): ?string
    {
        foreach ($row as $key => $value) {
            $normalizedKey = strtolower(trim((string) $key));
            foreach ($keywords as $keyword) {
                if (str_contains($normalizedKey, $keyword) && trim((string) $value) !== '') {
                    return trim((string) $value);
                }
            }
        }

        return null;
    }

    private function normalizePhone(string $phone): ?string
    {
        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if ($digits === '' || $digits === null) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }
}
