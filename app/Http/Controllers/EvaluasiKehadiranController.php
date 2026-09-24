<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EvaluasiKehadiranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $statusFilter = $request->get('status');

        $query = Pasien::with(['pemeriksaans' => function ($q) {
            $q->orderBy('tanggal_periksa', 'desc');
        }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Ambil semua data karena kita harus menghitung status secara dinamis di PHP (berdasarkan relasi terbaru)
        $pasiens = $query->get()->map(function ($pasien) {
            $kunjunganTerakhir = $pasien->pemeriksaans->first();
            
            $pasien->tanggal_kunjungan_terakhir = $kunjunganTerakhir ? Carbon::parse($kunjunganTerakhir->tanggal_periksa) : null;
            $pasien->status_kehadiran = 'Pasif'; // Default jika belum pernah periksa
            $pasien->hari_terlewat = null;
            $pasien->color_class = 'bg-red-100 text-red-800 border-red-200';

            if ($pasien->tanggal_kunjungan_terakhir) {
                $hariTerlewat = (int) $pasien->tanggal_kunjungan_terakhir->diffInDays(now());
                $pasien->hari_terlewat = $hariTerlewat;

                if ($hariTerlewat <= 40) {
                    $pasien->status_kehadiran = 'Rutin';
                    $pasien->color_class = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                } elseif ($hariTerlewat <= 90) {
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

        // Sort: Prioritaskan yang Pasif dan Jarang (berdasarkan hari_terlewat descending)
        $pasiens = $pasiens->sortByDesc('hari_terlewat');

        // Manual Pagination karena collection
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 15;
        $paginatedPasiens = new \Illuminate\Pagination\LengthAwarePaginator(
            $pasiens->forPage($page, $perPage),
            $pasiens->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.evaluasi.index', [
            'pasiens' => $paginatedPasiens,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
    }
}
