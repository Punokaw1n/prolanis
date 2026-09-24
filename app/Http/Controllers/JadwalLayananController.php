<?php

namespace App\Http\Controllers;

use App\Models\JadwalLayanan;
use Illuminate\Http\Request;

class JadwalLayananController extends Controller
{
    public function index()
    {
        $jadwal = JadwalLayanan::with('pembuat')->orderBy('tanggal', 'desc')->paginate(10);

        return view('admin.jadwal.index', compact('jadwal'));
    }

    public function create()
    {
        return view('admin.jadwal.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $isActive = $request->has('is_active');

        if ($isActive) {
            JadwalLayanan::query()->update(['is_active' => false]);
        }

        JadwalLayanan::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'dibuat_oleh' => auth()->id(),
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal layanan berhasil ditambahkan.');
    }

    public function edit(JadwalLayanan $jadwal)
    {
        return view('admin.jadwal.edit', compact('jadwal'));
    }

    public function update(Request $request, JadwalLayanan $jadwal)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $isActive = $request->has('is_active');

        if ($isActive) {
            JadwalLayanan::where('id', '!=', $jadwal->id)->update(['is_active' => false]);
        }

        $jadwal->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal layanan berhasil diperbarui.');
    }

    public function destroy(JadwalLayanan $jadwal)
    {
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal layanan berhasil dihapus.');
    }
}
