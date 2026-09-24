@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Evaluasi Kehadiran Pasien</h1>
        <p class="text-sm text-gray-500 mt-1">Pantau tingkat kepatuhan dan rutinitas kehadiran pasien Prolanis.</p>
    </div>
</div>

<div x-data="evaluasiSearch()" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <!-- Header & Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Tab Filters -->
        <div class="flex flex-wrap sm:flex-nowrap bg-gray-100 rounded-lg p-1 gap-1 overflow-x-auto">
            <a href="{{ route('admin.evaluasi.index') }}" class="whitespace-nowrap py-2 px-4 rounded-md text-sm font-bold {{ empty($statusFilter) ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                Semua Pasien
            </a>
            <a href="{{ route('admin.evaluasi.index', ['status' => 'Rutin']) }}" class="whitespace-nowrap py-2 px-4 rounded-md text-sm font-bold {{ $statusFilter == 'Rutin' ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                Rutin
            </a>
            <a href="{{ route('admin.evaluasi.index', ['status' => 'Jarang']) }}" class="whitespace-nowrap py-2 px-4 rounded-md text-sm font-bold {{ $statusFilter == 'Jarang' ? 'bg-yellow-500 text-white shadow-md' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                Jarang Hadir
            </a>
            <a href="{{ route('admin.evaluasi.index', ['status' => 'Pasif']) }}" class="whitespace-nowrap py-2 px-4 rounded-md text-sm font-bold {{ $statusFilter == 'Pasif' ? 'bg-red-600 text-white shadow-md' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200' }}">
                Pasif
            </a>
        </div>

        <!-- Search -->
        <div class="w-full lg:w-auto">
            <form @submit.prevent="performSearch" action="{{ route('admin.evaluasi.index') }}" method="GET" class="flex items-center gap-2">
                @if($statusFilter)
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <div class="relative flex-1 lg:w-64">
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                        <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input x-model="query" @input.debounce.500ms="performSearch" class="py-2 ps-10 pe-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" type="text" name="search" placeholder="Cari nama atau No HP...">
                </div>
                <button type="submit" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                    Cari
                </button>
                <button type="button" @click="resetSearch" x-show="query !== ''" style="display: none;" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50">
                    Reset
                </button>
            </form>
        </div>
    </div>

    <!-- Table Container -->
    <div id="evaluasi-table-container" class="relative">
        <!-- Loading Overlay -->
        <div x-show="isLoading" style="display: none;" class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-sm">
            <div class="inline-flex flex-col items-center gap-3 bg-white p-4 rounded-xl shadow-lg border border-gray-100">
                <div class="inline-flex" role="status" aria-label="loading">
                  <span class="flex h-6 items-center justify-center gap-1" aria-hidden="true">
                    <span class="h-4 w-1.5 rounded-sm bg-gray-300 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0s;"></span>
                    <span class="h-4 w-1.5 rounded-sm bg-gray-400 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.12s;"></span>
                    <span class="h-4 w-1.5 rounded-sm bg-gray-500 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.24s;"></span>
                  </span>
                  <span class="sr-only">Loading...</span>
                </div>
                <span class="text-sm font-medium text-blue-800" x-text="loadingText"></span>
            </div>
        </div>

        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Data Pasien</th>
                    <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Kunjungan Terakhir</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Kepatuhan</th>
                    <th scope="col" class="px-6 py-3 text-end text-xs font-semibold text-gray-500 uppercase tracking-wider">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($pasiens as $pasien)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900">{{ $pasien->nama }}</span>
                            <span class="text-xs text-gray-500">{{ $pasien->no_rm ?? '-' }} &bull; {{ $pasien->tipe_pasien ?? 'Prolanis' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($pasien->tanggal_kunjungan_terakhir)
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">{{ $pasien->tanggal_kunjungan_terakhir->translatedFormat('d F Y') }}</span>
                                <span class="text-xs text-gray-500">{{ $pasien->hari_terlewat }} hari yang lalu</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-500 italic">Belum ada riwayat</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-md text-xs font-bold border {{ $pasien->color_class }}">
                            @if($pasien->status_kehadiran == 'Rutin')
                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            @elseif($pasien->status_kehadiran == 'Jarang')
                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            @else
                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            @endif
                            {{ strtoupper($pasien->status_kehadiran) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                        @if($pasien->no_hp)
                            @php
                                // Format nomor HP ke format internasional WhatsApp (62...)
                                $waNumber = $pasien->no_hp;
                                if(substr($waNumber, 0, 1) == '0') {
                                    $waNumber = '62' . substr($waNumber, 1);
                                }
                                
                                $jadwalAktif = \App\Models\JadwalLayanan::where('is_active', true)->first();
                                $tanggalJadwal = $jadwalAktif ? \Carbon\Carbon::parse($jadwalAktif->tanggal)->translatedFormat('d F Y') : 'waktu dekat';
                                
                                $pesan = "Halo Bpk/Ibu {$pasien->nama}, ini dari Puskesmas Kalijudan. Kami mengingatkan bahwa sudah waktunya untuk jadwal kontrol Prolanis Anda pada {$tanggalJadwal} di Puskesmas Kalijudan. Mari jaga kesehatan bersama!";
                            @endphp
                            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($pesan) }}" target="_blank" class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-emerald-600 hover:text-emerald-800 disabled:opacity-50 disabled:pointer-events-none">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                Hubungi WA
                            </a>
                        @else
                            <span class="text-xs text-gray-400 italic">No HP tidak tersedia</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="size-10 text-gray-300 mb-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            <p class="text-sm">Tidak ada data pasien yang ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($pasiens->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-white">
        {{ $pasiens->links() }}
    </div>
    @endif
    </div> <!-- End Table Container -->
</div>
@endsection

@push('scripts')
<style>
@keyframes spinner-bars {
  0% { transform: scaleY(0.5); }
  50% { transform: scaleY(1); }
  100% { transform: scaleY(0.5); }
}
</style>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('evaluasiSearch', () => ({
        query: '{{ $search ?? '' }}',
        status: '{{ $statusFilter ?? '' }}',
        isLoading: false,
        loadingText: 'Mencari data...',
        
        async performSearch(isReset = false) {
            this.loadingText = isReset === true ? 'Mereset data...' : 'Mencari data...';
            this.isLoading = true;
            
            try {
                let url = `{{ route('admin.evaluasi.index') }}?search=${encodeURIComponent(this.query)}`;
                if (this.status) {
                    url += `&status=${encodeURIComponent(this.status)}`;
                }

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newTable = doc.querySelector('#evaluasi-table-container').innerHTML;
                document.querySelector('#evaluasi-table-container').innerHTML = newTable;
                
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
            this.performSearch(true);
        }
    }))
})
</script>
@endpush
