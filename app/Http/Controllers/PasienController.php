<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $hasSearch = ! empty(trim((string) $search));
        $hasFilterStatus = $request->has('status') && in_array($status, ['semua', 'aktif', 'tidak_aktif']);

        $query = Pasien::query()->with('pasienPrb');

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

        $perPage = $request->integer('per_page', 15);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 15;
        }

        // Tampilkan data jika user mengetik pencarian ATAU memilih filter status
        if ($hasSearch || $hasFilterStatus) {
            $pasiens = $query->orderBy('nama')->paginate($perPage)->withQueryString();
        } else {
            // Tampilan awal sebelum user mencari atau memilih filter status
            $pasiens = Pasien::whereRaw('1 = 0')->paginate($perPage)->withQueryString();
        }

        return view('admin.pasien.index', compact('pasiens', 'search', 'status'));
    }

    public function show($id)
    {
        $pasien = Pasien::with([
            'pasienPrb',
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

    public function update(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => ['required', 'string', 'max:20', Rule::unique('pasien', 'nik')->ignore($pasien->id)],
            'no_rm' => 'nullable|string|max:50',
            'no_bpjs' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:25',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:500',
            'pekerjaan' => 'nullable|string|max:100',
            'pendidikan' => 'nullable|string|max:100',
            'diagnosa' => 'nullable|string|max:100',
        ], [
            'nama.required' => 'Nama pasien wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'NIK sudah digunakan oleh pasien lain.',
        ]);

        if (! empty($validated['no_hp'])) {
            $validated['no_hp'] = $this->normalizePhoneNumber($validated['no_hp']);
        }

        $diagnosa = $validated['diagnosa'] ?? null;
        unset($validated['diagnosa']);

        $pasien->update($validated);

        if ($pasien->pasienPrb && $request->has('diagnosa')) {
            $pasien->pasienPrb->update([
                'diagnosa' => ! empty($diagnosa) ? strtoupper(trim($diagnosa)) : null,
            ]);
        }

        LogAktivitas::catat('UPDATE_PASIEN', "Memperbarui profil pasien {$pasien->nama} (NIK: {$pasien->nik}).");

        return redirect()->back()->with('success', "Data profil pasien {$pasien->nama} berhasil diperbarui.");
    }

    private function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/[\s\-\(\)\+]+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone ?: null;
    }
}
