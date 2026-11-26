<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPrbExport
{
    /**
     * Download Excel untuk Laporan Pasien yang Belum Ambil Obat.
     */
    public static function downloadBelumAmbilObat(Collection $rows, string $periodeInfo = 'Semua Periode'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Belum Ambil Obat');

        $headers = [
            'No',
            'Nama Pasien',
            'NIK',
            'No. BPJS',
            'Diagnosa',
            'Jatuh Tempo',
            'Status',
            'No. HP',
            'Alamat',
        ];

        self::writeSheetHeader(
            $sheet,
            'LAPORAN PASIEN PRB BELUM AMBIL OBAT',
            'Periode Filter: '.$periodeInfo.' | Diekspor: '.now()->format('d/m/Y H:i'),
            $headers,
            'I'
        );

        $rowNum = 5;
        foreach ($rows as $idx => $r) {
            $pasien = $r->pasienPrb?->pasien;
            $jatuhTempo = $r->tanggal_jatuh_tempo ? Carbon::parse($r->tanggal_jatuh_tempo) : null;
            $isOverdue = $jatuhTempo && $jatuhTempo->lt(now()->startOfDay());
            $statusLabel = $isOverdue
                ? 'Terlambat '.$jatuhTempo->diffInDays(now()->startOfDay()).' Hari'
                : 'Belum Diambil';

            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", (string) ($pasien?->nama ?? '-'));
            $sheet->setCellValueExplicit("C{$rowNum}", (string) ($pasien?->nik ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$rowNum}", (string) ($pasien?->no_bpjs ?: '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("E{$rowNum}", $r->diagnosa_lengkap ?: ($r->pasienPrb?->diagnosa ?: '-'));
            $sheet->setCellValue("F{$rowNum}", $jatuhTempo ? $jatuhTempo->format('d/m/Y') : '-');
            $sheet->setCellValue("G{$rowNum}", $statusLabel);
            $sheet->setCellValueExplicit("H{$rowNum}", (string) ($pasien?->no_hp ?: '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("I{$rowNum}", (string) ($pasien?->alamat ?: '-'));

            $rowNum++;
        }

        self::applyBodyStylingAndWidths($sheet, $rowNum - 1, 'I', [
            'A' => 6,
            'B' => 28,
            'C' => 20,
            'D' => 18,
            'E' => 28,
            'F' => 15,
            'G' => 20,
            'H' => 16,
            'I' => 36,
        ]);

        $filename = 'Laporan_Pasien_Belum_Ambil_Obat_'.now()->format('Ymd_His').'.xlsx';

        return self::streamDownload($spreadsheet, $filename);
    }

    /**
     * Download Excel untuk Laporan Pasien PRB Aktif / Tidak Aktif.
     */
    public static function downloadStatusPasien(Collection $rows, string $status): StreamedResponse
    {
        $isAktif = $status === 'aktif';
        $judul = $isAktif ? 'LAPORAN DATA PASIEN PRB AKTIF' : 'LAPORAN DATA PASIEN PRB TIDAK AKTIF';
        $statusTeks = $isAktif ? 'Aktif' : 'Tidak Aktif';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($isAktif ? 'Pasien Aktif' : 'Pasien Tidak Aktif');

        $headers = [
            'No',
            'Nama Pasien',
            'NIK',
            'No. BPJS',
            'Diagnosa',
            'Tgl Terdaftar',
            'Status Keaktifan',
            'No. HP',
            'Alamat',
        ];

        self::writeSheetHeader(
            $sheet,
            $judul,
            'Total Data: '.$rows->count().' Pasien | Diekspor: '.now()->format('d/m/Y H:i'),
            $headers,
            'I'
        );

        $rowNum = 5;
        foreach ($rows as $idx => $prb) {
            $pasien = $prb->pasien;
            $tglDaftar = $prb->tanggal_terdaftar ? Carbon::parse($prb->tanggal_terdaftar)->format('d/m/Y') : '-';

            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", (string) ($pasien?->nama ?? '-'));
            $sheet->setCellValueExplicit("C{$rowNum}", (string) ($pasien?->nik ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$rowNum}", (string) ($pasien?->no_bpjs ?: '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("E{$rowNum}", (string) ($prb->diagnosa ?: '-'));
            $sheet->setCellValue("F{$rowNum}", $tglDaftar);
            $sheet->setCellValue("G{$rowNum}", $statusTeks);
            $sheet->setCellValueExplicit("H{$rowNum}", (string) ($pasien?->no_hp ?: '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("I{$rowNum}", (string) ($pasien?->alamat ?: '-'));

            $rowNum++;
        }

        self::applyBodyStylingAndWidths($sheet, $rowNum - 1, 'I', [
            'A' => 6,
            'B' => 28,
            'C' => 20,
            'D' => 18,
            'E' => 28,
            'F' => 15,
            'G' => 18,
            'H' => 16,
            'I' => 36,
        ]);

        $slug = $isAktif ? 'Aktif' : 'Tidak_Aktif';
        $filename = "Laporan_Pasien_PRB_{$slug}_".now()->format('Ymd_His').'.xlsx';

        return self::streamDownload($spreadsheet, $filename);
    }

    private static function writeSheetHeader($sheet, string $title, string $subtitle, array $headers, string $lastCol): void
    {
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '0F172A'],
            ],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $subtitle);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 10,
                'color' => ['rgb' => '475569'],
            ],
        ]);

        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
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
                    'color' => ['rgb' => '047857'],
                ],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(24);
    }

    private static function applyBodyStylingAndWidths($sheet, int $lastRow, string $lastCol, array $widths): void
    {
        if ($lastRow >= 5) {
            $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F5:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A5:{$lastCol}{$lastRow}")->applyFromArray([
                'font' => [
                    'name' => 'Calibri',
                    'size' => 10.5,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    private static function streamDownload(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
