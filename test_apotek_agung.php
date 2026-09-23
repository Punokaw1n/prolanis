<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class TestImport implements ToArray, WithHeadingRow
{
    public $rows = [];

    public function array(array $array): void
    {
        $this->rows = $array;
    }
}

$import = new TestImport;
Excel::import($import, 'Dataset/Register Apotek(13820260922134107).xlsx');

foreach ($import->rows[0] as $row) {
    if (isset($row['nama']) && str_contains(strtoupper((string) $row['nama']), 'AGUNG MARSELAN')) {
        print_r($row);
        break;
    }
}
