<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplatePrbExport
{
    /**
     * Download template Excel untuk import data Register Pasien PRB.
     * Format kolom 100% identik dan selaras dengan dataset Register Pasien Rujuk Balik (PRB).xlsx
     */
    public static function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PRB 2026');

        // Row 1 - Main Headers
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NIK');
        $sheet->setCellValue('C1', 'No. Kartu BPJS');
        $sheet->setCellValue('D1', 'Nama Pasien');
        $sheet->setCellValue('E1', 'Alamat');
        $sheet->setCellValue('F1', 'Tgl Pertama PRB');
        $sheet->setCellValue('G1', 'Nama Obat');
        $sheet->setCellValue('H1', 'Tgl Lahir');
        $sheet->setCellValue('I1', 'Jenis Kelamin (L/P)');
        $sheet->setCellValue('J1', 'No. WA/ No. Telp');
        $sheet->setCellValue('K1', 'Diagnosa');
        $sheet->setCellValue('L1', 'Tgl Tahun lalu Terakhir Ambil PRB');

        // Month Groups in Row 1 (merged 2 columns per month)
        $months = [
            ['M', 'N', 'Januari (1)'],
            ['O', 'P', 'Februari (2)'],
            ['Q', 'R', 'Maret (3)'],
            ['S', 'T', 'April (4)'],
            ['U', 'V', 'Mei (5)'],
            ['W', 'X', 'Juni (6)'],
            ['Y', 'Z', 'Juli (7)'],
            ['AA', 'AB', 'Agustus (8)'],
            ['AC', 'AD', 'September (9)'],
            ['AE', 'AF', 'Oktober (10)'],
            ['AG', 'AH', 'November (11)'],
            ['AI', 'AJ', 'Desember (12)'],
        ];

        foreach ($months as [$startCol, $endCol, $label]) {
            $sheet->setCellValue("{$startCol}1", $label);
            $sheet->mergeCells("{$startCol}1:{$endCol}1");

            $sheet->setCellValue("{$startCol}2", 'Tgl. Obat di Ambil');
            $sheet->setCellValue("{$endCol}2", 'Status Pengambilan Obat');
        }

        // Merge headers A1 to L1 vertically to Row 2
        $verticallyMerged = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        foreach ($verticallyMerged as $col) {
            $sheet->mergeCells("{$col}1:{$col}2");
        }

        // Row Heights
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Styling Header Range A1:L2
        $headerPrimaryStyle = [
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'], // Blue 700
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '1E40AF'],
                ],
            ],
        ];

        $sheet->getStyle('A1:L2')->applyFromArray($headerPrimaryStyle);

        // Styling Month Headers (M1:AJ1)
        $monthHeaderStyle = [
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0284C7'], // Sky 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0369A1'],
                ],
            ],
        ];
        $sheet->getStyle('M1:AJ1')->applyFromArray($monthHeaderStyle);

        // Styling Sub-headers M2:AJ2
        $subHeaderStyle = [
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 9,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0EA5E9'], // Sky 500
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0284C7'],
                ],
            ],
        ];
        $sheet->getStyle('M2:AJ2')->applyFromArray($subHeaderStyle);

        // Set Text Format for Identification Columns
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('@');

        // Sample Rows
        $samples = [
            [
                'no' => 1,
                'nik' => '3578264811670001',
                'bpjs' => '0001850076781',
                'nama' => 'Budi Santoso',
                'alamat' => 'Jl. Kalijudan Taruna No. 10',
                'tgl_pertama' => '2024-05-10',
                'obat' => 'Amlodipin 10 mg 1x1 (30), Candesartan 16 mg 1x1 (30)',
                'tgl_lahir' => '1965-05-12',
                'jk' => 'L',
                'telp' => '081234567890',
                'diagnosa' => 'I10',
                'tgl_tahun_lalu' => '2025-12-15',
                'm_jan' => '2026-01-15', 's_jan' => 'SUDAH AMBIL',
                'm_feb' => '2026-02-15', 's_feb' => 'SUDAH AMBIL',
                'm_mar' => '2026-03-15', 's_mar' => 'SUDAH AMBIL',
                'm_apr' => '2026-04-15', 's_apr' => 'SUDAH AMBIL',
                'm_mei' => '2026-05-15', 's_mei' => 'SUDAH AMBIL',
                'm_jun' => '2026-06-15', 's_jun' => 'SUDAH AMBIL',
                'm_jul' => '2026-07-15', 's_jul' => 'SUDAH AMBIL',
                'm_agu' => '2026-08-15', 's_agu' => 'SUDAH AMBIL',
                'm_sep' => '2026-09-15', 's_sep' => 'SUDAH AMBIL',
                'm_okt' => '2026-10-15', 's_okt' => 'SUDAH AMBIL',
                'm_nov' => '', 's_nov' => 'BELUM DIAMBIL',
                'm_des' => '', 's_des' => 'BELUM DIAMBIL',
            ],
            [
                'no' => 2,
                'nik' => '3578264811670002',
                'bpjs' => '0001850076782',
                'nama' => 'Siti Aminah',
                'alamat' => 'Jl. Ploso Timur No. 45',
                'tgl_pertama' => '2024-08-20',
                'obat' => 'Metformin 500 mg 3x1 (90), Glimepirid 2 mg 1x1 (30)',
                'tgl_lahir' => '1970-10-22',
                'jk' => 'P',
                'telp' => '081298765432',
                'diagnosa' => 'E11.9',
                'tgl_tahun_lalu' => '2025-12-20',
                'm_jan' => '2026-01-20', 's_jan' => 'SUDAH AMBIL',
                'm_feb' => '2026-02-20', 's_feb' => 'SUDAH AMBIL',
                'm_mar' => '2026-03-20', 's_mar' => 'SUDAH AMBIL',
                'm_apr' => '2026-04-20', 's_apr' => 'SUDAH AMBIL',
                'm_mei' => '2026-05-20', 's_mei' => 'SUDAH AMBIL',
                'm_jun' => '2026-06-20', 's_jun' => 'SUDAH AMBIL',
                'm_jul' => '2026-07-20', 's_jul' => 'SUDAH AMBIL',
                'm_agu' => '2026-08-20', 's_agu' => 'SUDAH AMBIL',
                'm_sep' => '2026-09-20', 's_sep' => 'SUDAH AMBIL',
                'm_okt' => '2026-10-20', 's_okt' => 'SUDAH AMBIL',
                'm_nov' => '', 's_nov' => 'BELUM DIAMBIL',
                'm_des' => '', 's_des' => 'BELUM DIAMBIL',
            ],
        ];

        $rowIdx = 3;
        foreach ($samples as $sample) {
            $sheet->setCellValue("A{$rowIdx}", $sample['no']);
            $sheet->setCellValueExplicit("B{$rowIdx}", $sample['nik'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$rowIdx}", $sample['bpjs'], DataType::TYPE_STRING);
            $sheet->setCellValue("D{$rowIdx}", $sample['nama']);
            $sheet->setCellValue("E{$rowIdx}", $sample['alamat']);
            $sheet->setCellValue("F{$rowIdx}", $sample['tgl_pertama']);
            $sheet->setCellValue("G{$rowIdx}", $sample['obat']);
            $sheet->setCellValue("H{$rowIdx}", $sample['tgl_lahir']);
            $sheet->setCellValue("I{$rowIdx}", $sample['jk']);
            $sheet->setCellValueExplicit("J{$rowIdx}", $sample['telp'], DataType::TYPE_STRING);
            $sheet->setCellValue("K{$rowIdx}", $sample['diagnosa']);
            $sheet->setCellValue("L{$rowIdx}", $sample['tgl_tahun_lalu']);

            $sheet->setCellValue("M{$rowIdx}", $sample['m_jan']);
            $sheet->setCellValue("N{$rowIdx}", $sample['s_jan']);
            $sheet->setCellValue("O{$rowIdx}", $sample['m_feb']);
            $sheet->setCellValue("P{$rowIdx}", $sample['s_feb']);
            $sheet->setCellValue("Q{$rowIdx}", $sample['m_mar']);
            $sheet->setCellValue("R{$rowIdx}", $sample['s_mar']);
            $sheet->setCellValue("S{$rowIdx}", $sample['m_apr']);
            $sheet->setCellValue("T{$rowIdx}", $sample['s_apr']);
            $sheet->setCellValue("U{$rowIdx}", $sample['m_mei']);
            $sheet->setCellValue("V{$rowIdx}", $sample['s_mei']);
            $sheet->setCellValue("W{$rowIdx}", $sample['m_jun']);
            $sheet->setCellValue("X{$rowIdx}", $sample['s_jun']);
            $sheet->setCellValue("Y{$rowIdx}", $sample['m_jul']);
            $sheet->setCellValue("Z{$rowIdx}", $sample['s_jul']);
            $sheet->setCellValue("AA{$rowIdx}", $sample['m_agu']);
            $sheet->setCellValue("AB{$rowIdx}", $sample['s_agu']);
            $sheet->setCellValue("AC{$rowIdx}", $sample['m_sep']);
            $sheet->setCellValue("AD{$rowIdx}", $sample['s_sep']);
            $sheet->setCellValue("AE{$rowIdx}", $sample['m_okt']);
            $sheet->setCellValue("AF{$rowIdx}", $sample['s_okt']);
            $sheet->setCellValue("AG{$rowIdx}", $sample['m_nov']);
            $sheet->setCellValue("AH{$rowIdx}", $sample['s_nov']);
            $sheet->setCellValue("AI{$rowIdx}", $sample['m_des']);
            $sheet->setCellValue("AJ{$rowIdx}", $sample['s_des']);

            $sheet->getRowDimension($rowIdx)->setRowHeight(20);
            $rowIdx++;
        }

        $lastRow = $rowIdx - 1;

        // Styling data cells
        $sheet->getStyle("A3:AJ{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 10,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        $sheet->getStyle("A3:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B3:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F3:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H3:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("K3:L{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("M3:AJ{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column widths
        $columnWidths = [
            'A' => 6,
            'B' => 20, // NIK
            'C' => 18, // No BPJS
            'D' => 24, // Nama
            'E' => 30, // Alamat
            'F' => 16, // Tgl Pertama PRB
            'G' => 35, // Nama Obat
            'H' => 14, // Tgl Lahir
            'I' => 10, // Jenis Kelamin
            'J' => 16, // No Telp
            'K' => 12, // Diagnosa
            'L' => 18, // Tgl Thn Lalu
            'M' => 14, 'N' => 16,
            'O' => 14, 'P' => 16,
            'Q' => 14, 'R' => 16,
            'S' => 14, 'T' => 16,
            'U' => 14, 'V' => 16,
            'W' => 14, 'X' => 16,
            'Y' => 14, 'Z' => 16,
            'AA' => 14, 'AB' => 16,
            'AC' => 14, 'AD' => 16,
            'AE' => 14, 'AF' => 16,
            'AG' => 14, 'AH' => 16,
            'AI' => 14, 'AJ' => 16,
        ];

        foreach ($columnWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $fileName = 'Register Pasien Rujuk Balik (PRB).xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
