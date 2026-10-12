<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pasien;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $hasSearch = ! empty(trim((string) $search));
        $hasFilterStatus = $request->has('status') && in_array($status, ['semua', 'aktif', 'tidak_aktif']);

        $query = Pasien::query();

        if ($hasFilterStatus && in_array($status, ['aktif', 'tidak_aktif'])) {
            $query->where('status_keaktifan', $status);
        }

        if ($hasSearch) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_rm', 'like', "%{$search}%")
                    ->orWhere('no_bpjs', 'like', "%{$search}%");
            });
        }

        // Tampilkan data jika user mengetik pencarian ATAU memilih filter status
        if ($hasSearch || $hasFilterStatus) {
            $pasiens = $query->orderBy('nama')->paginate(15)->withQueryString();
        } else {
            // Tampilan awal sebelum user mencari atau memilih filter status
            $pasiens = Pasien::whereRaw('1 = 0')->paginate(15);
        }

        return view('admin.pasien.index', compact('pasiens', 'search', 'status'));
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

    public function toggleStatus(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);

        if ($pasien->status_keaktifan === 'aktif') {
            $request->validate([
                'alasan' => 'nullable|string|max:255',
            ]);

            $alasan = $request->input('alasan') ?: 'Dinonaktifkan oleh petugas';

            $pasien->update([
                'status_keaktifan' => 'tidak_aktif',
                'alasan_tidak_aktif' => $alasan,
                'tanggal_tidak_aktif' => now()->toDateString(),
            ]);

            // Sinkronkan status PRB jika pasien juga terdaftar sebagai pasien PRB
            if ($pasien->pasienPrb) {
                $pasien->pasienPrb->update(['status_keaktifan' => 'tidak_aktif']);
            }

            LogAktivitas::catat('UBAH_STATUS_PASIEN', "Menonaktifkan pasien {$pasien->nama} (NIK: {$pasien->nik}). Alasan: {$alasan}");

            return redirect()->back()->with('success', "Pasien {$pasien->nama} berhasil dinonaktifkan.");
        } else {
            $pasien->update([
                'status_keaktifan' => 'aktif',
                'alasan_tidak_aktif' => null,
                'tanggal_tidak_aktif' => null,
            ]);

            LogAktivitas::catat('UBAH_STATUS_PASIEN', "Mengaktifkan kembali pasien {$pasien->nama} (NIK: {$pasien->nik}).");

            return redirect()->back()->with('success', "Pasien {$pasien->nama} berhasil diaktifkan kembali.");
        }
    }
}
