<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Imports\PoliklinikImport;
use Illuminate\Contracts\Console\Kernel;
use Maatwebsite\Excel\Facades\Excel;

$import = new PoliklinikImport;
Excel::import($import, 'Dataset/Register PoliKlinik Semua(13820260922134223).xlsx');

echo "Done.\n";
