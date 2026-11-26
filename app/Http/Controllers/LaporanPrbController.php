<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPrbExport;
use App\Models\PasienPrb;
use App\Models\RiwayatPengambilanObatPrb;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LaporanPrbController extends Controller
{
    /**
     * Halaman utama daftar laporan PRB (ala SIMPUS).
     */
    public function index(Request $request)
    {
        $daftarLaporan = [
            [
                'no' => 1,
                'nama' => 'Pasien yang Belum Ambil Obat',
                'keterangan' => 'Daftar pasien PRB aktif dengan jadwal pengambilan obat yang belum diambil (filter hari, bulan, tahun).',
                'route' => route('admin.prb.laporan.belum-ambil-obat'),
            ],
            [
                'no' => 2,
                'nama' => 'Pasien PRB Tidak Aktif',
                'keterangan' => 'Daftar pasien PRB yang berstatus Tidak Aktif pada data Pasien PRB.',
                'route' => route('admin.prb.laporan.tidak-aktif'),
            ],
            [
                'no' => 3,
                'nama' => 'Pasien PRB Aktif',
                'keterangan' => 'Daftar seluruh pasien PRB yang berstatus Aktif.',
                'route' => route('admin.prb.laporan.aktif'),
            ],
        ];

        return view('admin.prb.laporan.index', compact('daftarLaporan'));
    }

    /**
     * Laporan 1: Pasien yang belum ambil obat (filter hari, bulan, tahun).
     */
    public function belumAmbilObat(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $search = trim((string) $request->input('search', ''));
        $perPage = $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? $perPage : 15;

        $riwayatList = $this->buildBelumAmbilObatQuery($request)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $daftarTahun = range(now()->year + 1, max(2024, now()->year - 3));

        return view('admin.prb.laporan.belum_ambil_obat', compact(
            'riwayatList',
            'tanggal',
            'bulan',
            'tahun',
            'search',
            'perPage',
            'daftarTahun'
        ));
    }

    /**
     * Export Excel Laporan 1: Pasien yang belum ambil obat.
     */
    public function exportBelumAmbilObat(Request $request)
    {
        $rows = $this->buildBelumAmbilObatQuery($request)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get();

        $periodeParts = [];
        if ($request->filled('tanggal')) {
            $periodeParts[] = 'Tanggal '.Carbon::parse($request->input('tanggal'))->format('d/m/Y');
        } else {
            if ($request->filled('bulan')) {
                $periodeParts[] = 'Bulan '.$request->input('bulan');
            }
            if ($request->filled('tahun')) {
                $periodeParts[] = 'Tahun '.$request->input('tahun');
            }
        }
        $periodeInfo = count($periodeParts) > 0 ? implode(' ', $periodeParts) : 'Semua Periode';

        return LaporanPrbExport::downloadBelumAmbilObat($rows, $periodeInfo);
    }

    /**
     * Laporan 2: Pasien PRB Tidak Aktif.
     */
    public function tidakAktif(Request $request)
    {
        return $this->daftarStatusPasien($request, 'tidak_aktif');
    }

    /**
     * Export Excel Laporan 2: Pasien PRB Tidak Aktif.
     */
    public function exportTidakAktif(Request $request)
    {
        $rows = $this->buildStatusPasienQuery($request, 'tidak_aktif')
            ->orderByDesc('updated_at')
            ->get();

        return LaporanPrbExport::downloadStatusPasien($rows, 'tidak_aktif');
    }

    /**
     * Laporan 3: Pasien PRB Aktif.
     */
    public function aktif(Request $request)
    {
        return $this->daftarStatusPasien($request, 'aktif');
    }

    /**
     * Export Excel Laporan 3: Pasien PRB Aktif.
     */
    public function exportAktif(Request $request)
    {
        $rows = $this->buildStatusPasienQuery($request, 'aktif')
            ->orderByDesc('updated_at')
            ->get();

        return LaporanPrbExport::downloadStatusPasien($rows, 'aktif');
    }

    private function buildBelumAmbilObatQuery(Request $request): Builder
    {
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $search = trim((string) $request->input('search', ''));

        $query = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien'])
            ->belumDiambilAktif();

        if ($tanggal) {
            $query->whereDate('tanggal_jatuh_tempo', $tanggal);
        } else {
            if ($bulan) {
                $query->whereMonth('tanggal_jatuh_tempo', (int) $bulan);
            }
            if ($tahun) {
                $query->whereYear('tanggal_jatuh_tempo', (int) $tahun);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('diagnosa', 'like', "%{$search}%")
                    ->orWhere('kode_diagnosa', 'like', "%{$search}%")
                    ->orWhereHas('pasienPrb', function ($prbQ) use ($search) {
                        $prbQ->where('diagnosa', 'like', "%{$search}%")
                            ->orWhereHas('pasien', function ($pasienQ) use ($search) {
                                $pasienQ->where('nama', 'like', "%{$search}%")
                                    ->orWhere('nik', 'like', "%{$search}%")
                                    ->orWhere('no_bpjs', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query;
    }

    private function buildStatusPasienQuery(Request $request, string $status): Builder
    {
        $search = trim((string) $request->input('search', ''));

        $query = PasienPrb::with(['pasien'])
            ->where('status_keaktifan', $status);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('diagnosa', 'like', "%{$search}%")
                    ->orWhereHas('pasien', function ($pasienQ) use ($search) {
                        $pasienQ->where('nama', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                            ->orWhere('no_bpjs', 'like', "%{$search}%")
                            ->orWhere('alamat', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Helper bersama untuk laporan pasien PRB berdasarkan status keaktifan.
     */
    private function daftarStatusPasien(Request $request, string $status)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? $perPage : 15;

        $pasienList = $this->buildStatusPasienQuery($request, $status)
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.prb.laporan.status_pasien', [
            'pasienList' => $pasienList,
            'status' => $status,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }
}
