<?php

namespace App\Exports;

use App\Models\LogAktivitas;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogAktivitasExport
{
    /**
     * Download data log aktivitas ke dalam file Excel (.xlsx)
     */
    public static function download(?Builder $query = null, string $fileName = 'Backup_Log_Aktivitas.xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Log Aktivitas');

        // Header Kolom
        $headers = [
            'No.',
            'Waktu & Tanggal',
            'Aksi / Modul',
            'Nama Petugas',
            'NIK',
            'Jabatan / Peran',
            'Deskripsi Aktivitas',
            'Alamat IP',
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Styling Header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // Navy Blue (Blue 900)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '172554'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Ambil Data
        $logsQuery = $query ?? LogAktivitas::with('user')->latest();
        $logs = $logsQuery->get();

        $rowIndex = 2;
        $no = 1;

        foreach ($logs as $log) {
            $user = $log->user;
            $namaUser = $user ? $user->nama : 'Sistem / Guest';
            $nikUser = $user ? (string) $user->nik : '-';
            $jabatanUser = $user ? trim((string) ($user->jabatan ?: $user->peran)) : '-';
            $waktu = $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-';

            $sheet->setCellValue("A{$rowIndex}", $no);
            $sheet->setCellValue("B{$rowIndex}", $waktu);
            $sheet->setCellValue("C{$rowIndex}", $log->aksi);
            $sheet->setCellValue("D{$rowIndex}", $namaUser);

            // NIK disimpan eksplisit sebagai teks agar tidak menjadi scientific notation di Excel
            $sheet->setCellValueExplicit("E{$rowIndex}", $nikUser, DataType::TYPE_STRING);

            $sheet->setCellValue("F{$rowIndex}", $jabatanUser);
            $sheet->setCellValue("G{$rowIndex}", $log->deskripsi);
            $sheet->setCellValue("H{$rowIndex}", $log->ip_address ?? '-');

            // Zebra striping untuk kenyamanan membaca
            if ($rowIndex % 2 === 0) {
                $sheet->getStyle("A{$rowIndex}:H{$rowIndex}")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'],
                ]);
            }

            $rowIndex++;
            $no++;
        }

        $lastRow = max($rowIndex - 1, 2);

        // Styling Border Seluruh Data
        $sheet->getStyle("A2:H{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 10.5,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // Alignment Per Kolom
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D2:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Vertical Center untuk semua baris
        $sheet->getStyle("A2:H{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Lebar Kolom
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(50);
        $sheet->getColumnDimension('H')->setWidth(18);

        // Freeze baris header pertama
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
