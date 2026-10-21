@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Petugas</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola data dokter, perawat, dan staf admin klinik.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.petugas.create') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none transition-colors shadow-sm">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Tambah Petugas
        </a>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <!-- Header & Search -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <form action="{{ route('admin.petugas.index') }}" method="GET" class="flex items-center gap-2 max-w-md">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                    <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input class="py-2 ps-10 pe-3 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIK, NIP, atau Nama...">
            </div>
            <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
                Cari
            </button>
            @if(request('q'))
                <a href="{{ route('admin.petugas.index') }}" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-medium rounded-lg border border-transparent text-blue-600 hover:bg-blue-50">
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
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Nama Lengkap</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Identitas (NIK/NIP)</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Jabatan & Peran</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Kontak</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-end"></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($petugas as $p)
                <tr class="hover:bg-gray-50 transition-colors {{ $p->is_active ? '' : 'bg-gray-100 opacity-60' }}">
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <div class="flex items-center gap-x-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $p->is_active ? 'bg-blue-100 text-blue-600' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($p->nama, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="block text-sm font-semibold {{ $p->is_active ? 'text-gray-800' : 'text-gray-500' }}">{{ $p->nama }}</span>
                                    <span class="block text-xs text-gray-500">{{ \Carbon\Carbon::parse($p->tanggal_lahir)->translatedFormat('d F Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <span class="block text-sm {{ $p->is_active ? 'text-gray-800' : 'text-gray-500' }}">NIK: {{ $p->nik ?? '-' }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">NIP: {{ $p->nip ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-md text-xs font-medium {{ $p->is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-600' }}">
                                {{ $p->jabatan }}
                            </span>
                            <span class="block text-xs text-gray-500 mt-1 capitalize">{{ $p->peran }} System</span>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3 text-sm text-gray-800">
                            {{ $p->no_hp ?? '-' }}
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap text-end">
                        <div class="px-6 py-1.5 flex justify-end gap-x-3 items-center">
                            <a href="{{ route('admin.petugas.edit', $p->id) }}" class="inline-flex items-center gap-x-1 text-sm text-blue-600 decoration-2 hover:underline font-medium">
                                Edit
                            </a>

                            <div x-data="{ open: false }" class="inline-block">
                                <button @click="open = true" type="button" class="inline-flex items-center gap-x-1 text-sm {{ $p->is_active ? 'text-amber-600' : 'text-emerald-600' }} decoration-2 hover:underline font-medium">
                                    {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>

                                <template x-teleport="body">
                                    <div x-show="open" style="display: none;" class="fixed inset-0 z-[80] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                        <!-- Backdrop -->
                                        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

                                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                            <!-- Modal Panel -->
                                            <div x-show="open" 
                                                x-transition:enter="ease-out duration-300" 
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                                x-transition:leave="ease-in duration-200" 
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                                @click.away="open = false"
                                                class="relative inline-block w-full max-w-lg p-4 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl sm:p-7 rounded-xl sm:my-8 sm:align-middle">
                                                
                                                <div class="text-center">
                                                    @if($p->is_active)
                                                    <svg class="size-14 mx-auto text-amber-500 mb-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <h3 class="text-lg font-bold text-gray-800" id="modal-title">Nonaktifkan Petugas?</h3>
                                                    <p class="text-sm text-gray-500 mt-2">Apakah Anda yakin ingin menonaktifkan petugas <strong>{{ $p->nama }}</strong>? Petugas tidak akan bisa login lagi ke sistem.</p>
                                                    @else
                                                    <svg class="size-14 mx-auto text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <h3 class="text-lg font-bold text-gray-800" id="modal-title">Aktifkan Petugas?</h3>
                                                    <p class="text-sm text-gray-500 mt-2">Apakah Anda yakin ingin mengaktifkan kembali petugas <strong>{{ $p->nama }}</strong>? Petugas akan bisa login lagi ke sistem.</p>
                                                    @endif
                                                </div>

                                                <div class="mt-6 flex justify-center gap-x-3">
                                                    <button @click="open = false" type="button" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                                                        Batal
                                                    </button>
                                                    <form action="{{ route('admin.petugas.toggle-status', $p->id) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent {{ $p->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white">
                                                            Ya, {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="size-10 text-gray-300 mb-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <p class="text-sm">Tidak ada data petugas yang ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($petugas->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-white">
        {{ $petugas->links() }}
    </div>
    @endif
</div>
@endsection
