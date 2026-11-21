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

<div x-data="prbSearch()" class="flex flex-col gap-6">
    <!-- Search & Filter Box -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form @submit.prevent="performSearch" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                    <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input type="text" x-model="query" @input.debounce.500ms="performSearch" class="py-2.5 ps-10 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Cari Nama / NIK / No. BPJS Pasien...">
            </div>

            <!-- Clickable Filter Status Pills -->
            <div class="flex items-center gap-1.5 p-1 bg-gray-100 rounded-lg border border-gray-200 self-start sm:self-auto">
                <button type="button" 
                        @click="setStatus('semua')" 
                        :class="status === 'semua' ? 'bg-white text-blue-700 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                        class="py-1.5 px-3 rounded-md text-xs transition-all cursor-pointer">
                    Semua Status
                </button>
                <button type="button" 
                        @click="setStatus('aktif')" 
                        :class="status === 'aktif' ? 'bg-white text-emerald-700 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                        class="py-1.5 px-3 rounded-md text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    Aktif
                </button>
                <button type="button" 
                        @click="setStatus('tidak_aktif')" 
                        :class="status === 'tidak_aktif' ? 'bg-white text-red-700 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                        class="py-1.5 px-3 rounded-md text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="size-1.5 rounded-full bg-red-500"></span>
                    Tidak Aktif
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm cursor-pointer">
                    Cari
                </button>
                <button type="button" @click="resetSearch" x-show="query !== '' || (status !== 'semua' && status !== null)" style="display: none;" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 cursor-pointer">
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="flex flex-col relative" id="prb-table-container">
        <!-- Loading Overlay di Tengah Table -->
        <div x-show="isLoading" style="display: none;" class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-sm rounded-xl">
            <div class="inline-flex flex-col items-center gap-3 bg-white p-4 rounded-xl shadow-lg border border-gray-100">
                <div class="inline-flex" role="status" aria-label="loading">
                  <span class="flex h-6 items-center justify-center gap-1" aria-hidden="true">
                    <span class="h-4 w-1.5 rounded-sm bg-gray-300 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0s;"></span>
                    <span class="h-4 w-1.5 rounded-sm bg-gray-400 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.12s;"></span>
                    <span class="h-4 w-1.5 rounded-sm bg-gray-500 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.24s;"></span>
                  </span>
                </div>
                <span class="text-xs font-semibold text-gray-700" x-text="loadingText">Mencari data...</span>
            </div>
        </div>

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
                            <div class="text-xs text-gray-500 flex items-center gap-1.5 flex-wrap">
                                <span>NIK: {{ $prb->pasien->nik ?? '-' }}</span>
                                @if(!empty($prb->pasien->no_bpjs))
                                    <span class="text-gray-300">&bull;</span>
                                    <span class="text-blue-600 font-medium">BPJS: {{ $prb->pasien->no_bpjs }}</span>
                                @endif
                            </div>
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
                            <div class="flex items-center justify-end gap-3" x-data="{ modalDeleteOpen: false }">
                                <a href="{{ route('admin.prb.show', $prb->id) }}" class="inline-flex items-center gap-x-1.5 text-sm text-blue-600 decoration-2 hover:underline font-medium">
                                    Detail Riwayat
                                </a>

                                @if(auth()->user()?->isAdmin())
                                    <button type="button" @click="modalDeleteOpen = true" class="p-1 text-gray-400 hover:text-red-600 transition-colors cursor-pointer" title="Hapus Pasien PRB">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>

                                    <!-- Delete Modal (Alpine.js) -->
                                    <template x-teleport="body">
                                        <div x-show="modalDeleteOpen" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title-{{ $prb->id }}" role="dialog" aria-modal="true" style="display: none; z-index: 9999;">
                                            <div class="fixed inset-0 transition-opacity" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);" @click="modalDeleteOpen = false"></div>
                                            
                                            <div class="fixed" style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 32rem; padding: 1rem; z-index: 10000;">
                                                <div x-show="modalDeleteOpen" 
                                                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                                     class="w-full text-left overflow-hidden shadow-xl transform transition-all" style="background-color: #ffffff; border-radius: 0.75rem;">
                                                    
                                                    <div class="p-6 sm:p-10 text-center">
                                                        <span class="mb-4 inline-flex justify-center items-center size-[62px] rounded-full border-4" style="border-color: #fef2f2; background-color: #fee2e2; color: #ef4444;">
                                                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                                            </svg>
                                                        </span>
                                                        <h3 class="mb-2 text-xl font-bold" id="modal-title-{{ $prb->id }}" style="color: #1f2937;">
                                                            Hapus Data Pasien PRB?
                                                        </h3>
                                                        <p class="text-sm whitespace-normal" style="color: #6b7280;">
                                                            Anda akan menghapus data pasien PRB <strong>{{ $prb->pasien?->nama }}</strong> (NIK: {{ $prb->pasien?->nik ?? '-' }}). Seluruh riwayat pengambilan obat PRB terkait juga akan terhapus. Tindakan ini tidak dapat dibatalkan.
                                                        </p>
                                                        
                                                        <div class="mt-6 flex justify-center gap-x-4">
                                                            <button type="button" @click="modalDeleteOpen = false" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border shadow-sm transition-colors cursor-pointer" style="background-color: #ffffff; border-color: #e5e7eb; color: #1f2937;">
                                                                Batal
                                                            </button>
                                                            <form action="{{ route('admin.prb.destroy', $prb->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent transition-colors cursor-pointer" style="background-color: #dc2626; color: #ffffff;">
                                                                    Ya, Hapus Pasien
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                @endif
                            </div>
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
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('prbSearch', () => ({
        query: '{{ $search ?? '' }}',
        status: {!! json_encode($status ?? 'semua') !!},
        isLoading: false,
        loadingText: 'Mencari data...',

        init() {
        },

        setStatus(newStatus) {
            this.status = newStatus;
            this.performSearch();
        },

        async performSearch(isReset = false) {
            this.loadingText = isReset === true ? 'Mereset data...' : 'Memuat data...';
            this.isLoading = true;

            try {
                const params = new URLSearchParams();
                if (this.query) params.append('search', this.query);
                if (this.status && this.status !== 'semua') params.append('status', this.status);

                const url = `{{ route('admin.prb.index') }}${params.toString() ? '?' + params.toString() : ''}`;
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const html = await response.text();

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newTable = doc.querySelector('#prb-table-container').innerHTML;
                const container = document.querySelector('#prb-table-container');
                container.innerHTML = newTable;

                if (window.Alpine) {
                    window.Alpine.initTree(container);
                }

                window.history.pushState({}, '', url);
            } catch (error) {
                console.error('Gagal mencari data:', error);
            } finally {
                setTimeout(() => {
                    this.isLoading = false;
                }, 300);
            }
        },

        resetSearch() {
            this.query = '';
            this.status = 'semua';
            this.performSearch(true);
        }
    }))
})
</script>
@endpush
