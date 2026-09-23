@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Data Pasien</h1>
        <p class="mt-2 text-lg text-gray-600">Kelola dan lihat daftar pasien beserta riwayat pemeriksaannya.</p>
    </div>
</header>

<!-- Filter/Search Box -->
<div x-data="pasienSearch()" class="flex flex-col gap-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form @submit.prevent="performSearch" class="flex items-center gap-3">
        <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <input type="text" x-model="query" @input.debounce.500ms="performSearch" class="py-2.5 ps-10 pe-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Ketik Nama, NIK, No RM, atau No BPJS...">
        </div>
        <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 min-w-[80px] justify-center">
            <span>Cari</span>
        </button>
            <button type="button" @click="resetSearch" x-show="query !== ''" style="display: none;" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50">
                Reset
            </button>
        </form>
    </div>

    <!-- Table Section -->
    <div class="flex flex-col relative" id="pasien-table-container">
        
        <!-- Loading Overlay di Tengah Table -->
        <div x-show="isLoading" style="display: none;" class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-sm rounded-xl">
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

        <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">NIK</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">No RM</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">No BPJS</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Lahir / Usia</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                            <th scope="col" class="px-6 py-3 text-end text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pasiens as $pasien)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-800">{{ $pasien->nama }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">{{ $pasien->nik }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">{{ $pasien->no_rm ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">{{ $pasien->no_bpjs ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">
                                    {{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('d-m-Y') : '-' }}
                                    @if($pasien->tanggal_lahir)
                                        <span class="text-xs text-gray-400 ms-1">({{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->age }} th)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">
                                    @if($pasien->jenis_kelamin == 'L') Laki-laki @elseif($pasien->jenis_kelamin == 'P') Perempuan @else - @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                <a href="{{ route('admin.pasien.show', $pasien->id) }}" class="inline-flex items-center gap-x-1.5 text-sm text-blue-600 decoration-2 hover:underline font-medium">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                @if(empty($search))
                                    <div class="inline-flex flex-col items-center justify-center text-gray-500">
                                        <svg class="size-10 text-gray-400 mb-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        <p class="text-base font-medium text-gray-800">Mulai Pencarian</p>
                                        <p class="text-sm mt-1">Ketik Nama, NIK, No RM, atau No BPJS di atas untuk menampilkan data.</p>
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm">Tidak ada data pasien ditemukan untuk pencarian "<strong>{{ $search }}</strong>".</p>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- Pagination -->
                @if($pasiens->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $pasiens->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pasienSearch', () => ({
        query: '{{ $search ?? '' }}',
        isLoading: false,
        loadingText: 'Mencari data...',
        
        init() {
            // Watch dihapus karena kita sudah pakai @input.debounce di elemen input
        },
        
        async performSearch(isReset = false) {
            this.loadingText = isReset === true ? 'Mereset data...' : 'Mencari data...';
            this.isLoading = true;
            
            try {
                // Fetch the same page with the new query
                const response = await fetch(`{{ route('admin.pasien.index') }}?search=${encodeURIComponent(this.query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const html = await response.text();
                
                // Parse the returned HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Extract just the table HTML and replace it
                const newTable = doc.querySelector('#pasien-table-container').innerHTML;
                document.querySelector('#pasien-table-container').innerHTML = newTable;
                
                // Update the URL silently so if user refreshes, they stay on the search
                window.history.pushState({}, '', `{{ route('admin.pasien.index') }}?search=${encodeURIComponent(this.query)}`);
            } catch (error) {
                console.error('Gagal mencari data:', error);
            } finally {
                // Beri sedikit delay agar animasinya terlihat halus
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
