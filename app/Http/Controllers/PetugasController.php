<?php

namespace App\Http\Controllers;

use App\Imports\PegawaiImport;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('pegawai');

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $petugas = $query->orderBy('nama', 'asc')->paginate($request->integer('per_page', 15))->withQueryString();

        return view('admin.petugas.index', compact('petugas'));
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel data pegawai wajib dipilih.',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran file tidak boleh melebihi 10MB.',
        ]);

        $import = new PegawaiImport;

        try {
            Excel::import($import, $request->file('file'));

            $summary = $import->summary;

            LogAktivitas::catat('IMPORT_PEGAWAI', "Mengimpor data pegawai: {$summary['petugas_baru']} baru, {$summary['petugas_existing']} diperbarui.");

            // Tentukan status dan pesan berdasarkan hasil import
            if ($summary['petugas_baru'] > 0) {
                $statusType = 'success';
                $message = "Proses import data pegawai selesai. {$summary['petugas_baru']} akun pegawai baru berhasil dibuat, {$summary['petugas_existing']} akun diperbarui.";
                session()->flash('success', $message);
            } elseif ($summary['petugas_existing'] > 0) {
                $statusType = 'info';
                $message = "Tidak ada akun baru yang ditambahkan. Seluruh {$summary['petugas_existing']} data pegawai dalam file sudah terdaftar di sistem (profil disinkronkan).";
                session()->flash('info', $message);
            } else {
                $statusType = 'warning';
                $message = 'Tidak ada data pegawai yang dapat diproses dari file Excel.';
                session()->flash('warning', $message);
            }

            session()->flash('import_summary', $summary);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'type' => $statusType,
                    'message' => $message,
                    'summary' => $summary,
                ]);
            }

            return redirect()->route('admin.petugas.index')
                ->with('import_summary', $summary)
                ->with($statusType, $message);
        } catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal memproses file Excel: '.$e->getMessage(),
                    'errors' => ['file' => [$e->getMessage()]],
                ], 422);
            }

            return back()->withErrors(['file' => 'Gagal memproses file Excel: '.$e->getMessage()])->withInput();
        }
    }

    public function create()
    {
        return view('admin.petugas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16|unique:pengguna,nik',
            'nip' => 'nullable|numeric|unique:pengguna,nip',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'pendidikan' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:pengguna,email',
            'no_bpjs' => 'nullable|string|max:50',
            'no_hp' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'peran' => 'required|string|in:admin,petugas',
            'alamat' => 'nullable|string',
        ], [
            'nik.unique' => 'NIK ini sudah terdaftar. Silakan gunakan NIK lain.',
            'nip.unique' => 'NIP ini sudah terdaftar. Silakan gunakan NIP lain.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'jabatan.required' => 'Jabatan profesi wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
        ]);

        // Generate password from tanggal_lahir (DDMMYYYY)
        $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
        $password = $tanggal_lahir->format('dmY');

        $user = User::create([
            'nama' => $request->nama,
            'nik' => $request->nik,
            'nip' => $request->nip ?: null,
            'email' => $request->email ?: null,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat ?: null,
            'no_hp' => $request->no_hp,
            'jabatan' => $request->jabatan,
            'peran' => $request->peran,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Sinkronkan ke tabel legacy pegawai
        Pegawai::updateOrCreate(
            ['nik' => $request->nik],
            [
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat ?: null,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin ?: null,
                'pendidikan' => $request->pendidikan ?: null,
                'no_hp' => $request->no_hp ?: null,
                'no_bpjs' => $request->no_bpjs ?: null,
            ]
        );

        LogAktivitas::catat('TAMBAH_PETUGAS', "Menambahkan akun petugas manual: {$user->nama} ({$user->jabatan}).");

        return redirect()->route('admin.petugas.index')->with('success', 'Data petugas berhasil ditambahkan. Password default adalah tanggal lahir (DDMMYYYY).');
    }

    public function edit(User $petuga)
    {
        $petugas = $petuga->load('pegawai');

        return view('admin.petugas.edit', compact('petugas'));
    }

    public function update(Request $request, User $petuga)
    {
        $petugas = $petuga;
        $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16|unique:pengguna,nik,'.$petugas->id,
            'nip' => 'nullable|numeric|unique:pengguna,nip,'.$petugas->id,
            'email' => 'nullable|email|max:255|unique:pengguna,email,'.$petugas->id,
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'pendidikan' => 'nullable|string|max:255',
            'no_bpjs' => 'nullable|string|max:50',
            'no_hp' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'peran' => 'required|string|in:admin,petugas',
            'alamat' => 'nullable|string',
        ], [
            'nik.unique' => 'NIK ini sudah terdaftar. Silakan gunakan NIK lain.',
            'nip.unique' => 'NIP ini sudah terdaftar. Silakan gunakan NIP lain.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'jabatan.required' => 'Jabatan profesi wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
        ]);

        $petugas->update([
            'nama' => $request->nama,
            'nik' => $request->nik,
            'nip' => $request->nip ?: null,
            'email' => $request->email ?: null,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat ?: null,
            'no_hp' => $request->no_hp,
            'jabatan' => $request->jabatan,
            'peran' => $request->peran,
        ]);

        Pegawai::updateOrCreate(
            ['nik' => $request->nik],
            [
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat ?: null,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin ?: null,
                'pendidikan' => $request->pendidikan ?: null,
                'no_hp' => $request->no_hp ?: null,
                'no_bpjs' => $request->no_bpjs ?: null,
            ]
        );

        LogAktivitas::catat('UPDATE_PETUGAS', "Memperbarui data petugas: {$petugas->nama}.");

        return redirect()->route('admin.petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function toggleStatus(User $petuga)
    {
        $petuga->is_active = ! $petuga->is_active;
        $petuga->save();

        $statusStr = $petuga->is_active ? 'diaktifkan' : 'dinonaktifkan';
        LogAktivitas::catat('UBAH_STATUS_PETUGAS', "Status petugas {$petuga->nama} {$statusStr}.");

        return redirect()->route('admin.petugas.index')->with('success', "Status petugas berhasil {$statusStr}.");
    }
}
