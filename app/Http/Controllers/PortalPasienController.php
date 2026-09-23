<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PortalPasienController extends Controller
{
    public function dashboard()
    {
        $pasien = Auth::guard('pasien')->user();

        // Load riwayat pemeriksaan
        $pasien->load([
            'pemeriksaans' => function ($q) {
                $q->orderBy('tanggal_periksa', 'desc');
            },
            'pemeriksaans.pemberianObats',
            'pemeriksaans.nakes',
        ]);

        // Siapkan data untuk grafik (diurutkan kronologis: terlama ke terbaru)
        $grafikData = $pasien->pemeriksaans->sortBy('tanggal_periksa')->values();

        $labels = $grafikData->map(function ($p) {
            return Carbon::parse($p->tanggal_periksa)->format('d M Y');
        });

        $beratBadan = $grafikData->pluck('berat_badan')->map(fn ($v) => floatval($v ?? 0));
        $tensiSistol = $grafikData->pluck('tensi_sistol')->map(fn ($v) => intval($v ?? 0));
        $tensiDiastol = $grafikData->pluck('tensi_diastol')->map(fn ($v) => intval($v ?? 0));

        $kunjunganTerakhir = $pasien->pemeriksaans->first();

        return view('pasien.dashboard', compact(
            'pasien',
            'kunjunganTerakhir',
            'labels',
            'beratBadan',
            'tensiSistol',
            'tensiDiastol'
        ));
    }
}
