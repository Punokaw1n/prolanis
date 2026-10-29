<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateObatUmumExport
{
    /**
     * Download template Excel untuk import Master Obat Umum SIMBOK
     * Format kolom 100% identik dengan dataset obat-umum-simbok.xlsx:
     * - Kolom A: No.
     * - Kolom B: Nama Komponen
     */
    public static function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        // Header kolom persis sesuai dataset obat-umum-simbok.xlsx
        $headers = ['No.', 'Nama Komponen'];
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

        // Baris contoh data sesuai format data obat SIMBOK
        $samples = [
            [1, 'ALLOPURINOL TABLET 100 MG'],
            [2, 'AMBROXOL 30 MG TABLET'],
            [3, 'AMITRIPTILIN HCL TAB SAL 25 MG'],
            [4, 'AMLODIPIN 10 MG TABLET'],
            [5, 'AMLODIPIN 5 MG TABLET'],
            [6, 'AMOKSISILIN KAPSUL 500 MG'],
            [7, 'ANTASIDA DOEN TAB, KOMB'],
            [8, 'PARACETAMOL TABLET 500 MG'],
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

        $fileName = 'obat-umum-simbok.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
