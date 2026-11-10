@extends('layouts.app')

@push('styles')
<style>
/* Memaksa scrollbar bawah selalu terlihat agar user tahu tabel bisa digeser */
.custom-scrollbar-x::-webkit-scrollbar {
    height: 8px;
    display: block !important;
}
.custom-scrollbar-x::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.custom-scrollbar-x::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar-x::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.custom-scrollbar-x {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}
</style>
@endpush

@section('content')
<header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Data Pasien PRB</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar pasien yang terdaftar dalam Program Rujuk Balik (PRB).</p>
    </div>
    <div class="sm:ms-auto flex items-center gap-2">
        {{-- <a href="{{ route('admin.prb.register') }}" class="py-2.5 px-3.5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:text-blue-600 shadow-2xs transition-colors">
            <svg class="flex-shrink-0 size-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Buku Register / Absensi
        </a> --}}
        @if(auth()->user()?->canTambahPasienPrb())
        <a href="{{ route('admin.prb.create') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Tambah Pasien PRB
        </a>
        @endif
    </div>
</header>

<div class="flex flex-col gap-6">
    <!-- Search & Filter Box -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form action="{{ route('admin.prb.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                    <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="py-2.5 ps-10 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Cari Nama / NIK Pasien...">
            </div>

            <!-- Clickable Filter Status Pills -->
            <div class="flex items-center gap-1.5 p-1 bg-gray-100 rounded-lg border border-gray-200 self-start sm:self-auto">
                <a href="{{ route('admin.prb.index', array_filter(['search' => request('search'), 'status' => 'semua'])) }}" 
                   class="py-1.5 px-3 rounded-md text-xs transition-all {{ ($status ?? 'semua') === 'semua' ? 'bg-white text-blue-700 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium' }}">
                    Semua Status
                </a>
                <a href="{{ route('admin.prb.index', array_filter(['search' => request('search'), 'status' => 'aktif'])) }}" 
                   class="py-1.5 px-3 rounded-md text-xs transition-all flex items-center gap-1.5 {{ ($status ?? 'semua') === 'aktif' ? 'bg-white text-emerald-700 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium' }}">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    Aktif
                </a>
                <a href="{{ route('admin.prb.index', array_filter(['search' => request('search'), 'status' => 'tidak_aktif'])) }}" 
                   class="py-1.5 px-3 rounded-md text-xs transition-all flex items-center gap-1.5 {{ ($status ?? 'semua') === 'tidak_aktif' ? 'bg-white text-red-700 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium' }}">
                    <span class="size-1.5 rounded-full bg-red-500"></span>
                    Tidak Aktif
                </a>
            </div>

            <input type="hidden" name="status" value="{{ $status ?? 'semua' }}">

            <div class="flex items-center gap-2">
                <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm">
                    Cari
                </button>
                @if(request('search') || (request('status') && request('status') !== 'semua'))
                    <a href="{{ route('admin.prb.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto pb-2 custom-scrollbar-x">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Diagnosa</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Status PRB</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Terdaftar</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Jatuh Tempo Terdekat</th>
                        <th scope="col" class="px-6 py-3 text-end text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($pasienPrb as $prb)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-800">{{ $prb->pasien->nama }}</div>
                            <div class="text-xs text-gray-500">NIK: {{ $prb->pasien->nik }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($prb->diagnosa)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Kode Diagnosa PRB">
                                    {{ $prb->diagnosa }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($prb->status_keaktifan == 'aktif')
                                <span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                    <span class="size-1.5 inline-block rounded-full bg-green-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                    <span class="size-1.5 inline-block rounded-full bg-red-500"></span>
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($prb->tanggal_terdaftar)->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @php
                                $latestRiwayat = $prb->riwayatPengambilan->first();
                            @endphp
                            @if($latestRiwayat && $latestRiwayat->tanggal_jatuh_tempo)
                                {{ \Carbon\Carbon::parse($latestRiwayat->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                            @else
                                <span class="text-gray-400 italic">Belum ada data</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                            <a href="{{ route('admin.prb.show', $prb->id) }}" class="inline-flex items-center gap-x-1.5 text-sm text-blue-600 decoration-2 hover:underline font-medium">
                                Detail Riwayat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">
                            Tidak ada data pasien PRB
                            @if(request('search')) untuk pencarian "<strong>{{ request('search') }}</strong>"@endif
                            @if(!empty($status) && $status !== 'semua') dengan status <strong>{{ $status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}</strong>@endif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pasienPrb->total() > 0)
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $pasienPrb->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
