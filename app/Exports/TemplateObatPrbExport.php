<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateObatPrbExport
{
    /**
     * Download template Excel untuk import Master Obat PRB
     * Format kolom 100% identik dengan dataset obat-PRB.xlsx:
     * - Kolom A: No.
     * - Kolom B: Nama Barang
     */
    public static function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        // Header kolom persis sesuai dataset obat-PRB.xlsx
        $headers = ['No.', 'Nama Barang'];
        $sheet->fromArray($headers, null, 'A1');

        // Styling Header
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D9488'], // Teal 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0F766E'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Baris contoh data sesuai format data obat PRB
        $samples = [
            [1, 'Acarbose 100mg'],
            [2, 'Amlodipin 10 mg'],
            [3, 'Allopurinol 100 mg'],
            [4, 'Atorvastatin 20 mg'],
            [5, 'Bisoprolol 5 mg'],
            [6, 'Candesartan 16 mg'],
            [7, 'Clopidogrel 75 mg'],
            [8, 'Glimepirid 2 mg'],
        ];
        $sheet->fromArray($samples, null, 'A2');

        // Styling data cells
        $lastRow = count($samples) + 1;
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A2:B{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // Auto column width
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);

        $fileName = 'obat-PRB.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
