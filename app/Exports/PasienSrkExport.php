<?php

namespace App\Exports;

use App\Models\PasienSrk;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PasienSrkExport
{
    /**
     * @param  Collection<int, PasienSrk>  $items
     */
    public static function download(Collection $items, string $status = ''): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pasien SRK');

        $headers = ['No', 'Nama Pasien', 'No. Telepon / WA', 'Status', 'Terakhir Dihubungi'];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $rowNum = 2;
        foreach ($items as $index => $item) {
            $sudah = $item->terakhir_dihubungi !== null;

            $sheet->setCellValue("A{$rowNum}", $index + 1);
            $sheet->setCellValue("B{$rowNum}", $item->nama);
            $sheet->setCellValueExplicit("C{$rowNum}", (string) $item->no_hp, DataType::TYPE_STRING);
            $sheet->setCellValue("D{$rowNum}", $sudah ? 'Sudah Dihubungi' : 'Belum Dihubungi');
            $sheet->setCellValue("E{$rowNum}", $sudah ? $item->terakhir_dihubungi->format('d/m/Y H:i') : '-');

            $rowNum++;
        }

        if ($rowNum > 2) {
            $lastRow = $rowNum - 1;
            $sheet->getStyle("A2:E{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(22);

        $statusSlug = match ($status) {
            'sudah' => 'sudah-dihubungi',
            'belum' => 'belum-dihubungi',
            default => 'semua',
        };
        $filename = 'pasien-srk-'.$statusSlug.'-'.now()->format('Y-m-d').'.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer): void {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
