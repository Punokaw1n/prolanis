<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvaluasiKehadiranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        $tanggal = $request->get('tanggal');
        $konfirmasiFilter = $request->get('konfirmasi');
        $page = $request->get('page');

        // Jika tidak ada parameter pencarian sama sekali, anggap ini loading awal
        $isInitialLoad = ! $search && ! $statusFilter && ! $tanggal && ! $konfirmasiFilter && ! $page && ! $request->ajax();

        $perPage = $request->integer('per_page', 15);
        $perPage = ($perPage > 0) ? $perPage : 15;

        if ($isInitialLoad) {
            $paginatedPasiens = new LengthAwarePaginator(
                collect([]), 0, $perPage, 1,
                ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
            );

            return view('admin.evaluasi.index', [
                'pasiens' => $paginatedPasiens,
                'search' => $search,
                'statusFilter' => $statusFilter,
                'tanggal' => $tanggal,
                'konfirmasiFilter' => $konfirmasiFilter,
                'isInitialLoad' => true,
            ]);
        }

        $jadwalLayananQuery = function ($q) use ($tanggal) {
            if ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            } else {
                $q->orderBy('tanggal', 'desc');
            }
        };

        $query = Pasien::with(['pemeriksaans' => function ($q) {
            $q->orderBy('tanggal_periksa', 'desc');
        }, 'jadwalLayanan' => $jadwalLayananQuery]);

        if ($tanggal) {
            $query->whereHas('jadwalLayanan', $jadwalLayananQuery);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%")
                    ->orWhere('no_bpjs', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $targetDate = $tanggal ? Carbon::parse($tanggal)->startOfMonth() : now()->startOfMonth();

        // Ambil semua data karena kita harus menghitung status secara dinamis di PHP (berdasarkan relasi terbaru)
        $pasiens = $query->get()->map(function ($pasien) use ($targetDate) {
            $kunjunganTerakhir = $pasien->pemeriksaans->first();
            $jadwalPasien = $pasien->jadwalLayanan->first();

            $pasien->konfirmasi = $jadwalPasien ? $jadwalPasien->pivot->konfirmasi_kehadiran : 'Menunggu';
            $pasien->jadwal_terkait_id = $jadwalPasien ? $jadwalPasien->id : null;

            $pasien->tanggal_kunjungan_terakhir = $kunjunganTerakhir ? Carbon::parse($kunjunganTerakhir->tanggal_periksa) : null;
            $pasien->status_kehadiran = 'Pasif'; // Default jika belum pernah periksa
            $pasien->bulan_terlewat = null;
            $pasien->color_class = 'bg-red-100 text-red-800 border-red-200';

            if ($pasien->tanggal_kunjungan_terakhir) {
                // Menghitung selisih bulan kalender (contoh: Sept ke Agustus = 1, Sept ke Juli = 2)
                $bulanTerlewat = (int) $pasien->tanggal_kunjungan_terakhir->copy()->startOfMonth()->diffInMonths($targetDate);
                $pasien->bulan_terlewat = $bulanTerlewat;

                if ($bulanTerlewat <= 1) {
                    $pasien->status_kehadiran = 'Rutin';
                    $pasien->color_class = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                } elseif ($bulanTerlewat <= 3) {
                    $pasien->status_kehadiran = 'Jarang';
                    $pasien->color_class = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                } else {
                    $pasien->status_kehadiran = 'Pasif';
                    $pasien->color_class = 'bg-red-100 text-red-800 border-red-200';
                }
            }

            return $pasien;
        });

        // Filter by status if requested
        if ($statusFilter) {
            $pasiens = $pasiens->filter(function ($pasien) use ($statusFilter) {
                return strtolower($pasien->status_kehadiran) === strtolower($statusFilter);
            });
        }

        if ($konfirmasiFilter) {
            $pasiens = $pasiens->filter(function ($pasien) use ($konfirmasiFilter) {
                return strtolower($pasien->konfirmasi) === strtolower($konfirmasiFilter);
            });
        }

        if ($request->get('export') == 'excel') {
            return $this->exportExcel($pasiens, $tanggal);
        }

        // Sort: Prioritaskan yang Pasif dan Jarang (berdasarkan hari_terlewat descending)
        $pasiens = $pasiens->sortByDesc('hari_terlewat');

        // Manual Pagination karena collection
        $page = Paginator::resolveCurrentPage() ?: 1;
        $paginatedPasiens = new LengthAwarePaginator(
            $pasiens->forPage($page, $perPage),
            $pasiens->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $isInitialLoad = false;

        return view('admin.evaluasi.index', [
            'pasiens' => $paginatedPasiens,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'tanggal' => $tanggal,
            'konfirmasiFilter' => $konfirmasiFilter,
            'isInitialLoad' => $isInitialLoad,
        ]);
    }

    public function updateKonfirmasi(Request $request, $pasienId)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_layanan,id',
            'status' => 'required|string|in:Hadir,Tidak Hadir,Menunggu',
        ]);

        $pasien = Pasien::findOrFail($pasienId);
        $pasien->jadwalLayanan()->updateExistingPivot($request->jadwal_id, [
            'konfirmasi_kehadiran' => $request->status,
        ]);

        return response()->json(['success' => true]);
    }

    private function exportExcel($pasiens, $tanggal)
    {
        $fileName = 'Absensi_Prolanis_'.($tanggal ? $tanggal : 'Semua').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Set Header
        $columns = ['No', 'Nama', 'NIK', 'Alamat', 'Tanggal', 'Tanda Tangan'];
        $colIndex = 'A';
        foreach ($columns as $header) {
            $sheet->setCellValue($colIndex.'1', $header);
            $sheet->getStyle($colIndex.'1')->getFont()->setBold(true);
            $colIndex++;
        }

        // Set Data
        $row = 2;
        $i = 1;
        foreach ($pasiens as $pasien) {
            $jadwal = $pasien->jadwalLayanan->first();
            $tglJadwal = $jadwal ? Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') : '-';

            $sheet->setCellValue('A'.$row, $i++);
            $sheet->setCellValue('B'.$row, $pasien->nama);
            // Prefix with ' to ensure NIK is treated as text, avoiding scientific notation
            $sheet->setCellValue('C'.$row, "'".$pasien->nik);
            $sheet->setCellValue('D'.$row, $pasien->alamat ?? '-');
            $sheet->setCellValue('E'.$row, $tglJadwal);

            // Tanda Tangan is empty, we just need space
            // Set row height for signing
            $sheet->getRowDimension($row)->setRowHeight(35);

            $row++;
        }

        // Auto-size columns A to E
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Manually set a wide column for Tanda Tangan (Column F)
        $sheet->getColumnDimension('F')->setWidth(25);

        // Stream the file back
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
