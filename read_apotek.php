<?php

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require __DIR__.'/vendor/autoload.php';

$reader = new Xlsx;
$spreadsheet = $reader->load('dataset/Register Apotek(13820260922134107).xlsx');
$sheet = $spreadsheet->getActiveSheet();

echo 'Sheet name: '.$sheet->getTitle().PHP_EOL;
echo 'Rows: '.$sheet->getHighestRow().PHP_EOL;
echo 'Cols: '.$sheet->getHighestColumn().PHP_EOL;

echo PHP_EOL.'=== HEADER ROW ==='.PHP_EOL;
foreach ($sheet->getRowIterator(1, 1) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ($cellIterator as $cell) {
        echo $cell->getColumn().': ['.$cell->getValue().']'.PHP_EOL;
    }
}

echo PHP_EOL.'=== ROW 2 (first data) ==='.PHP_EOL;
foreach ($sheet->getRowIterator(2, 2) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ($cellIterator as $cell) {
        echo $cell->getColumn().': ['.$cell->getValue().']'.PHP_EOL;
    }
}

echo PHP_EOL.'=== ROW 3 ==='.PHP_EOL;
foreach ($sheet->getRowIterator(3, 3) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ($cellIterator as $cell) {
        echo $cell->getColumn().': ['.$cell->getValue().']'.PHP_EOL;
    }
}
