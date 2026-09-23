<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Pasien::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%")
                    ->orWhere('no_bpjs', 'like', "%{$search}%");
            });
            $pasiens = $query->orderBy('nama')->paginate(15)->withQueryString();
        } else {
            // Jika tidak ada pencarian, kembalikan data kosong
            $pasiens = Pasien::whereRaw('1 = 0')->paginate(15);
        }

        return view('admin.pasien.index', compact('pasiens', 'search'));
    }

    public function show($id)
    {
        $pasien = Pasien::with([
            'pemeriksaans' => function ($query) {
                $query->orderBy('tanggal_periksa', 'desc');
            },
            'pemeriksaans.pemberianObats', // Relasi dari Pemeriksaan ke PemberianObat
        ])->findOrFail($id);

        return view('admin.pasien.show', compact('pasien'));
    }
}
