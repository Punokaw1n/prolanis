<?php

namespace App\Http\Controllers;

use App\Exports\LogAktivitasExport;
use App\Models\LogAktivitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 25);
        $query = LogAktivitas::with('user');

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                    ->orWhere('aksi', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('nama', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        $logs = $query->latest('created_at')->paginate($perPage > 0 ? $perPage : 25)->withQueryString();

        // Ringkasan data untuk header statistik
        $totalLogs = LogAktivitas::count();
        $logTertua = LogAktivitas::oldest('created_at')->value('created_at');
        $logTerbaru = LogAktivitas::latest('created_at')->value('created_at');
        $daftarAksi = LogAktivitas::select('aksi')->distinct()->orderBy('aksi')->pluck('aksi');

        // Hitung estimasi log lama untuk informasi pembersihan
        $countLebih30Hari = LogAktivitas::where('created_at', '<=', now()->subDays(30))->count();
        $countLebih60Hari = LogAktivitas::where('created_at', '<=', now()->subDays(60))->count();
        $countLebih90Hari = LogAktivitas::where('created_at', '<=', now()->subDays(90))->count();

        return view('admin.log.index', compact(
            'logs',
            'totalLogs',
            'logTertua',
            'logTerbaru',
            'daftarAksi',
            'countLebih30Hari',
            'countLebih60Hari',
            'countLebih90Hari'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = LogAktivitas::with('user');
        $periode = $request->input('periode', 'all');

        switch ($periode) {
            case 'sesuai_filter':
                $fileNamePeriode = 'Hasil_Filter';
                break;
            case '30_hari':
                $query->where('created_at', '<=', now()->subDays(30));
                $fileNamePeriode = 'Lebih_30_Hari';
                break;
            case '60_hari':
                $query->where('created_at', '<=', now()->subDays(60));
                $fileNamePeriode = 'Lebih_60_Hari';
                break;
            case '90_hari':
                $query->where('created_at', '<=', now()->subDays(90));
                $fileNamePeriode = 'Lebih_90_Hari';
                break;
            case '30_hari_terakhir':
                $query->where('created_at', '>=', now()->subDays(30));
                $fileNamePeriode = '30_Hari_Terakhir';
                break;
            case 'custom':
                if ($request->filled('dari_tanggal')) {
                    $query->whereDate('created_at', '>=', $request->dari_tanggal);
                }
                if ($request->filled('sampai_tanggal')) {
                    $query->whereDate('created_at', '<=', $request->sampai_tanggal);
                }
                $fileNamePeriode = 'Custom_'.($request->dari_tanggal ?: 'Awal').'_sd_'.($request->sampai_tanggal ?: 'Sekarang');
                break;
            default:
                $fileNamePeriode = 'Semua_Data';
                break;
        }

        $filterAksi = $request->input('filter_aksi', $request->input('aksi'));
        if (! empty($filterAksi)) {
            $query->where('aksi', $filterAksi);
        }

        $filterQ = $request->input('filter_q', $request->input('q'));
        if (! empty($filterQ)) {
            $search = trim($filterQ);
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                    ->orWhere('aksi', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('nama', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        $filterDari = $request->input('filter_dari_tanggal', $request->input('dari_tanggal'));
        if (! empty($filterDari)) {
            $query->whereDate('created_at', '>=', $filterDari);
        }

        $filterSampai = $request->input('filter_sampai_tanggal', $request->input('sampai_tanggal'));
        if (! empty($filterSampai)) {
            $query->whereDate('created_at', '<=', $filterSampai);
        }

        $query->latest('created_at');

        $fileName = 'Backup_Log_Aktivitas_'.$fileNamePeriode.'_'.date('Ymd_His').'.xlsx';

        return LogAktivitasExport::download($query, $fileName);
    }

    public function prune(Request $request)
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Hanya Administrator yang memiliki akses untuk membersihkan data log.');
        }

        $request->validate([
            'password' => 'required|string',
            'periode' => 'required|in:sesuai_filter,30_hari,60_hari,90_hari,sebelum_tanggal,semua',
            'sebelum_tanggal' => 'required_if:periode,sebelum_tanggal|nullable|date',
            'konfirmasi_semua' => 'required_if:periode,semua|nullable|string',
        ], [
            'password.required' => 'Password akun admin wajib diisi untuk verifikasi keamanan.',
            'periode.required' => 'Pilihan periode pembersihan log wajib dipilih.',
            'sebelum_tanggal.required_if' => 'Tanggal batas pembersihan wajib diisi.',
            'konfirmasi_semua.required_if' => 'Ketik KOSONGKAN untuk mengonfirmasi pembersihan seluruh data log.',
        ]);

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Password yang Anda masukkan salah. Pembersihan log dibatalkan demi keamanan.');
        }

        if ($request->periode === 'semua' && strtoupper(trim((string) $request->konfirmasi_semua)) !== 'KOSONGKAN') {
            return back()->with('error', 'Konfirmasi kata KOSONGKAN tidak sesuai. Pembersihan dibatalkan demi keamanan.');
        }

        $query = LogAktivitas::query();
        $keterangan = '';

        switch ($request->periode) {
            case 'sesuai_filter':
                $rincian = [];
                $filterAksi = $request->input('filter_aksi', $request->input('aksi'));
                if (! empty($filterAksi)) {
                    $query->where('aksi', $filterAksi);
                    $rincian[] = "Aksi: {$filterAksi}";
                }

                $filterQ = $request->input('filter_q', $request->input('q'));
                if (! empty($filterQ)) {
                    $search = trim($filterQ);
                    $query->where(function ($q) use ($search) {
                        $q->where('deskripsi', 'like', "%{$search}%")
                            ->orWhere('aksi', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($uq) use ($search) {
                                $uq->where('nama', 'like', "%{$search}%")
                                    ->orWhere('nik', 'like', "%{$search}%");
                            });
                    });
                    $rincian[] = "Kata Kunci: '{$search}'";
                }

                $filterDari = $request->input('filter_dari_tanggal', $request->input('dari_tanggal'));
                if (! empty($filterDari)) {
                    $query->whereDate('created_at', '>=', $filterDari);
                    $rincian[] = "Dari: {$filterDari}";
                }

                $filterSampai = $request->input('filter_sampai_tanggal', $request->input('sampai_tanggal'));
                if (! empty($filterSampai)) {
                    $query->whereDate('created_at', '<=', $filterSampai);
                    $rincian[] = "Sampai: {$filterSampai}";
                }

                if (count($rincian) === 0) {
                    return back()->with('error', 'Tidak ada kriteria filter yang ditemukan.');
                }

                $keterangan = 'sesuai filter aktif ('.implode(', ', $rincian).')';
                break;
            case '30_hari':
                $batas = now()->subDays(30);
                $query->where('created_at', '<=', $batas);
                $keterangan = 'lebih dari 30 hari yang lalu (sebelum '.$batas->format('d/m/Y').')';
                break;
            case '60_hari':
                $batas = now()->subDays(60);
                $query->where('created_at', '<=', $batas);
                $keterangan = 'lebih dari 60 hari yang lalu (sebelum '.$batas->format('d/m/Y').')';
                break;
            case '90_hari':
                $batas = now()->subDays(90);
                $query->where('created_at', '<=', $batas);
                $keterangan = 'lebih dari 90 hari yang lalu (sebelum '.$batas->format('d/m/Y').')';
                break;
            case 'sebelum_tanggal':
                $batas = Carbon::parse($request->sebelum_tanggal)->startOfDay();
                $query->where('created_at', '<', $batas);
                $keterangan = 'sebelum tanggal '.$batas->format('d/m/Y');
                break;
            case 'semua':
                $keterangan = 'seluruh data log aktivitas';
                break;
        }

        $count = $query->count();

        if ($count === 0) {
            return back()->with('info', "Tidak ada catatan log aktivitas yang cocok dengan kriteria ({$keterangan}). Database sudah bersih.");
        }

        $query->delete();

        // Catat aksi pembersihan ke dalam log baru
        LogAktivitas::catat('BERSIHKAN_LOG', "Admin membersihkan {$count} catatan log aktivitas ({$keterangan}).");

        return back()->with('success', "Pembersihan berhasil. Sebanyak {$count} catatan log aktivitas ({$keterangan}) telah dibersihkan dari database.");
    }
}
