@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.prb.laporan.index') }}" class="hover:text-blue-600 transition-colors">Laporan PRB</a>
                <span>/</span>
                <span class="text-gray-800 font-semibold">Pasien Belum Ambil Obat</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Laporan Pasien yang Belum Ambil Obat</h1>
            <p class="mt-1 text-xs sm:text-sm text-gray-600">
                Daftar pasien PRB aktif dengan jadwal pengambilan obat yang belum diambil.
            </p>
        </div>

        <div class="flex items-center gap-2 no-print">
            <a href="{{ route('admin.prb.laporan.index') }}"
               class="py-2 px-3.5 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs transition-colors">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Laporan
            </a>
            <a href="{{ route('admin.prb.laporan.belum-ambil-obat.export', request()->query()) }}"
               class="py-2 px-4 inline-flex items-center gap-x-2 text-xs font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm cursor-pointer transition-colors">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Card (Hari / Tanggal, Bulan, Tahun, Pencarian) -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs p-4 sm:p-5 no-print">
        <form action="{{ route('admin.prb.laporan.belum-ambil-obat') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Filter Hari / Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}"
                       class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Filter Bulan</label>
                <select name="bulan" class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                    <option value="">Semua Bulan</option>
                    @php
                        $namaBulan = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    @foreach($namaBulan as $num => $label)
                        <option value="{{ $num }}" {{ (string) $bulan === (string) $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Filter Tahun</label>
                <select name="tahun" class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                    <option value="">Semua Tahun</option>
                    @foreach($daftarTahun as $th)
                        <option value="{{ $th }}" {{ (string) $tahun === (string) $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pencarian Pasien / Diagnosa</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama, NIK, No. BPJS, Diagnosa..."
                       class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 inline-flex justify-center items-center gap-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-2xs cursor-pointer">
                    Terapkan
                </button>
                <a href="{{ route('admin.prb.laporan.belum-ambil-obat') }}"
                   class="py-2 px-3 inline-flex justify-center items-center text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs">
                    Reset
                </a>
            </div>
        </form>
        <p class="text-[11px] text-gray-500 mt-2">
            * Jika <strong>Filter Hari / Tanggal</strong> diisi, filter akan menampilkan jadwal tepat pada tanggal tersebut. Kosongkan tanggal jika ingin memfilter berdasarkan <strong>Bulan</strong> dan/atau <strong>Tahun</strong>.
        </p>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
        <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-gray-800">Daftar Pasien Belum Ambil Obat</h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                Total: {{ $riwayatList->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Pasien</th>
                        <th class="py-3 px-4">Diagnosa</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">No. HP / Alamat</th>
                        <th class="py-3 px-4 text-center no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-xs">
                    @forelse($riwayatList as $index => $r)
                        @php
                            $pasien = $r->pasienPrb?->pasien;
                            $jatuhTempo = $r->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($r->tanggal_jatuh_tempo) : null;
                            $isOverdue = $jatuhTempo && $jatuhTempo->lt(now()->startOfDay());
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="py-3 px-4 text-center font-semibold text-gray-600">
                                {{ $riwayatList->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-gray-900">{{ $pasien?->nama ?? '-' }}</div>
                                <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                    NIK: {{ $pasien?->nik ?? '-' }}
                                    @if($pasien?->no_bpjs)
                                        | BPJS: {{ $pasien->no_bpjs }}
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 font-medium text-gray-800">
                                {{ $r->diagnosa_lengkap ?: ($r->pasienPrb?->diagnosa ?: '-') }}
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-gray-800 whitespace-nowrap">
                                {{ $jatuhTempo ? $jatuhTempo->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($isOverdue)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-700 border border-red-200">
                                        Terlambat {{ $jatuhTempo->diffInDays(now()->startOfDay()) }} Hari
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Belum Diambil
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                <div>{{ $pasien?->no_hp ?: '-' }}</div>
                                <div class="text-[11px] text-gray-500 truncate max-w-[220px]">{{ $pasien?->alamat ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center no-print">
                                @if($r->pasien_prb_id)
                                    <a href="{{ route('admin.prb.show', $r->pasien_prb_id) }}"
                                       class="py-1 px-2.5 inline-flex items-center gap-1 text-[11px] font-bold rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">
                                        Detail PRB
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-xs text-gray-500 italic">
                                Tidak ada data pasien yang belum mengambil obat pada filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($riwayatList->hasPages())
            <div class="p-4 border-t border-gray-200 no-print">
                {{ $riwayatList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
