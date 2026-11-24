@extends('layouts.app')

@php
    $isFiltered = request()->filled('q') || request()->filled('aksi') || request()->filled('dari_tanggal') || request()->filled('sampai_tanggal');
@endphp

@section('content')
<div x-data="logAktivitasHandler">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Log Aktivitas</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau rekam jejak audit aktivitas yang dilakukan oleh petugas medis dan administrator.</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Tombol Refresh Halaman -->
            <button type="button" 
                @click="refreshHalaman()" 
                :disabled="isRefreshing"
                class="py-2.5 px-3.5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs transition-colors cursor-pointer disabled:opacity-75 disabled:cursor-wait"
                title="Segarkan data log aktivitas">
                <svg class="size-4 text-gray-500" :class="{ 'animate-spin': isRefreshing }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span x-text="isRefreshing ? 'Memuat...' : 'Refresh'">Refresh</span>
            </button>

            <!-- Tombol Download Backup Excel -->
            <button type="button" 
                @click="modalExportOpen = true" 
                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Backup Excel</span>
            </button>

            @if(auth()->user()?->isAdmin())
            <!-- Tombol Bersihkan Log Lama -->
            <button type="button" 
                @click="modalPruneOpen = true" 
                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 hover:border-rose-300 shadow-sm transition-colors">
                <svg class="size-4 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <span>Bersihkan Log Lama</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Alert Notifikasi Session -->
    @if(session('success'))
    <div class="mb-5 bg-teal-50 border border-teal-200 text-sm text-teal-800 rounded-lg p-4 flex items-start gap-3" role="alert">
        <svg class="size-5 text-teal-600 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('info'))
    <div class="mb-5 bg-blue-50 border border-blue-200 text-sm text-blue-800 rounded-lg p-4 flex items-start gap-3" role="alert">
        <svg class="size-5 text-blue-600 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <span class="font-medium">{{ session('info') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 flex items-start gap-3" role="alert">
        <svg class="size-5 text-red-600 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Kartu Statistik & Status Kapasitas Log -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Total Log -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Total Catatan Log</span>
                    <span class="text-xl font-bold text-gray-900 font-mono">{{ number_format($totalLogs) }}</span>
                    <span class="text-xs text-gray-500">baris</span>
                </div>
            </div>
        </div>

        <!-- Log Berusia > 30 Hari -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Log Usia > 30 Hari</span>
                    <span class="text-xl font-bold {{ $countLebih30Hari > 0 ? 'text-amber-700' : 'text-gray-800' }} font-mono">{{ number_format($countLebih30Hari) }}</span>
                    <span class="text-xs text-gray-500">dapat diarsipkan</span>
                </div>
            </div>
        </div>

        <!-- Rentang Waktu Log -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-teal-50 text-teal-600">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Log Tertua di Sistem</span>
                    <span class="text-xs font-semibold text-gray-800 block">
                        {{ $logTertua ? \Carbon\Carbon::parse($logTertua)->translatedFormat('d M Y, H:i') : 'Belum ada data' }}
                    </span>
                    <span class="text-[11px] text-gray-400">Terbaru: {{ $logTerbaru ? \Carbon\Carbon::parse($logTerbaru)->translatedFormat('d M Y, H:i') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Log -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('admin.log.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Pencarian Kata Kunci -->
            <div class="lg:col-span-2">
                <label for="q" class="block text-xs font-medium text-gray-600 mb-1">Cari Kata Kunci</label>
                <div class="relative">
                    <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Cari deskripsi, aksi, nama petugas, NIK..." class="py-2 px-3 ps-9 block w-full border-gray-200 rounded-lg text-xs focus:border-blue-500 focus:ring-blue-500 border">
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                        <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Jenis Aksi -->
            <div>
                <label for="aksi" class="block text-xs font-medium text-gray-600 mb-1">Kategori Aksi</label>
                <select id="aksi" name="aksi" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-xs focus:border-blue-500 focus:ring-blue-500 border">
                    <option value="">Semua Aksi</option>
                    @foreach($daftarAksi as $a)
                        <option value="{{ $a }}" {{ request('aksi') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div>
                <label for="dari_tanggal" class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" id="dari_tanggal" name="dari_tanggal" value="{{ request('dari_tanggal') }}" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-xs focus:border-blue-500 focus:ring-blue-500 border">
            </div>

            <!-- Sampai Tanggal & Tombol Aksi -->
            <div>
                <label for="sampai_tanggal" class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <div class="flex items-center gap-2">
                    <input type="date" id="sampai_tanggal" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-xs focus:border-blue-500 focus:ring-blue-500 border">
                    <button type="submit" class="py-2 px-3 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors" title="Filter Data">
                        Filter
                    </button>
                    @if($isFiltered)
                    <a href="{{ route('admin.log.index') }}" class="py-2 px-2.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold hover:bg-gray-200 transition-colors" title="Reset Filter">
                        ✕
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Banner Info Hasil Filter (jika filter sedang aktif) -->
    @if($isFiltered)
    <div class="mb-4 p-3 bg-blue-50/80 border border-blue-200 rounded-xl flex items-center justify-between gap-3 text-xs text-blue-900">
        <div class="flex items-center gap-2 flex-wrap">
            <svg class="size-4 text-blue-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
            </svg>
            <span>Menampilkan <strong>{{ number_format($logs->total()) }}</strong> data log hasil filter:</span>
            @if(request('aksi'))
                <span class="px-2 py-0.5 bg-blue-100 font-semibold rounded text-blue-800">Aksi: {{ request('aksi') }}</span>
            @endif
            @if(request('q'))
                <span class="px-2 py-0.5 bg-blue-100 rounded text-blue-800">Kata Kunci: "{{ request('q') }}"</span>
            @endif
            @if(request('dari_tanggal'))
                <span class="px-2 py-0.5 bg-blue-100 rounded text-blue-800">Dari: {{ request('dari_tanggal') }}</span>
            @endif
            @if(request('sampai_tanggal'))
                <span class="px-2 py-0.5 bg-blue-100 rounded text-blue-800">Sampai: {{ request('sampai_tanggal') }}</span>
            @endif
        </div>
        <a href="{{ route('admin.log.index') }}" class="font-semibold text-blue-700 hover:text-blue-900 underline shrink-0">
            Reset Filter
        </a>
    </div>
    @endif

    <!-- Tabel Data Log Aktivitas -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/75">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-5 py-3 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        <th scope="col" class="px-5 py-3 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">Petugas Pelaku</th>
                        <th scope="col" class="px-5 py-3 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">Deskripsi Aktivitas</th>
                        <th scope="col" class="px-5 py-3 text-start text-xs font-semibold text-gray-600 uppercase tracking-wider">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- Waktu -->
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-700">
                            <span class="font-medium text-gray-900 block">{{ $log->created_at->translatedFormat('d F Y') }}</span>
                            <span class="text-[11px] text-gray-500 font-mono">{{ $log->created_at->format('H:i:s') }} WIB</span>
                        </td>

                        <!-- Aksi Tag -->
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            @php
                                $aksi = $log->aksi;
                                $badgeClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                if (str_contains($aksi, 'TAMBAH') || str_contains($aksi, 'CREATE') || str_contains($aksi, 'IMPORT')) {
                                    $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                                } elseif (str_contains($aksi, 'UPDATE') || str_contains($aksi, 'UBAH') || str_contains($aksi, 'EDIT')) {
                                    $badgeClass = 'bg-blue-50 text-blue-800 border-blue-200';
                                } elseif (str_contains($aksi, 'HAPUS') || str_contains($aksi, 'DELETE') || str_contains($aksi, 'BERSIHKAN')) {
                                    $badgeClass = 'bg-rose-50 text-rose-800 border-rose-200';
                                } elseif (str_contains($aksi, 'LOGIN') || str_contains($aksi, 'LOGOUT')) {
                                    $badgeClass = 'bg-purple-50 text-purple-800 border-purple-200';
                                }
                            @endphp
                            <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-[11px] font-semibold border {{ $badgeClass }}">
                                {{ $log->aksi }}
                            </span>
                        </td>

                        <!-- Petugas -->
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                            @if($log->user)
                                <span class="font-semibold text-gray-900 block">{{ $log->user->nama }}</span>
                                <span class="text-[11px] text-gray-500 block">
                                    {{ $log->user->jabatan ?: ucfirst($log->user->peran) }}
                                    @if($log->user->nik)
                                        • <span class="font-mono">{{ $log->user->nik }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-500 italic">Sistem / Pengunjung</span>
                            @endif
                        </td>

                        <!-- Deskripsi -->
                        <td class="px-5 py-3.5 text-xs text-gray-800 whitespace-pre-line leading-relaxed max-w-xl">
                            {{ $log->deskripsi }}
                        </td>

                        <!-- IP Address -->
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-500 font-mono">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                            <svg class="size-9 text-gray-300 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Tidak ada catatan log aktivitas yang cocok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- ================= MODAL EXPORT / BACKUP EXCEL ================= -->
    <template x-teleport="body">
        <div x-show="modalExportOpen" 
             x-transition:enter="ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-150" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4"
             @keydown.escape.window="modalExportOpen = false" 
             style="display: none;">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xl max-w-lg w-full overflow-hidden" 
                 @click.outside="modalExportOpen = false">
                <!-- Header Modal -->
                <div class="px-6 py-4 border-b border-gray-100 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Download Backup Log (Excel)</h3>
                            <p class="text-xs text-gray-500">Unduh arsip log aktivitas ke dalam file spreadsheet .xlsx</p>
                        </div>
                    </div>
                    <button type="button" @click="modalExportOpen = false" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 transition-colors">
                        ✕
                    </button>
                </div>

                <!-- Form Export -->
                <form action="{{ route('admin.log.export') }}" method="GET" class="p-6">
                    <!-- Teruskan Parameter Filter Halaman -->
                    <input type="hidden" name="filter_q" value="{{ request('q') }}">
                    <input type="hidden" name="filter_aksi" value="{{ request('aksi') }}">
                    <input type="hidden" name="filter_dari_tanggal" value="{{ request('dari_tanggal') }}">
                    <input type="hidden" name="filter_sampai_tanggal" value="{{ request('sampai_tanggal') }}">

                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Pilih Periode Data yang Ingin Diunduh:</p>
                    
                    <div class="space-y-2.5 mb-5">
                        <!-- Opsi Sesuai Filter Aktif (Jika Filter Sedang Dipakai) -->
                        @if($isFiltered)
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-emerald-500 bg-emerald-50/60 hover:bg-emerald-50 cursor-pointer transition-colors"
                            :class="exportPeriode === 'sesuai_filter' ? 'border-emerald-600 ring-2 ring-emerald-500 bg-emerald-50' : ''">
                            <input type="radio" name="periode" value="sesuai_filter" x-model="exportPeriode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs w-full">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-gray-900">Sesuai Hasil Filter yang Sedang Aktif</span>
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded">Filter Aktif</span>
                                </div>
                                <span class="text-gray-600 mt-1 block">
                                    Unduh hanya <strong>{{ number_format($logs->total()) }} data</strong> yang cocok dengan filter saat ini.
                                </span>
                            </div>
                        </label>
                        @endif

                        <!-- Semua Log -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="exportPeriode === 'all' ? 'border-emerald-500 bg-emerald-50/40' : ''">
                            <input type="radio" name="periode" value="all" x-model="exportPeriode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-800 block">Seluruh Riwayat Log</span>
                                <span class="text-gray-500">Unduh seluruh catatan yang ada di database (Total {{ number_format($totalLogs) }} baris).</span>
                            </div>
                        </label>

                        <!-- Lebih dari 30 Hari -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="exportPeriode === '30_hari' ? 'border-emerald-500 bg-emerald-50/40' : ''">
                            <input type="radio" name="periode" value="30_hari" x-model="exportPeriode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-800 block">Log Berusia Lebih Dari 30 Hari</span>
                                <span class="text-gray-500">Arsipkan data lama sebelum dibersihkan ({{ number_format($countLebih30Hari) }} baris).</span>
                            </div>
                        </label>

                        <!-- Lebih dari 90 Hari -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="exportPeriode === '90_hari' ? 'border-emerald-500 bg-emerald-50/40' : ''">
                            <input type="radio" name="periode" value="90_hari" x-model="exportPeriode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-800 block">Log Berusia Lebih Dari 90 Hari</span>
                                <span class="text-gray-500">Arsip data log yang berusia lebih dari 3 bulan ({{ number_format($countLebih90Hari) }} baris).</span>
                            </div>
                        </label>

                        <!-- 30 Hari Terakhir -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="exportPeriode === '30_hari_terakhir' ? 'border-emerald-500 bg-emerald-50/40' : ''">
                            <input type="radio" name="periode" value="30_hari_terakhir" x-model="exportPeriode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-800 block">30 Hari Terakhir Saja</span>
                                <span class="text-gray-500">Unduh rangkuman aktivitas bulan berjalan.</span>
                            </div>
                        </label>

                        <!-- Rentang Tanggal Khusus -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="exportPeriode === 'custom' ? 'border-emerald-500 bg-emerald-50/40' : ''">
                            <input type="radio" name="periode" value="custom" x-model="exportPeriode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs w-full">
                                <span class="font-bold text-gray-800 block">Rentang Tanggal Khusus</span>
                                <div class="grid grid-cols-2 gap-2 mt-2" x-show="exportPeriode === 'custom'">
                                    <div>
                                        <span class="text-[11px] text-gray-500 block mb-0.5">Dari:</span>
                                        <input type="date" name="dari_tanggal" class="py-1 px-2 border border-gray-200 rounded-md text-xs w-full">
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-gray-500 block mb-0.5">Sampai:</span>
                                        <input type="date" name="sampai_tanggal" class="py-1 px-2 border border-gray-200 rounded-md text-xs w-full">
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Tombol Aksi Modal -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="modalExportOpen = false" class="py-2.5 px-4 text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit" @click="setTimeout(() => modalExportOpen = false, 500)" class="py-2.5 px-4 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg inline-flex items-center gap-1.5 shadow-sm transition-colors">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Unduh File Excel (.xlsx)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- ================= MODAL 1: PILIH KRITERIA PEMBERSIHAN ================= -->
    @if(auth()->user()?->isAdmin())
    <template x-teleport="body">
        <div x-show="modalPruneOpen" 
             x-transition:enter="ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-150" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4"
             @keydown.escape.window="modalPruneOpen = false" 
             style="display: none;">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xl max-w-lg w-full overflow-hidden" 
                 @click.outside="modalPruneOpen = false">
                <!-- Header Modal -->
                <div class="px-6 py-4 border-b border-gray-100 bg-rose-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-rose-100 text-rose-700 rounded-lg">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Bersihkan Data Log Aktivitas</h3>
                            <p class="text-xs text-rose-600 font-medium">Hapus catatan log lama untuk menjaga performa database</p>
                        </div>
                    </div>
                    <button type="button" @click="modalPruneOpen = false" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 transition-colors">
                        ✕
                    </button>
                </div>

                <!-- Konten Pemilihan Kriteria -->
                <div class="p-6">
                    <!-- Peringatan Keamanan -->
                    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-start gap-2.5">
                        <svg class="size-5 text-amber-600 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <div class="text-xs text-amber-900 leading-relaxed">
                            <strong>Perhatian:</strong> Data log yang dibersihkan akan dihapus permanen dari database. Pastikan Anda telah men-download file <strong>Backup Excel</strong> terlebih dahulu sebagai arsip.
                        </div>
                    </div>

                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Pilih Kriteria Log yang Ingin Dihapus:</p>

                    <div class="space-y-2.5 mb-5">
                        <!-- Opsi Khusus: Sesuai Filter yang Sedang Aktif -->
                        @if($isFiltered)
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-rose-400 bg-rose-50/60 hover:bg-rose-50 cursor-pointer transition-colors shadow-2xs"
                            :class="prunePeriode === 'sesuai_filter' ? 'border-rose-600 ring-2 ring-rose-500 bg-rose-50' : ''">
                            <input type="radio" name="pilihan_periode" value="sesuai_filter" x-model="prunePeriode" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="text-xs w-full">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-rose-950 text-sm">Sesuai Hasil Filter yang Sedang Aktif</span>
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded">Filter Aktif</span>
                                </div>
                                <span class="text-rose-800 mt-1 block font-medium">
                                    Hanya menghapus <strong>{{ number_format($logs->total()) }} data log</strong> yang sedang tampil pada filter saat ini:
                                </span>
                                <div class="mt-1.5 flex flex-wrap gap-1.5 text-[11px] font-mono">
                                    @if(request('aksi'))
                                        <span class="px-2 py-0.5 bg-white border border-rose-200 rounded text-rose-800 font-semibold">Aksi: {{ request('aksi') }}</span>
                                    @endif
                                    @if(request('q'))
                                        <span class="px-2 py-0.5 bg-white border border-rose-200 rounded text-rose-800">Cari: "{{ request('q') }}"</span>
                                    @endif
                                    @if(request('dari_tanggal'))
                                        <span class="px-2 py-0.5 bg-white border border-rose-200 rounded text-rose-800">Dari: {{ request('dari_tanggal') }}</span>
                                    @endif
                                    @if(request('sampai_tanggal'))
                                        <span class="px-2 py-0.5 bg-white border border-rose-200 rounded text-rose-800">Sampai: {{ request('sampai_tanggal') }}</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endif

                        <!-- Opsi 1: Lebih dari 30 Hari (Rekomendasi) -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="prunePeriode === '30_hari' ? 'border-rose-500 bg-rose-50/30' : ''">
                            <input type="radio" name="pilihan_periode" value="30_hari" x-model="prunePeriode" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-gray-800">Lebih Dari 30 Hari yang Lalu</span>
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded">Direkomendasikan</span>
                                </div>
                                <span class="text-gray-500 mt-0.5 block">Log 30 hari terakhir tetap aman di sistem untuk audit harian (± {{ number_format($countLebih30Hari) }} data terhapus).</span>
                            </div>
                        </label>

                        <!-- Opsi 2: Lebih dari 60 Hari -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="prunePeriode === '60_hari' ? 'border-rose-500 bg-rose-50/30' : ''">
                            <input type="radio" name="pilihan_periode" value="60_hari" x-model="prunePeriode" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-800 block">Lebih Dari 60 Hari yang Lalu</span>
                                <span class="text-gray-500">Hapus log yang berusia lebih dari 2 bulan (± {{ number_format($countLebih60Hari) }} data terhapus).</span>
                            </div>
                        </label>

                        <!-- Opsi 3: Lebih dari 90 Hari -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="prunePeriode === '90_hari' ? 'border-rose-500 bg-rose-50/30' : ''">
                            <input type="radio" name="pilihan_periode" value="90_hari" x-model="prunePeriode" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="text-xs">
                                <span class="font-bold text-gray-800 block">Lebih Dari 90 Hari yang Lalu</span>
                                <span class="text-gray-500">Hapus log yang berusia lebih dari 3 bulan (± {{ number_format($countLebih90Hari) }} data terhapus).</span>
                            </div>
                        </label>

                        <!-- Opsi 4: Sebelum Tanggal Tertentu -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50/70 cursor-pointer transition-colors"
                            :class="prunePeriode === 'sebelum_tanggal' ? 'border-rose-500 bg-rose-50/30' : ''">
                            <input type="radio" name="pilihan_periode" value="sebelum_tanggal" x-model="prunePeriode" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="text-xs w-full">
                                <span class="font-bold text-gray-800 block">Sebelum Tanggal Tertentu</span>
                                <span class="text-gray-500 block mb-1.5">Tentukan batas tanggal log yang ingin dibersihkan.</span>
                                <input type="date" x-model="sebelumTanggal" x-show="prunePeriode === 'sebelum_tanggal'" class="py-1.5 px-3 border border-gray-200 rounded-md text-xs w-full max-w-xs">
                            </div>
                        </label>

                        <!-- Opsi 5: Hapus Seluruh Log -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 bg-rose-50/20 hover:bg-rose-50/50 cursor-pointer transition-colors"
                            :class="prunePeriode === 'semua' ? 'border-rose-500 bg-rose-50/60' : ''">
                            <input type="radio" name="pilihan_periode" value="semua" x-model="prunePeriode" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="text-xs w-full">
                                <span class="font-bold text-rose-800 block">Kosongkan Seluruh Riwayat Log (Reset Total)</span>
                                <span class="text-gray-500 block mb-2">Seluruh {{ number_format($totalLogs) }} baris log akan dihapus tanpa sisa.</span>
                                <div x-show="prunePeriode === 'semua'" class="mt-2 p-2.5 bg-white border border-rose-200 rounded-lg">
                                    <label class="block text-[11px] font-semibold text-rose-700 mb-1">
                                        Ketik kata <span class="font-mono underline">KOSONGKAN</span> untuk konfirmasi:
                                    </label>
                                    <input type="text" x-model="konfirmasiSemua" placeholder="Ketik KOSONGKAN disini" class="py-1 px-2.5 border border-gray-300 rounded text-xs w-full font-mono uppercase">
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Tombol Aksi Modal 1 -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="modalPruneOpen = false" class="py-2.5 px-4 text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="button" 
                            @click="bukaKonfirmasiPassword()"
                            :disabled="prunePeriode === 'semua' && konfirmasiSemua.trim().toUpperCase() !== 'KOSONGKAN'"
                            class="py-2.5 px-4 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg inline-flex items-center gap-1.5 shadow-sm transition-colors">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Ya, Bersihkan Log
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ================= MODAL 2: POP-UP KONFIRMASI PASSWORD ================= -->
    <template x-teleport="body">
        <div x-show="modalPasswordConfirmOpen" 
             x-transition:enter="ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-150" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             @keydown.escape.window="modalPasswordConfirmOpen = false" 
             style="display: none;">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-2xl max-w-md w-full overflow-hidden" 
                 @click.outside="modalPasswordConfirmOpen = false">
                <!-- Header Modal Konfirmasi -->
                <div class="px-6 py-4 border-b border-gray-100 bg-rose-50/70 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-rose-100 text-rose-700 rounded-lg">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Verifikasi Keamanan Admin</h3>
                            <p class="text-xs text-rose-600 font-medium">Konfirmasi password untuk mengeksekusi</p>
                        </div>
                    </div>
                    <button type="button" @click="modalPasswordConfirmOpen = false" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 transition-colors">
                        ✕
                    </button>
                </div>

                <!-- Form Eksekusi Pembersihan -->
                <form action="{{ route('admin.log.prune') }}" method="POST" class="p-6">
                    @csrf
                    <!-- Data Kriteria Terpilih (Dikirim via Hidden Inputs) -->
                    <input type="hidden" name="periode" :value="prunePeriode">
                    <input type="hidden" name="sebelum_tanggal" :value="sebelumTanggal">
                    <input type="hidden" name="konfirmasi_semua" :value="konfirmasiSemua">

                    <!-- Teruskan Parameter Filter Halaman -->
                    <input type="hidden" name="filter_q" value="{{ request('q') }}">
                    <input type="hidden" name="filter_aksi" value="{{ request('aksi') }}">
                    <input type="hidden" name="filter_dari_tanggal" value="{{ request('dari_tanggal') }}">
                    <input type="hidden" name="filter_sampai_tanggal" value="{{ request('sampai_tanggal') }}">

                    <!-- Ringkasan Target Penghapusan -->
                    <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs text-slate-700">
                        <span class="text-gray-500 block mb-1">Target data yang akan dibersihkan:</span>
                        <span class="font-bold text-rose-700 text-sm block leading-snug" x-text="labelKriteriaTerpilih"></span>
                        <span class="text-[11px] text-gray-400 block mt-1.5">Tindakan ini permanen dan tidak dapat dibatalkan.</span>
                    </div>

                    <!-- Input Password Akun -->
                    <div class="mb-5">
                        <label for="admin_password_confirm" class="block text-xs font-semibold text-gray-800 mb-1.5">
                            Masukkan Password Akun Anda <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" 
                            id="admin_password_confirm" 
                            name="password" 
                            x-model="adminPassword" 
                            required 
                            placeholder="Ketik password login admin Anda..." 
                            class="py-2.5 px-3 block w-full border-gray-300 rounded-lg text-xs focus:border-rose-500 focus:ring-rose-500 shadow-sm border bg-white">
                        <p class="text-[11px] text-gray-500 mt-1">Masukkan password login akun Anda untuk melanjutkan proses penghapusan data.</p>
                    </div>

                    <!-- Tombol Aksi Modal Konfirmasi -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="modalPasswordConfirmOpen = false; modalPruneOpen = true" class="py-2.5 px-4 text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                            ← Kembali
                        </button>
                        <button type="submit" 
                            :disabled="!adminPassword"
                            class="py-2.5 px-4 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg inline-flex items-center gap-1.5 shadow-sm transition-colors">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Konfirmasi & Hapus Permanen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    function registerLogAktivitasComponent() {
        if (!window.Alpine) return;
        
        Alpine.data('logAktivitasHandler', () => ({
            isRefreshing: false,
            modalExportOpen: false, 
            modalPruneOpen: false, 
            modalPasswordConfirmOpen: false,
            isFilterAktif: {{ $isFiltered ? 'true' : 'false' }},
            exportPeriode: '{{ $isFiltered ? 'sesuai_filter' : 'all' }}', 
            prunePeriode: '{{ $isFiltered ? 'sesuai_filter' : '30_hari' }}',
            sebelumTanggal: '',
            konfirmasiSemua: '',
            adminPassword: '',

            refreshHalaman() {
                this.isRefreshing = true;
                window.location.reload();
            },

            get labelKriteriaTerpilih() {
                if (this.prunePeriode === 'sesuai_filter') {
                    return 'Data Sesuai Filter yang Sedang Aktif ({{ number_format($logs->total()) }} data)';
                }
                if (this.prunePeriode === '30_hari') {
                    return 'Log Berusia Lebih Dari 30 Hari (± {{ number_format($countLebih30Hari) }} data)';
                }
                if (this.prunePeriode === '60_hari') {
                    return 'Log Berusia Lebih Dari 60 Hari (± {{ number_format($countLebih60Hari) }} data)';
                }
                if (this.prunePeriode === '90_hari') {
                    return 'Log Berusia Lebih Dari 90 Hari (± {{ number_format($countLebih90Hari) }} data)';
                }
                if (this.prunePeriode === 'sebelum_tanggal') {
                    return 'Log Sebelum Tanggal: ' + (this.sebelumTanggal || '(Belum dipilih)');
                }
                if (this.prunePeriode === 'semua') {
                    return 'Kosongkan Seluruh Riwayat Log (Total {{ number_format($totalLogs) }} data)';
                }
                return '-';
            },

            bukaKonfirmasiPassword() {
                if (this.prunePeriode === 'sebelum_tanggal' && !this.sebelumTanggal) {
                    alert('Silakan tentukan tanggal batas log yang ingin dibersihkan terlebih dahulu.');
                    return;
                }
                if (this.prunePeriode === 'semua' && this.konfirmasiSemua.trim().toUpperCase() !== 'KOSONGKAN') {
                    alert('Ketik kata KOSONGKAN untuk melanjutkan konfirmasi reset total.');
                    return;
                }
                this.adminPassword = '';
                this.modalPruneOpen = false;
                this.modalPasswordConfirmOpen = true;
                this.$nextTick(() => {
                    const input = document.getElementById('admin_password_confirm');
                    if (input) {
                        input.focus();
                    }
                });
            }
        }));
    }

    if (window.Alpine) {
        registerLogAktivitasComponent();
    } else {
        document.addEventListener('alpine:init', registerLogAktivitasComponent);
    }
})();
</script>
@endpush
