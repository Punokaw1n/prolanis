@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Arsip Informed Consent</h1>
        <p class="text-sm text-gray-500 mt-1">Daftar seluruh surat persetujuan tindakan pasien.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.persetujuan.create') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none transition-colors shadow-sm">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Terbitkan Surat
        </a>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <!-- Header & Search -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <form action="{{ route('admin.persetujuan.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <!-- Search Box -->
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                    <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input class="py-2 ps-10 pe-3 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIK pasien...">
            </div>
            
            <!-- Status Filter -->
            <div class="w-full sm:w-auto">
                <select name="status" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="menunggu_tanda_tangan" {{ request('status') === 'menunggu_tanda_tangan' ? 'selected' : '' }}>Menunggu Tanda Tangan</option>
                    <option value="ditandatangani" {{ request('status') === 'ditandatangani' ? 'selected' : '' }}>Ditandatangani</option>
                    <option value="kedaluwarsa" {{ request('status') === 'kedaluwarsa' ? 'selected' : '' }}>Kedaluwarsa</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            
            <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                Filter
            </button>
            
            @if(request('search') || request('status'))
                <a href="{{ route('admin.persetujuan.index') }}" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-medium rounded-lg border border-transparent text-blue-600 hover:bg-blue-50">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-start"><span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Dokumen</span></th>
                    <th scope="col" class="px-6 py-3 text-start"><span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Pasien</span></th>
                    <th scope="col" class="px-6 py-3 text-start"><span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Status</span></th>
                    <th scope="col" class="px-6 py-3 text-start"><span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Waktu Terbit</span></th>
                    <th scope="col" class="px-6 py-3 text-end"></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($persetujuans as $p)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <span class="block text-sm font-semibold text-gray-800">Informed Consent</span>
                            <span class="block text-xs text-gray-500 mt-0.5">Oleh: {{ $p->petugas->nama ?? 'Admin' }}</span>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <span class="block text-sm font-semibold text-gray-800">{{ $p->pasien->nama }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">NIK: {{ $p->pasien->nik ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            @if($p->status === 'menunggu_tanda_tangan')
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <span class="size-1.5 inline-block bg-yellow-500 rounded-full"></span>
                                    Menunggu
                                </span>
                            @elseif($p->status === 'ditandatangani')
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                    Ditandatangani
                                </span>
                            @elseif($p->status === 'kedaluwarsa')
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">
                                    Kedaluwarsa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                    Dibatalkan
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3 text-sm text-gray-800">
                            {{ $p->created_at->translatedFormat('d M Y, H:i') }}
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap text-end">
                        <div class="px-6 py-1.5">
                            <a href="{{ route('admin.persetujuan.show', $p->id) }}" class="inline-flex items-center gap-x-1 text-sm text-blue-600 decoration-2 hover:underline font-medium">
                                Detail
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="size-10 text-gray-300 mb-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                            <p class="text-sm">Belum ada arsip Informed Consent.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($persetujuans->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-white">
        {{ $persetujuans->links() }}
    </div>
    @endif
</div>
@endsection
