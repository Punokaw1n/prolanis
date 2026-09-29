<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $petugas = $query->orderBy('nama', 'asc')->paginate(15);

        return view('admin.petugas.index', compact('petugas'));
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
            'nip' => 'required|numeric|unique:pengguna,nip',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'no_hp' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'peran' => 'required|string|in:admin,petugas',
        ], [
            'nik.unique' => 'NIK ini sudah terdaftar. Silakan gunakan NIK lain.',
            'nip.unique' => 'NIP ini sudah terdaftar. Silakan gunakan NIP lain.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.'
        ]);

        // Generate password from tanggal_lahir (DDMMYYYY)
        $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
        $password = $tanggal_lahir->format('dmY');

        User::create([
            'nama' => $request->nama,
            'nik' => $request->nik,
            'nip' => $request->nip,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'jabatan' => $request->jabatan,
            'peran' => $request->peran,
            'password' => Hash::make($password),
        ]);

        return redirect()->route('admin.petugas.index')->with('success', 'Data petugas berhasil ditambahkan. Password default adalah tanggal lahir (DDMMYYYY).');
    }

    public function edit(User $petuga)
    {
        $petugas = $petuga; // Because parameter in route is 'petuga' when resource is 'petugas'
        return view('admin.petugas.edit', compact('petugas'));
    }

    public function update(Request $request, User $petuga)
    {
        $petugas = $petuga;
        $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16|unique:pengguna,nik,' . $petugas->id,
            'nip' => 'required|numeric|unique:pengguna,nip,' . $petugas->id,
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'no_hp' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'peran' => 'required|string|in:admin,petugas',
        ], [
            'nik.unique' => 'NIK ini sudah terdaftar. Silakan gunakan NIK lain.',
            'nip.unique' => 'NIP ini sudah terdaftar. Silakan gunakan NIP lain.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.'
        ]);

        $data = $request->except(['_token', '_method']);
        
        // If tanggal_lahir changes, we DO NOT automatically change the password unless requested. 
        // But for simplicity in this clinic system, we can leave the old password.
        
        $petugas->update($data);

        return redirect()->route('admin.petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function toggleStatus(User $petuga)
    {
        $petuga->is_active = !$petuga->is_active;
        $petuga->save();

        $statusStr = $petuga->is_active ? 'diaktifkan' : 'dinonaktifkan';
        \App\Models\LogAktivitas::catat('UBAH_STATUS_PETUGAS', "Status petugas {$petuga->nama} {$statusStr}.");

        return redirect()->route('admin.petugas.index')->with('success', "Status petugas berhasil {$statusStr}.");
    }
}
