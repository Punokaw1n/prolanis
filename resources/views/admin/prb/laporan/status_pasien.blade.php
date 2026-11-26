@extends('layouts.app')

@section('content')
@php
    $isAktif = $status === 'aktif';
    $judul = $isAktif ? 'Laporan Pasien PRB Aktif' : 'Laporan Pasien PRB Tidak Aktif';
    $deskripsi = $isAktif
        ? 'Daftar seluruh pasien Program Rujuk Balik (PRB) yang berstatus Aktif.'
        : 'Daftar seluruh pasien Program Rujuk Balik (PRB) yang berstatus Tidak Aktif.';
    $routeName = $isAktif ? 'admin.prb.laporan.aktif' : 'admin.prb.laporan.tidak-aktif';
@endphp

<div class="space-y-5">
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.prb.laporan.index') }}" class="hover:text-blue-600 transition-colors">Laporan PRB</a>
                <span>/</span>
                <span class="text-gray-800 font-semibold">{{ $isAktif ? 'Pasien Aktif' : 'Pasien Tidak Aktif' }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">{{ $judul }}</h1>
            <p class="mt-1 text-xs sm:text-sm text-gray-600">{{ $deskripsi }}</p>
        </div>

        <div class="flex items-center gap-2 no-print">
            <a href="{{ route('admin.prb.laporan.index') }}"
               class="py-2 px-3.5 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs transition-colors">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Laporan
            </a>
            <a href="{{ route($routeName . '.export', request()->query()) }}"
               class="py-2 px-4 inline-flex items-center gap-x-2 text-xs font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm cursor-pointer transition-colors">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Pencarian -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs p-4 sm:p-5 no-print">
        <form action="{{ route($routeName) }}" method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pencarian Pasien / Diagnosa</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik Nama Pasien, NIK, No. BPJS, Alamat, atau Diagnosa..."
                       class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
            </div>
            <div class="w-full sm:w-40">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Data per Halaman</label>
                <select name="per_page" class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                    <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="py-2 px-4 inline-flex items-center justify-center text-xs font-bold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-2xs cursor-pointer">
                    Terapkan
                </button>
                <a href="{{ route($routeName) }}"
                   class="py-2 px-3.5 inline-flex items-center justify-center text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Pasien -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
        <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-gray-800">
                {{ $isAktif ? 'Daftar Pasien PRB Aktif' : 'Daftar Pasien PRB Tidak Aktif' }}
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isAktif ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                Total: {{ $pasienList->total() }} Pasien
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Pasien</th>
                        <th class="py-3 px-4">NIK / No. BPJS</th>
                        <th class="py-3 px-4">Diagnosa</th>
                        <th class="py-3 px-4">Tgl Terdaftar</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">No. HP / Alamat</th>
                        <th class="py-3 px-4 text-center no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-xs">
                    @forelse($pasienList as $index => $prb)
                        @php
                            $pasien = $prb->pasien;
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="py-3 px-4 text-center font-semibold text-gray-600">
                                {{ $pasienList->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 font-bold text-gray-900">
                                {{ $pasien?->nama ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-gray-700">
                                <div>{{ $pasien?->nik ?? '-' }}</div>
                                @if($pasien?->no_bpjs)
                                    <div class="text-gray-500">BPJS: {{ $pasien->no_bpjs }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-gray-800">
                                {{ $prb->diagnosa ?: '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-gray-700 whitespace-nowrap">
                                {{ $prb->tanggal_terdaftar ? \Carbon\Carbon::parse($prb->tanggal_terdaftar)->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($isAktif)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-700 border border-red-200">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                <div>{{ $pasien?->no_hp ?: '-' }}</div>
                                <div class="text-[11px] text-gray-500 truncate max-w-[220px]">{{ $pasien?->alamat ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center no-print">
                                <a href="{{ route('admin.prb.show', $prb->id) }}"
                                   class="py-1 px-2.5 inline-flex items-center gap-1 text-[11px] font-bold rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">
                                    Detail PRB
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-xs text-gray-500 italic">
                                Tidak ada data pasien PRB dengan status {{ $isAktif ? 'Aktif' : 'Tidak Aktif' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pasienList->hasPages())
            <div class="p-4 border-t border-gray-200 no-print">
                {{ $pasienList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
