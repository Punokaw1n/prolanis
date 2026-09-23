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
Excel::import($import, 'Dataset/Register PoliKlinik Semua(13820260922134223).xlsx');

echo "HEADERS:\n";
print_r(array_keys($import->rows[0]));
echo "\nFIRST ROW:\n";
print_r($import->rows[0]);
