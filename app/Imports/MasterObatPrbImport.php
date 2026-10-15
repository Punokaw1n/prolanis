<?php

namespace App\Imports;

use App\Models\MasterObat;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterObatPrbImport implements ToArray, WithHeadingRow
{
    /** @var array{obat_baru: int, obat_existing: int, baris_gagal: int, errors: list<string>} */
    public array $summary = [
        'obat_baru' => 0,
        'obat_existing' => 0,
        'baris_gagal' => 0,
        'errors' => [],
    ];

    public function array(array $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $nama = trim($row['nama_barang'] ?? $row['nama_obat'] ?? $row['nama'] ?? '');

                if (empty($nama)) {
                    continue;
                }

                $existing = MasterObat::where('nama_obat', $nama)
                    ->where('kategori', 'prb')
                    ->first();

                if ($existing) {
                    $this->summary['obat_existing']++;
                } else {
                    MasterObat::create([
                        'nama_obat' => $nama,
                        'kategori' => 'prb',
                        'sumber' => 'obat-prb',
                        'is_active' => true,
                    ]);
                    $this->summary['obat_baru']++;
                }
            } catch (\Throwable $e) {
                $this->summary['baris_gagal']++;
                $this->summary['errors'][] = "Baris {$rowNumber}: {$e->getMessage()}";
            }
        }
    }
}
