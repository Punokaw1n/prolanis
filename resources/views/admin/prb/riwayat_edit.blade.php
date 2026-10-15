@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-x-4">
            <a href="{{ route('admin.prb.show', $prb->id) }}" class="inline-flex justify-center items-center size-10 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 transition-colors" title="Kembali ke Detail Pasien">
                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Edit Pengambilan Obat</h1>
                <p class="mt-1 text-sm text-gray-600">Pasien: <strong class="text-gray-800">{{ $prb->pasien->nama }}</strong> (NIK: {{ $prb->pasien->nik }})</p>
            </div>
        </div>
    </header>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 mb-6" role="alert">
            <span class="font-bold">Error:</span>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.prb.riwayat.update', $riwayat->id) }}" method="POST" x-data="riwayatForm()">
        @csrf
        @method('PUT')
        
        <!-- Informasi Tanggal -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">Informasi Waktu</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_jatuh_tempo" x-model="tanggalJatuhTempo" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-2">Kapan pasien seharusnya mengambil obat di siklus ini.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal Diambil</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="tanggal_diambil" x-model="tanggalDiambil" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Kapan pasien benar-benar mengambil. Kosongkan jika memang belum ambil.</p>
                </div>
            </div>
        </div>

        <!-- Daftar Obat Pasien (Pola Resep Farmasi / Kasir Modern) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 sm:p-6 mb-6">
            <div class="mb-5 border-b border-gray-100 pb-3">
                <h2 class="text-lg font-semibold text-gray-800">Daftar Obat Pasien</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pilih obat PRB atau Non-PRB SIMBOK dari kotak pencarian untuk ditambahkan ke resep pasien.</p>
            </div>

            <!-- Panel Input Pencarian & Tambah Obat -->
            <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-4 sm:p-5 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <span class="size-2 rounded-full bg-blue-600"></span>
                        Cari & Tambah Obat ke Resep
                    </label>
                    
                    <!-- Toggle Kategori (PRB vs SIMBOK) -->
                    <div class="inline-flex p-1 bg-white border border-slate-200 rounded-lg shadow-2xs self-start sm:self-auto">
                        <button type="button" 
                            @click="selectedKategori = 'prb'; isDropdownOpen = false; activeFilter = searchQuery; isSearching = false"
                            class="py-1.5 px-3.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5"
                            :class="selectedKategori === 'prb' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            :style="selectedKategori === 'prb' ? 'background-color: #0d9488 !important; color: #ffffff !important;' : ''">
                            <span class="size-2 rounded-full" :style="selectedKategori === 'prb' ? 'background-color: #ffffff !important;' : 'background-color: #0d9488 !important;'"></span>
                            Obat PRB (<span x-text="masterPrb.length"></span>)
                        </button>
                        <button type="button" 
                            @click="selectedKategori = 'non_prb'; isDropdownOpen = false; activeFilter = searchQuery; isSearching = false"
                            class="py-1.5 px-3.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5"
                            :class="selectedKategori === 'non_prb' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            :style="selectedKategori === 'non_prb' ? 'background-color: #4f46e5 !important; color: #ffffff !important;' : ''">
                            <span class="size-2 rounded-full" :style="selectedKategori === 'non_prb' ? 'background-color: #ffffff !important;' : 'background-color: #4f46e5 !important;'"></span>
                            Non-PRB SIMBOK (<span x-text="masterNonPrb.length"></span>)
                        </button>
                    </div>
                </div>

                <!-- Alert Duplicate / Notification (Preline UI Style) -->
                <div x-show="alertMessage" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    style="display: none;"
                    class="mb-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 flex items-center justify-between shadow-2xs"
                    role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="size-4 text-amber-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="text-xs font-semibold" x-text="alertMessage"></span>
                    </div>
                    <button type="button" @click="alertMessage = ''" class="text-amber-500 hover:text-amber-700 p-0.5 rounded-md hover:bg-amber-100 transition-colors">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Input Row with Autocomplete -->
                <div class="relative" @click.outside="isDropdownOpen = false">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="relative flex-1">
                            <!-- Search Icon -->
                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-10 ps-3.5 text-slate-400">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>

                            <!-- Input Field -->
                            <input type="text" 
                                x-ref="searchInput"
                                x-model="searchQuery"
                                @focus="isDropdownOpen = true"
                                @input="onSearchInput()"
                                @input.debounce.300ms="performSearch()"
                                @keydown.escape="isDropdownOpen = false"
                                @keydown.enter.prevent="tambahObat()"
                                class="py-2.5 ps-10 pe-10 block w-full border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-blue-500 shadow-xs placeholder-slate-400"
                                style="padding-left: 2.6rem !important; padding-right: 2.5rem !important;"
                                :placeholder="selectedKategori === 'prb' ? 'Ketik nama obat PRB (misal: Glikazid, Ryzodeg, dll)...' : 'Ketik nama obat SIMBOK (misal: Amlodipin, Metformin, dll)...'"
                                autocomplete="off">

                            <!-- Bouncing Bars Loading Spinner -->
                            <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none" x-show="isSearching" style="display: none;">
                                <span class="flex h-4 items-center justify-center gap-0.5" aria-hidden="true">
                                    <span class="h-3.5 w-1 rounded-sm bg-gray-300 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0s;"></span>
                                    <span class="h-3.5 w-1 rounded-sm bg-gray-400 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.12s;"></span>
                                    <span class="h-3.5 w-1 rounded-sm bg-blue-600 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.24s;"></span>
                                </span>
                            </div>

                            <!-- Clear button -->
                            <button type="button" x-show="!isSearching && searchQuery" @click="clearSearch()"
                                class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 hover:text-slate-600">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Button Tambah -->
                        <button type="button" 
                            @click="tambahObat()"
                            :disabled="!searchQuery.trim()"
                            class="py-2.5 px-4 inline-flex items-center justify-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none transition-colors shrink-0 shadow-xs">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            + Tambah ke Resep
                        </button>
                    </div>

                    <!-- Autocomplete Dropdown List -->
                    <div x-show="isDropdownOpen && (isSearching || searchQuery.trim().length > 0 || filteredOptions.length > 0)" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        style="display: none;"
                        class="absolute start-0 end-0 top-full mt-1.5 z-50 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden flex flex-col">
                        
                        <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium shrink-0">
                            <span class="flex items-center gap-1.5">
                                <span class="size-2 rounded-full" :class="selectedKategori === 'prb' ? 'bg-teal-500' : 'bg-indigo-500'"></span>
                                <span class="font-semibold text-slate-700" x-text="selectedKategori === 'prb' ? 'Katalog Master PRB' : 'Katalog Master SIMBOK'"></span>
                            </span>
                            <span x-show="isSearching" style="display: none;" class="inline-flex items-center text-blue-600 font-medium text-[11px]">
                                <span class="size-1.5 inline-block bg-blue-600 rounded-full animate-pulse me-1"></span>
                                Mencari data...
                            </span>
                            <span x-show="!isSearching" class="text-[11px] text-slate-400" x-text="`${filteredOptions.length} obat ditampilkan (Klik untuk memilih)`"></span>
                        </div>

                        <!-- Loading State inside Dropdown -->
                        <div x-show="isSearching" style="display: none;" class="py-6 flex flex-col items-center justify-center gap-2 text-xs text-slate-500">
                            <span class="flex h-5 items-center justify-center gap-0.5" aria-hidden="true">
                                <span class="h-3.5 w-1 rounded-sm bg-gray-300 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0s;"></span>
                                <span class="h-3.5 w-1 rounded-sm bg-gray-400 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.12s;"></span>
                                <span class="h-3.5 w-1 rounded-sm bg-blue-600 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.24s;"></span>
                            </span>
                            <span>Mencari obat di katalog...</span>
                        </div>

                        <!-- Options List -->
                        <div x-show="!isSearching" style="max-height: 220px; overflow-y: auto;" class="p-1.5 space-y-0.5">
                            <template x-for="item in filteredOptions" :key="item">
                                <button type="button" 
                                    @click="pilihDariDropdown(item)"
                                    class="w-full text-start px-3 py-2 text-sm text-slate-800 rounded-lg hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between transition-colors group">
                                    <span class="truncate pr-2 font-medium" x-text="item"></span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded shrink-0"
                                        :class="selectedKategori === 'prb' ? 'bg-teal-50 text-teal-700 group-hover:bg-teal-100' : 'bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100'">
                                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        Pilih
                                    </span>
                                </button>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-center">
                                <p class="text-xs text-slate-500">Tidak ada obat di katalog <span class="font-bold text-slate-700" x-text="selectedKategori === 'prb' ? 'PRB' : 'SIMBOK'"></span> yang cocok dengan pencarian.</p>
                                <button type="button" @click="tambahObat()" class="mt-1 text-xs text-blue-600 hover:underline font-semibold">
                                    Tambahkan "<span x-text="searchQuery"></span>" sebagai obat resep kustom
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Resep Obat Pasien -->
            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-2xs">
                <div class="px-4 py-3 bg-gray-50/80 border-b border-gray-200 flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                        <svg class="size-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Resep Obat Pasien (<span x-text="obatList.length"></span> Obat)
                    </h4>

                    <template x-if="obatList.length > 0">
                        <button type="button" @click="kosongkanObat" class="text-xs text-red-600 hover:text-red-700 font-semibold hover:underline">
                            Kosongkan Resep
                        </button>
                    </template>
                </div>

                <!-- Empty State -->
                <template x-if="obatList.length === 0">
                    <div class="text-center py-10 px-4">
                        <div class="size-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-3">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m8.5 8.5 7 7" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-800">Belum Ada Obat di Resep Ini</h4>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">Gunakan panel pencarian di atas untuk memilih dan menambahkan obat PRB atau Non-PRB SIMBOK pasien.</p>
                    </div>
                </template>

                <!-- Table of Selected Medicines -->
                <template x-if="obatList.length > 0">
                    <div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th scope="col" class="py-2.5 px-3 text-center w-12">#</th>
                                        <th scope="col" class="py-2.5 px-4 text-start">Nama Obat</th>
                                        <th scope="col" class="py-2.5 px-4 text-start w-56">Kategori</th>
                                        <th scope="col" class="py-2.5 px-3 text-center w-16">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <template x-for="(obat, index) in obatList" :key="obat.id">
                                        <tr class="hover:bg-gray-50/60 transition-colors">
                                            <!-- Nomor -->
                                            <td class="py-3 px-3 text-center">
                                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-gray-100 text-gray-700 font-semibold text-xs" x-text="index + 1"></span>
                                            </td>

                                            <!-- Nama Obat -->
                                            <td class="py-3 px-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="size-2 rounded-full" :class="obat.kategori === 'prb' ? 'bg-teal-500' : 'bg-indigo-500'"></span>
                                                    <span class="text-sm font-semibold text-gray-800" x-text="obat.nama"></span>
                                                </div>
                                                <!-- Hidden inputs for Laravel Form POST -->
                                                <input type="hidden" :name="`obat[${index}][nama]`" :value="obat.nama">
                                                <input type="hidden" :name="`obat[${index}][kategori]`" :value="obat.kategori">
                                            </td>

                                            <!-- Kategori Badge -->
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                                    :class="obat.kategori === 'prb' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200'"
                                                    :style="obat.kategori === 'prb' ? 'background-color: #f0fdfa !important; color: #0f766e !important; border-color: #99f6e4 !important;' : 'background-color: #eef2ff !important; color: #4338ca !important; border-color: #c7d2fe !important;'">
                                                    <span class="size-1.5 rounded-full" :style="obat.kategori === 'prb' ? 'background-color: #0d9488 !important;' : 'background-color: #4f46e5 !important;'"></span>
                                                    <span x-text="obat.kategori === 'prb' ? 'PRB' : 'Non-PRB SIMBOK'"></span>
                                                </span>
                                            </td>

                                            <!-- Aksi Hapus -->
                                            <td class="py-3 px-3 text-center">
                                                <button type="button" @click="hapusObat(index)" 
                                                    title="Hapus dari resep"
                                                    class="size-8 inline-flex justify-center items-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer Summary -->
                        <div class="px-4 py-3 bg-gray-50/50 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-gray-500 gap-1">
                            <div>
                                Total: <span class="font-bold text-gray-800" x-text="obatList.length"></span> obat dicatat
                                (<span class="font-semibold text-teal-700" x-text="obatList.filter(o => o.kategori === 'prb').length"></span> PRB, 
                                 <span class="font-semibold text-indigo-700" x-text="obatList.filter(o => o.kategori === 'non_prb').length"></span> SIMBOK)
                            </div>
                            <div class="text-[11px] text-gray-400">
                                Data obat akan tersimpan otomatis saat Anda menekan tombol "Simpan Perubahan".
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modal Konfirmasi Kosongkan Resep (Preline UI Style) -->
        <div x-show="showConfirmKosongkan" 
            style="display: none; position: fixed; inset: 0; z-index: 99999; background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);"
            class="fixed inset-0 z-[9999] overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="showConfirmKosongkan = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white border border-gray-200 rounded-xl shadow-xl max-w-sm w-full p-6 text-center">
                
                <div class="size-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <h3 class="text-base font-bold text-gray-800">Kosongkan Semua Obat?</h3>
                <p class="text-xs text-gray-500 mt-1">Seluruh obat yang ada pada daftar resep ini akan dihapus.</p>

                <div class="mt-5 flex items-center justify-center gap-2">
                    <button type="button" @click="showConfirmKosongkan = false"
                        class="py-2 px-3.5 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs">
                        Batal
                    </button>
                    <button type="button" @click="obatList = []; showConfirmKosongkan = false"
                        class="py-2 px-3.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-2xs">
                        Ya, Kosongkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Toast Notification (Preline UI Style) -->
        <div x-show="alertMessage"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            style="display: none; position: fixed; top: 1.25rem; right: 1.25rem; z-index: 99999; max-width: 24rem; width: 100%;"
            class="fixed top-5 end-5 z-[9999] max-w-sm w-full bg-white border border-amber-200 rounded-xl shadow-xl p-4"
            role="alert">
            <div class="flex items-start">
                <div class="shrink-0">
                    <svg class="size-5 text-amber-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ms-3 flex-1">
                    <h4 class="text-sm font-semibold text-gray-800">Perhatian</h4>
                    <p class="text-xs text-gray-600 mt-0.5" x-text="alertMessage"></p>
                </div>
                <button type="button" @click="alertMessage = ''" class="inline-flex shrink-0 justify-center items-center size-5 rounded-lg text-gray-400 hover:text-gray-600 ms-2">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function riwayatForm() {
    return {
        tanggalDiambil: '{{ $riwayat->tanggal_diambil ? $riwayat->tanggal_diambil->format('Y-m-d') : '' }}',
        tanggalJatuhTempo: '{{ $riwayat->tanggal_jatuh_tempo ? $riwayat->tanggal_jatuh_tempo->format('Y-m-d') : '' }}',
        masterPrb: {!! json_encode($masterObatPrb ?? []) !!},
        masterNonPrb: {!! json_encode($masterObatNonPrb ?? []) !!},
        obatList: {!! $riwayat->obatPrb->map(function($o) { 
            return [
                'id' => $o->id, 
                'nama' => $o->nama_obat, 
                'kategori' => $o->kategori ?? 'prb',
            ]; 
        })->toJson() !!},
        selectedKategori: 'prb',
        searchQuery: '',
        activeFilter: '',
        isSearching: false,
        isDropdownOpen: false,
        alertMessage: '',
        alertTimeout: null,
        showConfirmKosongkan: false,

        onSearchInput() {
            this.isSearching = true;
            this.isDropdownOpen = true;
        },

        performSearch() {
            this.activeFilter = this.searchQuery;
            this.isSearching = false;
        },

        clearSearch() {
            this.searchQuery = '';
            this.activeFilter = '';
            this.isSearching = false;
            this.isDropdownOpen = true;
            this.$refs.searchInput?.focus();
        },

        showAlert(msg) {
            this.alertMessage = msg;
            if (this.alertTimeout) clearTimeout(this.alertTimeout);
            this.alertTimeout = setTimeout(() => {
                this.alertMessage = '';
            }, 4000);
        },

        get filteredOptions() {
            const list = this.selectedKategori === 'prb' ? this.masterPrb : this.masterNonPrb;
            const q = (this.activeFilter || '').trim().toLowerCase();
            if (!q) return list.slice(0, 20);
            return list.filter(item => item.toLowerCase().includes(q)).slice(0, 30);
        },
        
        tambahObat(nama = null) {
            const obatNama = (nama || this.searchQuery).trim();
            if (!obatNama) return;

            // Check if already in list
            const exists = this.obatList.some(o => o.nama.toLowerCase() === obatNama.toLowerCase() && o.kategori === this.selectedKategori);
            if (exists) {
                this.showAlert(`Obat "${obatNama}" sudah ada di dalam resep ini.`);
                return;
            }

            this.obatList.push({
                id: Date.now() + Math.random(),
                nama: obatNama,
                kategori: this.selectedKategori
            });

            this.searchQuery = '';
            this.activeFilter = '';
            this.isSearching = false;
            this.isDropdownOpen = false;
            this.$refs.searchInput?.focus();
        },

        pilihDariDropdown(item) {
            this.tambahObat(item);
        },
        
        hapusObat(index) {
            this.obatList.splice(index, 1);
        },

        kosongkanObat() {
            this.showConfirmKosongkan = true;
        }
    }
}
</script>
@endpush
@endsection
