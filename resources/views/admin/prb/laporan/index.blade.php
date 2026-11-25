@extends('layouts.app')

@section('content')
<div class="space-y-5" x-data="laporanIndexApp({{ json_encode($daftarLaporan) }})">
    <!-- Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.prb.index') }}" class="hover:text-blue-600 transition-colors">Program Rujuk Balik</a>
                <span>/</span>
                <span class="text-gray-800 font-semibold">Laporan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Puskesmas Kalijudan</h1>
        </div>
    </div>

    <!-- Card Daftar Laporan (ala SIMPUS) -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
        <!-- Header Card -->
        <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-200 flex items-center gap-2.5">
            <svg class="size-5 text-gray-700 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            <h2 class="text-base font-bold text-gray-800">Laporan</h2>
        </div>

        <!-- Toolbar Tampilkan & Pencarian -->
        <div class="p-4 sm:px-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-gray-700">
                <span>Tampilkan</span>
                <select x-model.number="perPage" class="py-1.5 px-2.5 border border-gray-300 rounded-md text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>data per halaman</span>
            </div>

            <div class="flex items-center gap-2 text-xs text-gray-700">
                <label for="search_laporan" class="font-medium">Pencarian:</label>
                <input type="text" id="search_laporan" x-model="search"
                       placeholder="Cari nama laporan..."
                       class="py-1.5 px-3 w-full sm:w-64 border border-gray-300 rounded-md text-xs text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
            </div>
        </div>

        <!-- Tabel Daftar Laporan -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-xs font-bold text-gray-700">
                        <th class="py-3 px-5 w-16">No</th>
                        <th class="py-3 px-4">Nama</th>
                        <th class="py-3 px-5 w-20 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    <template x-for="(item, idx) in paginatedItems" :key="item.no">
                        <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer group" @click="window.location.href = item.route">
                            <td class="py-3.5 px-5 text-xs font-semibold text-gray-600 align-middle" x-text="idx + 1"></td>
                            <td class="py-3.5 px-4 align-middle">
                                <a :href="item.route" class="text-sm font-medium text-gray-800 group-hover:text-blue-700 transition-colors block" x-text="item.nama"></a>
                                <p class="text-[11px] text-gray-500 mt-0.5" x-text="item.keterangan"></p>
                            </td>
                            <td class="py-3.5 px-5 text-right align-middle">
                                <a :href="item.route"
                                   class="inline-flex items-center justify-center text-sky-600 hover:text-sky-800 transition-transform group-hover:translate-x-0.5"
                                   :title="'Buka ' + item.nama">
                                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 15l3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredItems.length === 0">
                        <tr>
                            <td colspan="3" class="py-8 text-center text-xs text-gray-500 italic">
                                Tidak ada laporan yang cocok dengan pencarian "<span x-text="search"></span>".
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Footer Info -->
        <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-200 flex items-center justify-between text-xs text-gray-600">
            <div>
                <template x-if="filteredItems.length > 0">
                    <span>Menampilkan 1 s/d <span x-text="paginatedItems.length"></span> dari <span x-text="filteredItems.length"></span> data</span>
                </template>
                <template x-if="filteredItems.length === 0">
                    <span>Menampilkan 0 data</span>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function laporanIndexApp(items) {
        return {
            items: items || [],
            search: '',
            perPage: 10,

            get filteredItems() {
                const q = (this.search || '').trim().toLowerCase();
                if (!q) return this.items;
                return this.items.filter(item =>
                    (item.nama || '').toLowerCase().includes(q) ||
                    (item.keterangan || '').toLowerCase().includes(q)
                );
            },

            get paginatedItems() {
                return this.filteredItems.slice(0, this.perPage);
            }
        };
    }
</script>
@endsection
