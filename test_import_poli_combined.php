<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Imports\PoliklinikImport;
use App\Models\Pasien;
use App\Models\Pemeriksaan;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\Facades\Excel;

// Bersihkan DB untuk test
Artisan::call('migrate:fresh');

$import1 = new PoliklinikImport;
Excel::import($import1, 'Dataset/Register PoliKlinik Semua(13820260922134223).xlsx');

$import2 = new PoliklinikImport;
Excel::import($import2, 'Dataset/MEDICAL CHECKUP POLI.xlsx');

echo "=== Hasil Import 1 (September) ===\n";
print_r($import1->summary);

echo "=== Hasil Import 2 (Jan - Aug) ===\n";
print_r($import2->summary);

echo "=== Total di Database ===\n";
echo 'Total Pasien: '.Pasien::count()."\n";
echo 'Total Kunjungan: '.Pemeriksaan::count()."\n";
