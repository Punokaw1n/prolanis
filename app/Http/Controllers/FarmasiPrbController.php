<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPengambilanObatPrb;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FarmasiPrbController extends Controller
{
    /**
     * Tampilkan tabel antrean resep farmasi khusus pasien PRB
     */
    public function index(Request $request)
    {
        // Cari tanggal terakhir ada data resep obat pasien PRB
        $latestRiwayat = RiwayatPengambilanObatPrb::has('obatPrb')
            ->whereNotNull('tanggal_diambil')
            ->latest('tanggal_diambil')
            ->first();

        $latestDateAvailable = $latestRiwayat?->tanggal_diambil?->format('Y-m-d')
            ?? RiwayatPengambilanObatPrb::has('obatPrb')->latest('created_at')->first()?->created_at?->format('Y-m-d');

        // Tanggal filter yang dipilih (default: tanggal yang diminta, atau hari ini)
        $tanggal = $request->get('tanggal');
        if (! $request->has('tanggal')) {
            $tanggal = Carbon::today()->format('Y-m-d');
        }

        $query = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien', 'obatPrb', 'pencatat', 'petugasFarmasi'])
            ->has('obatPrb');

        // Filter tanggal jika tidak memilih 'semua'
        if ($tanggal && $tanggal !== 'semua') {
            $query->where(function ($q) use ($tanggal) {
                $q->whereDate('tanggal_diambil', $tanggal)
                    ->orWhere(function ($sq) use ($tanggal) {
                        $sq->whereNull('tanggal_diambil')
                            ->whereDate('created_at', $tanggal);
                    });
            });
        }

        // Pencarian (Nama, No RM, NIK, Alamat, Pencatat/Dokter, Diagnosa, Nama Obat)
        if ($request->filled('q')) {
            $search = trim($request->get('q'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('pasienPrb.pasien', function ($qp) use ($search) {
                    $qp->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_rm', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%");
                })->orWhereHas('pencatat', function ($qn) use ($search) {
                    $qn->where('nama', 'like', "%{$search}%");
                })->orWhereHas('pasienPrb', function ($qprb) use ($search) {
                    $qprb->where('diagnosa', 'like', "%{$search}%")
                        ->orWhere('keterangan_diagnosa', 'like', "%{$search}%")
                        ->orWhere('kode_diagnosa_prb', 'like', "%{$search}%");
                })->orWhereHas('obatPrb', function ($qo) use ($search) {
                    $qo->where('nama_obat', 'like', "%{$search}%");
                });
            });
        }

        $riwayats = $query->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', 25))
            ->withQueryString();

        // Hitung total resep dokter untuk pasien PRB hari ini
        $totalResepHariIni = RiwayatPengambilanObatPrb::has('obatPrb')
            ->where(function ($q) {
                $today = Carbon::today()->format('Y-m-d');
                $q->whereDate('tanggal_diambil', $today)
                    ->orWhereDate('created_at', $today);
            })
            ->count();

        return view('admin.prb.farmasi.index', compact(
            'riwayats',
            'tanggal',
            'latestDateAvailable',
            'totalResepHariIni'
        ));
    }

    /**
     * Tampilkan detail resep riwayat obat (Halaman SIMPUS Kunjungan Obat)
     */
    public function show($id)
    {
        $riwayat = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien', 'obatPrb', 'pencatat', 'petugasFarmasi'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => $riwayat,
            ]);
        }

        $pasien = $riwayat->pasienPrb?->pasien;
        $dokter = $riwayat->pencatat;
        $obatPrbs = $riwayat->obatPrb;

        return view('admin.prb.farmasi.show', compact('riwayat', 'pasien', 'dokter', 'obatPrbs'));
    }

    /**
     * Proses pemberian obat oleh petugas farmasi (Beri Obat)
     */
    public function beriObat(Request $request, $id)
    {
        $riwayat = RiwayatPengambilanObatPrb::with('obatPrb')->findOrFail($id);

        $request->validate([
            'obat' => 'required|array',
            'obat.*.id' => 'required|exists:obat_prbs,id',
            'obat.*.status_pemberian' => 'required|in:sudah,kosong,belum',
            'obat.*.catatan_farmasi' => 'nullable|string|max:255',
        ]);

        $adaStokKosong = false;
        $adaBelum = false;
        $adaSudah = false;

        foreach ($request->obat as $obatItem) {
            $obat = $riwayat->obatPrb->firstWhere('id', $obatItem['id']);
            if ($obat) {
                $status = $obatItem['status_pemberian'];
                $obat->update([
                    'status_pemberian' => $status,
                    'catatan_farmasi' => $obatItem['catatan_farmasi'] ?? null,
                ]);

                if ($status === 'kosong') {
                    $adaStokKosong = true;
                } elseif ($status === 'sudah') {
                    $adaSudah = true;
                } else {
                    $adaBelum = true;
                }
            }
        }

        // Tentukan status keseluruhan resep di tabel luar
        if ($adaStokKosong) {
            $statusFarmasi = 'tidak_lengkap';
            $pesan = 'Pemberian obat telah disimpan dengan status: Sudah Diberikan Tidak Lengkap (ada stok kosong).';
        } elseif ($adaSudah && ! $adaBelum) {
            $statusFarmasi = 'sudah';
            $pesan = 'Seluruh obat resep telah berhasil diberikan lengkap kepada pasien.';
        } elseif ($adaSudah && $adaBelum) {
            $statusFarmasi = 'tidak_lengkap';
            $pesan = 'Sebagian obat telah diberikan kepada pasien.';
        } else {
            $statusFarmasi = 'belum';
            $pesan = 'Status obat disimpan sebagai belum diracik / belum diberikan.';
        }

        $riwayat->update([
            'status_farmasi' => $statusFarmasi,
            'petugas_farmasi_id' => auth()->id(),
            'waktu_diberikan' => now(),
        ]);

        return redirect()->route('admin.prb.farmasi.index', [
            'tanggal' => $riwayat->tanggal_diambil?->format('Y-m-d') ?? now()->format('Y-m-d'),
        ])->with('success', $pesan);
    }
}
