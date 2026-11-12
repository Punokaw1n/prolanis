@extends('layouts.app')

@php
    $currentUser = auth()->user();
    $isAdmin = $currentUser?->isAdmin() ?? false;
    if ($isAdmin && !$currentUser?->isDokter() && !empty($daftarDokterPemeriksa)) {
        $defaultDokterNama = $daftarDokterPemeriksa[0]['nama'];
        $defaultDokterSip = $daftarDokterPemeriksa[0]['sip'];
        $defaultDokterId = $daftarDokterPemeriksa[0]['id'];
    } else {
        $defaultDokterNama = $currentUser?->nama_gelar ?? 'Dokter Pemeriksa';
        $defaultDokterSip = $currentUser?->nip ? 'SIP. ' . $currentUser->nip : 'SIP. -';
        $defaultDokterId = $currentUser?->id ?? null;
    }
@endphp

@push('styles')
<style>
@media print {
    body, html {
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        height: auto !important;
        overflow: visible !important;
    }
    .h-screen, .overflow-hidden {
        height: auto !important;
        overflow: visible !important;
    }
    .lg\:ms-56 {
        margin-left: 0 !important;
    }
    /* Sembunyikan semua navigasi & elemen web */
    #sidebar, header, nav, .no-print, aside, div[role="dialog"], [role="alert"] {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
        display: block !important;
        background: #ffffff !important;
    }
    /* Tampilkan hanya lembar resep */
    .print-only {
        display: block !important;
        position: relative !important;
        width: 100mm !important;
        max-width: 100mm !important;
        padding: 4mm 6mm !important;
        margin: 0 auto !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-shadow: none !important;
        border: none !important;
        font-family: 'Times New Roman', Times, serif !important;
    }
    @page {
        size: 110mm 160mm;
        margin: 5mm 6mm;
    }
}
.print-only {
    display: none;
}
</style>
@endpush

@section('content')
<div class="max-w-[1400px] mx-auto" x-data="riwayatForm()">

    <!-- Top Navigation / Badges Bar (ala Gambar 1) -->
    <header class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 no-print">
        <div class="flex items-center gap-x-3 flex-wrap">
            <a href="{{ route('admin.prb.show', $prb->id) }}" class="inline-flex justify-center items-center size-9 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 transition-colors" title="Kembali ke Detail Pasien">
                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            
            <!-- Badges Kategori Pasien & Dokter (ala Gambar 1) -->
            <div class="inline-flex items-center gap-1.5 flex-wrap">
                <span class="inline-flex items-center gap-1 py-1 px-3 rounded-full text-xs font-bold bg-cyan-500 text-white shadow-2xs tracking-wide">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    LANSIA / PRB
                </span>
                <span class="inline-flex items-center gap-1 py-1 px-3 rounded-full text-xs font-semibold bg-rose-600 text-white shadow-2xs">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span x-text="cetakDokter || @js($defaultDokterNama)"></span>
                </span>
                <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    {{ $prb->status_keaktifan == 'aktif' ? 'Status: Aktif PRB' : 'Status: Tidak Aktif' }}
                </span>
            </div>
        </div>

    
    </header>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-xl p-4 mb-5 no-print" role="alert">
            <span class="font-bold">Periksa Kembali Input:</span>
            <ul class="list-disc list-inside mt-1.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formResepPrb" action="{{ route('admin.prb.riwayat.store', $prb->id) }}" method="POST" class="no-print" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true">
        @csrf

        <!-- Layout 2 Kolom (Area Resep di Kiri & Panel Action di Kanan) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ================= KOLOM KIRI: RESEP UTAMA (75%) ================= -->
            <div class="lg:col-span-8 xl:col-span-9 space-y-5">
                
                <!-- Main Resep Card (ala Gambar 1: Resep Internal) -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden resep-card">
                    
                    <!-- Resep Card Header -->
                    <div class="px-5 py-3.5 bg-gradient-to-r from-slate-50 to-white border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="size-8 rounded-lg bg-teal-600 text-white flex items-center justify-center shadow-2xs shrink-0">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                    Resep Internal
                                    <span class="text-[11px] font-semibold text-teal-700 bg-teal-50 border border-teal-200/80 px-2 py-0.2 rounded-full">PRB & SIMBOK</span>
                                </h2>
                                <p class="text-[11px] text-gray-500">Catatan pelayanan obat rutin bulanan pasien Prolanis / PRB</p>
                            </div>
                        </div>

                    </div>

                    <!-- Metadata Grid Pasien & Resep (Format Tabel Ringkas ala Gambar 1) -->
                    <div class="p-4 sm:p-5 bg-slate-50/60 border-b border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-2.5 gap-x-6 text-xs text-gray-600">
                            <div class="flex items-center">
                                <span class="w-24 text-gray-400 font-medium">No. RM / Nota</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <strong class="text-gray-800 font-mono">{{ $prb->pasien->no_rm ?? 'RM-'.$prb->pasien->nik }}</strong>
                            </div>
                            <div class="flex items-center">
                                <span class="w-24 text-gray-400 font-medium">Tanggal Kunj.</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <span class="font-semibold text-gray-800" x-text="tanggalDiambil || '-'"></span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-24 text-gray-400 font-medium">Status</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/80">Ambil Obat Rutin</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-24 text-gray-400 font-medium">Nama Pasien</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <strong class="text-gray-900 font-bold uppercase">{{ $prb->pasien->nama }}</strong>
                            </div>
                            <div class="flex items-center">
                                <span class="w-24 text-gray-400 font-medium">Total Obat</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <span class="font-bold text-blue-700"><span x-text="obatList.length">0</span> Item</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-24 text-gray-400 font-medium">NIK Pasien</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <span class="font-mono text-gray-700">{{ $prb->pasien->nik }}</span>
                            </div>
                            <div class="flex items-center sm:col-span-2">
                                <span class="w-24 text-gray-400 font-medium">Keterangan</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <span class="text-gray-700">Resep Obat Kronis PRB (30 Hari)</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-24 text-gray-400 font-medium">Jatuh Tempo</span>
                                <span class="text-gray-400 mr-2">:</span>
                                <span class="font-semibold text-indigo-700" x-text="tanggalJatuhTempo || '-'"></span>
                            </div>
                        </div>

                        <!-- ================= BAR PENGATURAN PROGRAM PRB & JADWAL KUNJUNGAN ================= -->
                        <div class="mt-4 pt-4 border-t border-slate-200/80 space-y-4 no-print">
                            
                            <!-- 1. Pemilihan Program Diagnosa / Penyakit PRB -->
                            <div class="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="size-6 rounded-md bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </span>
                                        <div>
                                            <span class="text-xs font-bold text-gray-800 uppercase tracking-wide">Program Diagnosa PRB Kunjungan Ini</span>
                                            <span class="text-red-500 font-bold">*</span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-gray-500">Pilih program penyakit yang dilayani atau tambah diagnosa baru jika ada penyakit lain</span>
                                </div>

                                <!-- Segmented Cards Pilihan Program Pasien -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                    @foreach($programPasien as $p)
                                        <div @click="pilihProgram('{{ $p['key'] }}')"
                                            class="relative p-2.5 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between gap-3 text-left group"
                                            :class="selectedProgramKey === '{{ $p['key'] }}' ? 'border-teal-600 bg-teal-50/80 shadow-2xs' : 'border-gray-200 hover:border-teal-300 bg-white'">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    @if(!empty($p['kode']))
                                                        <span class="font-mono font-bold text-[11px] px-1.5 py-0.2 rounded"
                                                            :class="selectedProgramKey === '{{ $p['key'] }}' ? 'bg-teal-700 text-white' : 'bg-teal-100 text-teal-800'">
                                                            {{ $p['kode'] }}
                                                        </span>
                                                    @endif
                                                    <strong class="text-xs font-bold text-gray-900 truncate">{{ $p['diagnosa'] }}</strong>
                                                </div>
                                                <p class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1">
                                                    <span class="size-1 rounded-full bg-gray-400"></span>
                                                    <span>{{ count($p['obat_list']) }} obat tersimpan</span>
                                                    @if($p['last_date'] !== '-')
                                                        <span>• Tgl: {{ $p['last_date'] }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="size-4 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                                :class="selectedProgramKey === '{{ $p['key'] }}' ? 'border-teal-600 bg-teal-600' : 'border-gray-300 group-hover:border-teal-400'">
                                                <span class="size-1.5 rounded-full bg-white" x-show="selectedProgramKey === '{{ $p['key'] }}'"></span>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Tombol Kartu: Tambah Diagnosa Baru / Penyakit Berbeda -->
                                    <div @click="pilihProgram('baru')"
                                        class="relative p-2.5 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between gap-3 text-left group"
                                        :class="selectedProgramKey === 'baru' ? 'border-teal-600 bg-teal-50/80 shadow-2xs' : 'border-dashed border-gray-300 hover:border-teal-400 bg-slate-50/60'">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 text-teal-700 font-bold text-xs">
                                                <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                <span>+ Diagnosa Baru / Lainnya</span>
                                            </div>
                                            <p class="text-[10px] text-gray-500 mt-0.5">Jika pasien datang dengan penyakit/rujukan baru</p>
                                        </div>
                                        <div class="size-4 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                            :class="selectedProgramKey === 'baru' ? 'border-teal-600 bg-teal-600' : 'border-gray-300 group-hover:border-teal-400'">
                                            <span class="size-1.5 rounded-full bg-white" x-show="selectedProgramKey === 'baru'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Input Hidden untuk POST Laravel Form -->
                                <input type="hidden" name="kode_diagnosa" :value="kodeDiagnosa">
                                <input type="hidden" name="diagnosa" :value="diagnosa">
                                <input type="hidden" name="dokter_id" :value="selectedDokterId">

                                <!-- Panel Input Terbuka Jika Memilih 'Diagnosa Baru' -->
                                <div x-show="selectedProgramKey === 'baru'" x-transition class="mt-3 pt-3 border-t border-teal-100">
                                    <div class="text-[11px] font-bold text-teal-800 mb-2 flex items-center gap-1.5">
                                        <svg class="size-3.5 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Input Data Diagnosa Baru Pasien:</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 mb-2.5">
                                        <div class="sm:col-span-4">
                                            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                                Kode ICD-10 <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="kodeDiagnosa" placeholder="Misal: I10, J45, E11" class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-mono font-bold uppercase text-gray-800 bg-white focus:border-teal-500 focus:ring-teal-500 shadow-2xs">
                                        </div>
                                        <div class="sm:col-span-8">
                                            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                                Nama Diagnosa / Penyakit <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="diagnosa" placeholder="Misal: Hipertensi, Asma Bronkial, Diabetes Melitus" class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-teal-500 focus:ring-teal-500 shadow-2xs">
                                        </div>
                                    </div>

                                    <!-- Tombol Preset Cepat Diagnosa Standar PRB BPJS -->
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[10px] font-semibold text-gray-500">Pilih Cepat Standar PRB:</span>
                                        @foreach($presetDiagnosaPrb as $preset)
                                            <button type="button" 
                                                @click="pilihPresetDiagnosa('{{ $preset['kode'] }}', '{{ $preset['nama'] }}')"
                                                class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 hover:bg-teal-100 text-slate-700 hover:text-teal-800 border border-slate-200 hover:border-teal-300 transition-colors inline-flex items-center gap-1">
                                                <span class="font-mono font-bold text-[10px]">{{ $preset['kode'] }}</span>
                                                <span>{{ $preset['nama'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Pengaturan Tanggal Pelayanan & Jadwal Jatuh Tempo (Layout 2 Kolom Luas & Seimbang) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Tanggal Pelayanan / Diambil</span>
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="tanggal_diambil" x-model="tanggalDiambil" @change="updateAllHari()" class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs" required>
                                    <p class="text-[11px] text-gray-500 mt-1.5">Tanggal pasien datang menerima obat di Puskesmas.</p>
                                </div>

                                <div class="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Jadwal Jatuh Tempo (Berikutnya)</span>
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="date" name="tanggal_jatuh_tempo" x-model="tanggalJatuhTempo" @change="updateAllHari()" class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs" required>
                                        <button type="button" @click="setSaranJatuhTempo" class="py-2.5 px-3.5 inline-flex items-center justify-center gap-1.5 text-xs font-bold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 shrink-0 transition-colors shadow-2xs whitespace-nowrap" title="Set otomatis ke +30 hari dari tanggal diambil">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                            <span>+30 Hari</span>
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-gray-500 mt-1.5">Siklus 30 hari untuk jadwal pengambilan obat bulan depan.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Panel Input & Pencarian Obat -->
                    <div class="p-4 sm:p-5 bg-white border-b border-gray-200 search-box-panel no-print">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-600">Ketik obat untuk ditambahkan ke tabel resep:</span>
                            </div>

                            <!-- Switcher Kategori PRB vs Non-PRB SIMBOK -->
                            <div class="inline-flex p-0.5 bg-gray-100 border border-gray-200 rounded-lg self-start sm:self-auto">
                                <button type="button" 
                                    @click="selectedKategori = 'prb'; isDropdownOpen = false; activeFilter = searchQuery; isSearching = false"
                                    class="py-1 px-3 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5"
                                    :class="selectedKategori === 'prb' ? 'bg-teal-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                                    :style="selectedKategori === 'prb' ? 'background-color: #0d9488 !important; color: #ffffff !important;' : ''">
                                    <span class="size-1.5 rounded-full" :style="selectedKategori === 'prb' ? 'background-color: #ffffff !important;' : 'background-color: #0d9488 !important;'"></span>
                                    Obat PRB (<span x-text="masterPrb.length"></span>)
                                </button>
                                <button type="button" 
                                    @click="selectedKategori = 'non_prb'; isDropdownOpen = false; activeFilter = searchQuery; isSearching = false"
                                    class="py-1 px-3 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5"
                                    :class="selectedKategori === 'non_prb' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                                    :style="selectedKategori === 'non_prb' ? 'background-color: #4f46e5 !important; color: #ffffff !important;' : ''">
                                    <span class="size-1.5 rounded-full" :style="selectedKategori === 'non_prb' ? 'background-color: #ffffff !important;' : 'background-color: #4f46e5 !important;'"></span>
                                    SIMBOK (<span x-text="masterNonPrb.length"></span>)
                                </button>
                            </div>
                        </div>

                        <!-- Autocomplete Box -->
                        <div class="relative" @click.outside="isDropdownOpen = false">
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3 text-gray-400">
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                    x-ref="searchInput"
                                    x-model="searchQuery"
                                    @focus="isDropdownOpen = true"
                                    @input="onSearchInput()"
                                    @input.debounce.300ms="performSearch()"
                                    @keydown.escape="isDropdownOpen = false"
                                    @keydown.enter.prevent="if (filteredOptions.length > 0) { pilihDariDropdown(filteredOptions[0]); } else { bukaModalObatBaru(searchQuery); }"
                                    class="py-2.5 ps-9 pe-9 block w-full border border-gray-300 rounded-lg text-xs font-medium bg-white focus:border-teal-500 focus:ring-teal-500 shadow-2xs"
                                    :placeholder="selectedKategori === 'prb' ? 'Ketik nama obat PRB (misal: Amlodipine, Metformin, Glikazid)...' : 'Ketik nama obat SIMBOK (misal: Antasida, Paracetamol, Vitamin B)...'"
                                    autocomplete="off">
                                
                                <!-- Spinner / Clear -->
                                <div class="absolute inset-y-0 end-0 flex items-center pe-2.5 pointer-events-none" x-show="isSearching" style="display: none;">
                                    <svg class="animate-spin size-4 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                                <button type="button" x-show="!isSearching && searchQuery" @click="clearSearch()" class="absolute inset-y-0 end-0 flex items-center pe-2.5 text-gray-400 hover:text-gray-600">
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <!-- Dropdown List Hasil Pencarian -->
                            <div x-show="isDropdownOpen && searchQuery.length >= 1" 
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                class="absolute start-0 end-0 z-30 mt-1 max-h-80 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-xl p-1"
                                style="display: none;">
                                <template x-if="filteredOptions.length > 0">
                                    <div>
                                        <template x-for="(item, idx) in filteredOptions" :key="idx">
                                            <button type="button" 
                                                @click="pilihDariDropdown(item)"
                                                class="w-full text-start px-3 py-2 text-xs rounded-lg hover:bg-gray-100 flex items-center justify-between group transition-colors">
                                                <span class="font-medium text-gray-800" x-text="item"></span>
                                                <span class="text-[10px] font-semibold text-gray-400 group-hover:text-teal-600 flex items-center gap-1">
                                                    + Masukkan
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="filteredOptions.length === 0">
                                    <div class="p-3 text-center">
                                        <p class="text-xs text-gray-500 mb-2">Obat "<span class="font-bold text-gray-800" x-text="searchQuery"></span>" tidak ditemukan di master.</p>
                                        <button type="button" 
                                            @click="bukaModalObatBaru(searchQuery)"
                                            class="py-1.5 px-3 text-xs font-bold rounded-lg bg-rose-600 text-white hover:bg-rose-700 shadow-2xs">
                                            + Tambahkan Sebagai Obat Baru
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- ================= TABEL OBAT (ALA GAMBAR 1) ================= -->
                    <div class="overflow-x-auto min-h-[360px] pb-32">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                            <thead class="bg-gray-50 text-gray-600 font-semibold tracking-wider uppercase text-[10.5px]">
                                <tr>
                                    <th scope="col" class="py-3 px-3 text-center w-10">No</th>
                                    <th scope="col" class="py-3 px-3 w-20">Kode</th>
                                    <th scope="col" class="py-3 px-4 min-w-[200px]">Nama Obat</th>
                                    <th scope="col" class="py-3 px-3 text-center min-w-[140px] w-40 whitespace-nowrap">Signa</th>
                                    <th scope="col" class="py-3 px-2 text-center w-16">Hari</th>
                                    <th scope="col" class="py-3 px-3 text-center w-24">Jumlah</th>
                                    {{-- <th scope="col" class="py-3 px-3 min-w-[190px] w-52 whitespace-nowrap">Waktu Minum</th> --}}
                                    <th scope="col" class="py-3 px-3 text-center w-12 no-print">#</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <!-- Empty State -->
                                <template x-if="obatList.length === 0">
                                    <tr>
                                        <td colspan="7" class="py-10 text-center text-gray-400">
                                            <svg class="size-8 text-gray-300 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            <p class="font-medium text-xs text-gray-600">Belum ada obat dalam resep ini.</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">Ketik di kotak pencarian atau klik "+ Tambah" di atas untuk menambahkan obat.</p>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Rows Daftar Obat -->
                                <template x-for="(obat, index) in obatList" :key="obat.id || index">
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-2.5 px-3 text-center text-gray-500 font-mono" x-text="index + 1 + '.'"></td>

                                        <!-- Kode (ala Gambar 1: (NON) / (PRB)) -->
                                        <td class="py-2.5 px-3">
                                            <span class="font-bold text-[10px] font-mono px-1.5 py-0.5 rounded"
                                                :class="obat.kategori === 'prb' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200'"
                                                x-text="obat.kategori === 'prb' ? '(PRB)' : '(NON)'"></span>
                                        </td>

                                        <!-- Nama Obat -->
                                        <td class="py-2.5 px-4 font-semibold text-gray-900">
                                            <span x-text="obat.nama"></span>
                                            <!-- Hidden Inputs for Form Submission -->
                                            <input type="hidden" :name="'obat[' + index + '][nama]'" :value="obat.nama">
                                            <input type="hidden" :name="'obat[' + index + '][kategori]'" :value="obat.kategori">
                                        </td>

                                        <!-- Signa (SIMPUS-style: signa1 x signa2) -->
                                        <td class="py-2.5 px-2 align-middle text-center">
                                            <div class="inline-flex items-center justify-center gap-1">
                                                <input type="number" step="any" min="0"
                                                    x-model="obat.signa1"
                                                    @input="hitungOtomatisJumlah(obat)"
                                                    class="py-1 px-1 text-center w-12 border border-gray-300 rounded-md text-xs font-mono font-bold text-gray-800 focus:border-blue-500 focus:ring-blue-500 bg-white shadow-2xs"
                                                    placeholder="1">
                                                <span class="text-xs font-bold text-gray-400 select-none">×</span>
                                                <input type="number" step="any" min="0"
                                                    x-model="obat.signa2"
                                                    @input="hitungOtomatisJumlah(obat)"
                                                    class="py-1 px-1 text-center w-12 border border-gray-300 rounded-md text-xs font-mono font-bold text-gray-800 focus:border-blue-500 focus:ring-blue-500 bg-white shadow-2xs"
                                                    placeholder="1">
                                            </div>
                                            <input type="hidden" :name="'obat[' + index + '][signa]'" :value="(obat.signa1 || 1) + ' x ' + (obat.signa2 || 1)">
                                        </td>

                                        <!-- Hari -->
                                        <td class="py-2.5 px-2 text-center align-middle">
                                            <input type="number" 
                                                :name="'obat[' + index + '][jumlah_hari]'" 
                                                x-model="obat.jumlah_hari" 
                                                @input="hitungOtomatisJumlah(obat)"
                                                min="1" max="90" 
                                                class="py-1 px-1 text-center w-14 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 bg-white focus:border-blue-500 shadow-2xs">
                                        </td>

                                        <!-- Qty (Jumlah Butir) -->
                                        <td class="py-2.5 px-3 text-center align-middle">
                                            <div class="inline-flex items-center gap-1">
                                                <input type="number" 
                                                    :name="'obat[' + index + '][jumlah_obat]'" 
                                                    x-model="obat.jumlah_obat" 
                                                    min="1" 
                                                    class="py-1 px-1.5 text-center w-16 border border-blue-300 rounded-md text-xs font-bold text-blue-700 bg-blue-50/50 focus:border-blue-500 shadow-2xs">
                                                <span class="text-[10px] text-gray-400">bt</span>
                                            </div>
                                        </td>

                                        {{-- <!-- Waktu Minum with Custom Styled Select Dropdown -->
                                        <td class="py-2.5 px-3 align-middle min-w-[170px]">
                                            <div class="relative" x-data="{ openAturan: false }" @click.outside="openAturan = false">
                                                <!-- Hidden input for Laravel Form POST -->
                                                <input type="hidden" :name="'obat[' + index + '][aturan_minum]'" :value="obat.aturan_minum">

                                                <!-- Trigger Button -->
                                                <button type="button" 
                                                    @click="openAturan = !openAturan" 
                                                    class="py-1 px-2.5 w-full bg-white border border-gray-300 hover:border-blue-400 rounded-md text-xs font-semibold flex items-center justify-between shadow-2xs transition-colors"
                                                    :class="obat.aturan_minum ? 'text-gray-800' : 'text-gray-400'">
                                                    <span class="truncate flex items-center gap-1.5">
                                                        <template x-if="obat.aturan_minum">
                                                            <span class="size-2 rounded-full shrink-0" :class="{
                                                                'bg-emerald-500': obat.aturan_minum === 'Sesudah Makan',
                                                                'bg-amber-500': obat.aturan_minum === 'Sebelum Makan',
                                                                'bg-blue-500': obat.aturan_minum === 'Bersama Makan',
                                                                'bg-purple-500': obat.aturan_minum === 'Malam Hari',
                                                                'bg-sky-500': obat.aturan_minum === 'Pagi Hari',
                                                                'bg-rose-500': obat.aturan_minum === 'Bila Perlu (PRN)'
                                                            }"></span>
                                                        </template>
                                                        <span x-text="obat.aturan_minum || 'Pilih waktu minum...'"></span>
                                                    </span>
                                                    <svg class="size-3.5 text-gray-400 transition-transform shrink-0 ms-1" :class="openAturan ? 'rotate-180 text-blue-600' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>

                                                <!-- Dropdown Menu Popover -->
                                                <div x-show="openAturan" 
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                                    style="display: none; position: absolute; top: 100%; left: 0; z-index: 60; width: 220px; max-height: 320px; overflow-y: auto;"
                                                    class="mt-1 bg-white border border-gray-200 rounded-xl shadow-xl p-1.5 space-y-0.5 text-xs">
                                                    <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 mb-1">
                                                        Pilih Waktu Minum
                                                    </div>
                                                    <template x-for="opt in aturanOptions" :key="opt.val">
                                                        <button type="button" 
                                                            @click="obat.aturan_minum = opt.val; openAturan = false"
                                                            class="w-full text-start px-2 py-1.5 rounded-lg flex items-center justify-between transition-colors group"
                                                            :class="obat.aturan_minum === opt.val ? 'bg-blue-50 text-blue-700 font-bold' : 'hover:bg-gray-50 text-gray-700 font-medium'">
                                                            <span class="flex items-center gap-2">
                                                                <span class="size-2 rounded-full shrink-0" :class="opt.dotClass"></span>
                                                                <span x-text="opt.val"></span>
                                                            </span>
                                                            <svg x-show="obat.aturan_minum === opt.val" class="size-3.5 text-blue-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                            </svg>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </td> --}}
                                        <!-- Aksi Hapus (ala Gambar 1: Tong Sampah Biru) -->
                                        <td class="py-2.5 px-3 text-center no-print">
                                            <button type="button" @click="hapusObat(index)" class="text-sky-600 hover:text-red-600 p-1 rounded-md hover:bg-red-50 transition-colors" title="Hapus obat">
                                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Ringkasan Resep (ala Gambar 1) -->
                    <div class="px-5 py-3.5 bg-gray-50/80 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-gray-600 gap-2">
                        <div>
                            Total: <strong class="text-gray-900" x-text="obatList.length">0</strong> obat dicatat
                            (<span class="font-semibold text-teal-700" x-text="obatList.filter(o => o.kategori === 'prb').length">0</span> PRB, 
                             <span class="font-semibold text-indigo-700" x-text="obatList.filter(o => o.kategori === 'non_prb').length">0</span> SIMBOK)
                            <span class="text-gray-300 mx-1.5">•</span>
                            Estimasi butir: <strong class="text-blue-700 font-bold" x-text="obatList.reduce((acc, o) => acc + (parseInt(o.jumlah_obat) || 0), 0)">0</strong> butir/unit
                        </div>
                        <div class="text-[11px] text-gray-400 italic">
                            Terbilang: <span x-text="obatList.length + ' jenis obat kronis terdaftar'"></span>
                        </div>
                    </div>
                </div>

                <!-- Catatan Tambahan Dokter / Apoteker -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 sm:p-5 no-print">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Catatan Khusus / Anjuran Dokter (Opsional)
                    </label>
                    <textarea name="catatan" x-model="catatanKhusus" rows="2" placeholder="Tuliskan catatan kepatuhan minum obat, keluhan pasien, atau anjuran kontrol berikutnya..." class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs focus:border-blue-500 focus:ring-blue-500 shadow-2xs"></textarea>
                </div>
            </div>

            <!-- ================= KOLOM KANAN: PANEL ACTION (25%) ala GAMBAR 1 ================= -->
            <div class="lg:col-span-4 xl:col-span-3 action-sidebar no-print">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden lg:sticky lg:top-6">
                    
                    <!-- Header Action Card (ala Gambar 1: strip oranye/merah di atas + title Action) -->
                    <div class="px-4 py-3 bg-gradient-to-r from-slate-800 to-slate-900 text-white flex items-center justify-between border-t-4 border-rose-500">
                        <div class="flex items-center gap-2">
                            <svg class="size-4 text-rose-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider">Action</span>
                        </div>
                        <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>

                    <!-- Tombol-Tombol Aksi Resep (ala Gambar 1) -->
                    <div class="p-4 space-y-2.5">
                        
                        <!-- 1. Tombol Simpan Resep (Primary Action) -->
                        <button type="submit" 
                            :disabled="isSubmitting"
                            class="group w-full py-2.5 px-4 inline-flex items-center justify-center gap-2 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed text-white shadow-xs hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 cursor-pointer">
                            <span x-show="isSubmitting" class="animate-spin inline-block size-4 border-2 border-white border-t-transparent rounded-full"></span>
                            <svg x-show="!isSubmitting" class="size-4 group-hover:scale-110 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span x-text="isSubmitting ? 'Menyimpan Catatan Resep...' : 'Simpan Catatan Resep'">Simpan Catatan Resep</span>
                        </button>

                        <!-- 2. Tombol Tambah Data Obat Baru (Master) - Sesuai Permintaan User! -->
                        <button type="button" 
                            @click="bukaModalObatBaru(searchQuery)"
                            class="group w-full py-2.5 px-4 inline-flex items-center justify-center gap-2 text-xs font-bold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 cursor-pointer"
                            title="Tambah obat baru ke database master">
                            <svg class="size-4 group-hover:rotate-90 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Obat Baru (Master)
                        </button>

                        <!-- 3. Tombol Salin Resep Bulan Lalu (Fitur Praktis PRB) -->
                        <button type="button" 
                            @click="salinResepTerakhir()"
                            class="group w-full py-2.5 px-3.5 inline-flex items-center justify-center gap-2 text-xs font-semibold rounded-lg border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 shadow-2xs hover:shadow-xs hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 cursor-pointer">
                            <svg class="size-3.5 text-blue-600 group-hover:scale-110 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                            </svg>
                            Salin Resep Bulan Lalu
                        </button>

                        <!-- 4. Tombol Cetak Lembar Resep (Membuka Pilihan Cetak Obat PRB / Non PRB / Semua) -->
                        <button type="button" 
                            @click="bukaModalCetak()"
                            class="group w-full py-2.5 px-3.5 inline-flex items-center justify-center gap-2 text-xs font-bold rounded-lg bg-sky-500 hover:bg-sky-600 text-white shadow-2xs hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 cursor-pointer">
                            <svg class="size-3.5 group-hover:scale-110 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Lembar Resep
                        </button>

                        <div class="pt-2 border-t border-gray-100 space-y-2">
                            <!-- Kosongkan Resep -->
                            <button type="button" 
                                @click="kosongkanObat()"
                                :disabled="obatList.length === 0"
                                class="group w-full py-2 px-3 inline-flex items-center justify-center gap-1.5 text-xs font-medium rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50 disabled:opacity-40 disabled:pointer-events-none transition-all duration-200">
                                <svg class="size-3.5 group-hover:scale-110 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Kosongkan Resep
                            </button>

                            <!-- WhatsApp Pasien -->
                            @if($prb->pasien->no_hp)
                            <a href="https://wa.me/{{ $prb->pasien->no_hp }}" target="_blank" class="group w-full py-2 px-3 inline-flex items-center justify-center gap-1.5 text-xs font-medium rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 hover:border-emerald-300 border border-emerald-200 shadow-2xs hover:shadow-xs hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                                <svg class="size-3.5 group-hover:scale-110 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
                                WA Pasien ({{ $prb->pasien->no_hp }})
                            </a>
                            @endif

                            <!-- Batal / Kembali -->
                            <a href="{{ route('admin.prb.show', $prb->id) }}" class="group w-full py-2.5 px-3.5 inline-flex items-center justify-center gap-2 text-xs font-semibold rounded-lg border border-slate-300 hover:border-slate-400 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 shadow-2xs hover:shadow-xs hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 cursor-pointer">
                                <svg class="size-4 text-slate-600 group-hover:text-slate-900 group-hover:-translate-x-1 transition-all duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                </svg>
                                Kembali ke Detail Pasien
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- ================= MODAL PILIHAN CETAK RESEP (PILIHAN PRB / NON-PRB / SEMUA) ================= -->
    <div x-show="showModalCetak" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px);"
        class="flex items-center justify-center p-3 sm:p-4 no-print overflow-y-auto"
        @keydown.escape.window="showModalCetak = false">
        
        <div @click.outside="showModalCetak = false"
            class="bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden text-start">
            
            <!-- Header Modal -->
            <div class="px-5 py-3.5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex items-center justify-between border-b border-slate-700 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="size-8 rounded-lg bg-sky-500 text-white flex items-center justify-center shadow-xs">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Cetak Lembar Resep Dokter</h3>
                        <p class="text-[11px] text-slate-300">Format resmi resep klinis Puskesmas Kalijudan Surabaya</p>
                    </div>
                </div>
                <button type="button" @click="showModalCetak = false" class="size-8 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 flex items-center justify-center transition-colors">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Body Modal: 2 Kolom (Pengaturan Cetak di Kiri, Live Preview di Kanan) -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 grid grid-cols-1 md:grid-cols-12 gap-6 bg-slate-50">
                
                <!-- Pengaturan Pilihan Cetak (5 Kolom) -->
                <div class="md:col-span-5 space-y-4">
                    
                    <!-- 1. Pilihan Kategori Obat (Permintaan Utama User) -->
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">
                            Pilih Kategori Obat yang Dicetak <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <!-- Semua Obat -->
                            <label class="flex items-center justify-between p-2.5 rounded-lg border cursor-pointer transition-all"
                                :class="cetakFilter === 'all' ? 'bg-sky-50/70 border-sky-400 ring-2 ring-sky-200 font-semibold' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="cetak_filter" value="all" x-model="cetakFilter" class="text-sky-600 focus:ring-sky-500">
                                    <span class="text-xs text-gray-800">Semua Obat (PRB & SIMBOK)</span>
                                </div>
                                <span class="text-[10.5px] font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full" x-text="obatList.length + ' item'"></span>
                            </label>

                            <!-- Obat PRB Saja -->
                            <label class="flex items-center justify-between p-2.5 rounded-lg border cursor-pointer transition-all"
                                :class="cetakFilter === 'prb' ? 'bg-teal-50/70 border-teal-400 ring-2 ring-teal-200 font-semibold' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="cetak_filter" value="prb" x-model="cetakFilter" class="text-teal-600 focus:ring-teal-500">
                                    <span class="text-xs text-gray-800">Obat PRB Saja</span>
                                </div>
                                <span class="text-[10.5px] font-bold text-teal-700 bg-teal-100/70 px-2 py-0.5 rounded-full" x-text="obatList.filter(o => o.kategori === 'prb').length + ' item'"></span>
                            </label>

                            <!-- Obat Non-PRB Saja -->
                            <label class="flex items-center justify-between p-2.5 rounded-lg border cursor-pointer transition-all"
                                :class="cetakFilter === 'non_prb' ? 'bg-indigo-50/70 border-indigo-400 ring-2 ring-indigo-200 font-semibold' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="cetak_filter" value="non_prb" x-model="cetakFilter" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-gray-800">Obat Non-PRB (SIMBOK) Saja</span>
                                </div>
                                <span class="text-[10.5px] font-bold text-indigo-700 bg-indigo-100/70 px-2 py-0.5 rounded-full" x-text="obatList.filter(o => o.kategori === 'non_prb').length + ' item'"></span>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Data Dokter & SIP -->
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                Informasi Dokter Pemeriksa
                            </label>
                            @if($isAdmin)
                                <span class="text-[10px] bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded-md border border-blue-200">Mode Admin</span>
                            @endif
                        </div>

                        @if($isAdmin)
                        <!-- Khusus Admin: Dropdown Pilihan Dokter -->
                        <div>
                            <label class="block text-[11px] text-gray-600 mb-1 font-medium">Pilih Dokter Pemeriksa (Khusus Admin)</label>
                            <select @change="pilihDokterPemeriksa($event.target.value)" class="py-1.5 px-3 block w-full border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 focus:border-blue-500 shadow-2xs bg-white">
                                <option value="">-- Pilih Dokter Terdaftar (Atau Ketik Manual) --</option>
                                @foreach($daftarDokterPemeriksa ?? [] as $doc)
                                    <option value="{{ $doc['id'] }}" :selected="cetakDokter === @js($doc['nama'])">
                                        {{ $doc['nama'] }} ({{ $doc['sip'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Nama Dokter</label>
                            <input type="text" x-model="cetakDokter" placeholder="Nama Dokter"
                                @if(!$isAdmin) readonly @endif
                                class="py-1.5 px-3 block w-full border border-gray-300 rounded-lg text-xs font-semibold focus:border-blue-500 shadow-2xs @if(!$isAdmin) bg-gray-100 text-gray-700 cursor-not-allowed @endif">
                        </div>
                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Nomor SIP (Opsional)</label>
                            <input type="text" x-model="cetakSip" placeholder="Nomor SIP (Opsional)"
                                @if(!$isAdmin) readonly @endif
                                class="py-1.5 px-3 block w-full border border-gray-300 rounded-lg text-xs focus:border-blue-500 shadow-2xs @if(!$isAdmin) bg-gray-100 text-gray-600 cursor-not-allowed @endif">
                        </div>
                    </div>

                    <!-- 4. Catatan / Anjuran Dokter di Resep -->
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs space-y-1.5">
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                            Catatan / Anjuran Dokter di Resep
                        </label>
                        <textarea x-model="catatanKhusus" rows="2" placeholder="Tuliskan catatan kepatuhan atau anjuran kontrol (opsional)..." class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-xs focus:border-blue-500 shadow-2xs"></textarea>
                        <p class="text-[10px] text-gray-400">Otomatis tercetak di lembar resep sebelum identitas pasien.</p>
                    </div>
                </div>

                <!-- Preview Lembar Resep Asli (7 Kolom) -->
                <div class="md:col-span-7 flex flex-col items-center">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-2">Live Preview Kertas Resep (Persis Format Cetak)</span>
                    
                    <!-- Kotak Resep Fisik Mirip Foto Asli -->
                    <div id="kotak-resep-preview" class="w-full max-w-[360px] bg-white border border-gray-300 rounded-lg shadow-md p-6 font-serif text-xs text-black leading-normal select-none">
                        
                        <!-- KOP SURAT PUSKESMAS KALIJUDAN -->
                        <div class="border-b-2 border-black pb-2 mb-3">
                            <div class="flex items-center gap-2.5">
                                <!-- Logo Surabaya -->
                                <div class="shrink-0 flex items-center justify-center">
                                    <img src="{{ $logoSurabayaBase64 ?? asset('surabaya-city.png') }}" onerror="this.onerror=null; this.src='/surabaya-city.png'" alt="Logo Kota Surabaya" class="w-14 h-14 object-contain">
                                </div>
                                <!-- Teks KOP -->
                                <div class="text-center flex-1 font-serif">
                                    <div class="text-[9px] font-bold uppercase tracking-wide leading-tight">PEMERINTAH KOTA SURABAYA</div>
                                    <div class="text-[9px] font-bold uppercase tracking-wide leading-tight">DINAS KESEHATAN KOTA SURABAYA</div>
                                    <div class="text-[11.5px] font-black uppercase tracking-wider leading-tight mt-0.5">PUSKESMAS KALIJUDAN</div>
                                    <div class="text-[8px] font-sans text-gray-700 leading-tight mt-0.5">Jl. Kalijudan No. 123 Surabaya 60114</div>
                                    <div class="text-[8px] font-sans text-gray-700 leading-tight">Telp. (031) 3824566</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tempat Tanggal & Dokter -->
                        <div class="flex justify-between items-start text-[11px] font-serif mb-3">
                            <div>
                                <p class="font-bold text-xs" x-text="cetakDokter || 'Dokter Pemeriksa'"></p>
                                <p class="text-[10px] text-gray-700" x-text="cetakSip || 'SIP.'"></p>
                            </div>
                            <div class="text-right">
                                <p><span x-text="cetakKota || 'Surabaya'"></span>, <span x-text="cetakTanggal"></span></p>
                            </div>
                        </div>

                        <!-- Body Resep R/ -->
                        <div class="space-y-3.5 my-3 min-h-[140px]">
                            <template x-if="filteredObatCetak.length === 0">
                                <div class="py-8 text-center text-gray-400 italic font-sans text-xs">
                                    Tidak ada obat dalam kategori ini.
                                </div>
                            </template>

                            <template x-for="(obat, idx) in filteredObatCetak" :key="idx">
                                <div class="relative">
                                    <div class="flex items-baseline justify-between">
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="text-base font-bold italic font-serif leading-none select-none">R/</span>
                                            <span class="font-bold text-xs text-black" x-text="obat.nama"></span>
                                        </div>
                                        <div class="text-right font-serif font-bold text-xs">
                                            <span>no. </span>
                                            <span class="underline underline-offset-2" x-text="toRoman(obat.jumlah_obat)"></span>
                                        </div>
                                    </div>
                                    <div class="ps-5 text-[11px] text-gray-800 mt-0.5 font-serif font-normal" x-show="formatSignaResep(obat)">
                                        <span x-text="formatSignaResep(obat)"></span>
                                    </div>
                                    <!-- Garis Penutup Resep & Paraf ala Foto -->
                                    <div class="mt-1 flex items-center">
                                        <div class="flex-1 border-b border-black"></div>
                                        <div class="w-6 text-right text-xs -mb-1 select-none">
                                            <svg class="w-5 h-2.5 inline-block text-black" viewBox="0 0 40 20" fill="none" stroke="currentColor">
                                                <path d="M5 12 Q20 2 28 10 Q35 18 38 12" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Paraf Penutup Akhir Resep (Tanda obat terakhir dokter di bagian tengah) -->
                            <div class="pt-1.5 pb-1 flex justify-center items-center" x-show="filteredObatCetak.length > 0">
                                <div class="w-28 text-center mx-auto">
                                    <svg class="w-20 h-10 mx-auto text-black" viewBox="0 0 120 50" fill="none" stroke="currentColor">
                                        <path d="M10 10 Q40 50 60 25 T110 40" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Khusus / Anjuran Dokter di Lembar Resep Preview -->
                        <div class="border-t border-dashed border-gray-400 pt-1.5 mt-2 text-[10px] font-serif leading-tight" x-show="catatanKhusus && catatanKhusus.trim()">
                            <span class="font-bold italic">Catatan / Anjuran Dokter:</span>
                            <div class="whitespace-pre-line text-gray-800 mt-0.5 ps-1" x-text="catatanKhusus"></div>
                        </div>

                        <!-- Footer Pro Pasien (Identitas Bawah ala Foto) -->
                        <div class="border-t border-black pt-2 mt-2 text-[10px] font-serif leading-relaxed">
                            <div class="grid grid-cols-12 gap-x-1">
                                <div class="col-span-2 font-bold">Pro</div>
                                <div class="col-span-10">: <strong class="uppercase text-xs">{{ $prb->pasien->nama }}</strong></div>
                                
                                <div class="col-span-2 font-bold">Umur</div>
                                <div class="col-span-10 flex items-center justify-between">
                                    <span>: {{ $prb->pasien->tanggal_lahir ? \Carbon\Carbon::parse($prb->pasien->tanggal_lahir)->age : '-' }} Thn</span>
                                    <span>BB : ............... kg</span>
                                </div>

                                <div class="col-span-2 font-bold">No. RM</div>
                                <div class="col-span-10">: {{ $prb->pasien->no_rm ?? 'RM-'.$prb->pasien->nik }}</div>

                                <div class="col-span-2 font-bold">Alamat</div>
                                <div class="col-span-10 truncate">: {{ $prb->pasien->alamat ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-5 py-3.5 bg-white border-t border-gray-200 flex flex-wrap items-center justify-end gap-2.5 shrink-0">
                <button type="button" @click="showModalCetak = false"
                    class="py-2 px-4 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs transition-colors">
                    Batal
                </button>
                <button type="button" 
                    @click="downloadResepPdf()"
                    :disabled="filteredObatCetak.length === 0 || isGeneratingPdf"
                    class="py-2 px-4 inline-flex items-center gap-2 text-xs font-bold rounded-lg bg-rose-600 hover:bg-rose-700 text-white disabled:opacity-50 disabled:pointer-events-none shadow-xs transition-colors cursor-pointer"
                    title="Download lembar resep sebagai file PDF">
                    <template x-if="!isGeneratingPdf">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </template>
                    <template x-if="isGeneratingPdf">
                        <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isGeneratingPdf ? 'Membuat PDF...' : 'Download PDF'"></span>
                </button>
                <button type="button" 
                    @click="eksekusiPrint()"
                    :disabled="filteredObatCetak.length === 0 || isGeneratingPdf"
                    class="py-2 px-5 inline-flex items-center gap-2 text-xs font-bold rounded-lg bg-sky-600 hover:bg-sky-700 text-white disabled:opacity-50 disabled:pointer-events-none shadow-xs transition-colors cursor-pointer"
                    title="Cetak langsung ke printer fisik">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Lembar Resep Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ================= DOKUMEN CETAK RESMI (HANYA MUNCUL SAAT CETAK KE PRINTER) ================= -->
    <div id="lembar-resep-asli" class="print-only">
        <!-- KOP SURAT PUSKESMAS KALIJUDAN -->
        <div style="border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <!-- Logo Surabaya -->
                <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ $logoSurabayaBase64 ?? asset('surabaya-city.png') }}" onerror="this.onerror=null; this.src='/surabaya-city.png'" alt="Logo Kota Surabaya" style="width: 48px; height: 48px; object-fit: contain; display: block;">
                </div>
                <!-- Teks KOP -->
                <div style="text-align: center; flex: 1; font-family: 'Times New Roman', Times, serif;">
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">PEMERINTAH KOTA SURABAYA</div>
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">DINAS KESEHATAN KOTA SURABAYA</div>
                    <div style="font-size: 14.5px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.8px; line-height: 1.2; margin-top: 1px;">PUSKESMAS KALIJUDAN</div>
                    <div style="font-size: 9.5px; font-family: Arial, sans-serif; line-height: 1.2; margin-top: 1px;">Jl. Kalijudan No. 123 Surabaya 60114</div>
                    <div style="font-size: 9.5px; font-family: Arial, sans-serif; line-height: 1.2;">Telp. (031) 3824566</div>
                </div>
            </div>
        </div>

        <!-- Tanggal & Dokter -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; font-size: 12.5px; font-family: 'Times New Roman', Times, serif; margin-bottom: 10px;">
            <div>
                <p style="font-weight: bold; font-size: 14px; margin: 0;" x-text="cetakDokter || 'Dokter Pemeriksa'"></p>
                <p style="font-size: 11.5px; margin: 2px 0 0 0;" x-text="cetakSip || 'SIP.'"></p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0;"><span x-text="cetakKota || 'Surabaya'"></span>, <span x-text="cetakTanggal"></span></p>
            </div>
        </div>

        <!-- Daftar R/ Obat -->
        <div style="font-family: 'Times New Roman', Times, serif; font-size: 12.5px; margin: 8px 0;">
            <template x-for="(obat, idx) in filteredObatCetak" :key="idx">
                <div style="margin-bottom: 10px;">
                    <div style="display: flex; align-items: baseline; justify-content: space-between;">
                        <div style="display: flex; align-items: baseline; gap: 8px;">
                            <span style="font-size: 19px; font-weight: bold; font-style: italic; line-height: 1;">R/</span>
                            <span style="font-weight: bold; font-size: 13.5px;" x-text="obat.nama"></span>
                        </div>
                        <div style="text-align: right; font-weight: bold; font-size: 12.5px;">
                            <span>no. </span>
                            <span style="text-decoration: underline;" x-text="toRoman(obat.jumlah_obat)"></span>
                        </div>
                    </div>
                    <div style="padding-left: 24px; font-size: 12px; margin-top: 1px;" x-show="formatSignaResep(obat)">
                        <span x-text="formatSignaResep(obat)"></span>
                    </div>
                    <!-- Garis Penutup Resep & Paraf -->
                    <div style="display: flex; align-items: center; margin-top: 3px;">
                        <div style="flex: 1; border-bottom: 1px solid #000;"></div>
                        <div style="width: 20px; text-align: right; line-height: 0;">
                            <svg style="width: 18px; height: 9px;" viewBox="0 0 40 20" fill="none" stroke="#000">
                                <path d="M5 12 Q20 2 28 10 Q35 18 38 12" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Paraf Penutup Akhir Resep (Tanda obat terakhir dokter di bagian tengah) -->
            <div style="display: flex; justify-content: center; align-items: center; padding-top: 4px; padding-bottom: 4px; text-align: center; margin: 0 auto;" x-show="filteredObatCetak.length > 0">
                <div style="width: 100px; text-align: center; margin: 0 auto;">
                    <svg style="width: 70px; height: 30px; display: inline-block; margin: 0 auto;" viewBox="0 0 120 50" fill="none" stroke="#000">
                        <path d="M10 10 Q40 50 60 25 T110 40" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Catatan Khusus / Anjuran Dokter -->
        <div style="border-top: 1px dashed #333; padding-top: 5px; margin-top: 7px; font-family: 'Times New Roman', Times, serif; font-size: 12px; line-height: 1.4;" x-show="catatanKhusus && catatanKhusus.trim()">
            <div style="font-weight: bold; font-style: italic;">Catatan / Anjuran Dokter:</div>
            <div style="white-space: pre-line; margin-top: 2px; padding-left: 4px;" x-text="catatanKhusus"></div>
        </div>

        <!-- Footer Pro Pasien -->
        <div style="border-top: 1.5px solid #000; padding-top: 7px; margin-top: 10px; font-family: 'Times New Roman', Times, serif; font-size: 12px; line-height: 1.45;">
            <table style="width: 100%; border-collapse: collapse; border: none;">
                <tr style="border: none;">
                    <td style="width: 55px; font-weight: bold; border: none; padding: 1px 0;">Pro</td>
                    <td style="border: none; padding: 1px 0;">: <strong style="text-transform: uppercase; font-size: 13.5px;">{{ $prb->pasien->nama }}</strong></td>
                </tr>
                <tr style="border: none;">
                    <td style="font-weight: bold; border: none; padding: 1px 0;">Umur</td>
                    <td style="border: none; padding: 1px 0;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>: {{ $prb->pasien->tanggal_lahir ? \Carbon\Carbon::parse($prb->pasien->tanggal_lahir)->age : '-' }} Thn</span>
                            <span>BB : ............... kg</span>
                        </div>
                    </td>
                </tr>
                <tr style="border: none;">
                    <td style="font-weight: bold; border: none; padding: 1px 0;">No. RM</td>
                    <td style="border: none; padding: 1px 0;">: {{ $prb->pasien->no_rm ?? 'RM-'.$prb->pasien->nik }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="font-weight: bold; border: none; padding: 1px 0;">Alamat</td>
                    <td style="border: none; padding: 1px 0;">: {{ $prb->pasien->alamat ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Modal Konfirmasi Kosongkan Resep -->
    <div x-show="showConfirmKosongkan" 
        style="display: none; position: fixed; inset: 0; z-index: 99999; background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);"
        class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-4 no-print">
        <div @click.outside="showConfirmKosongkan = false"
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

    <!-- Modal Tambah Obat Baru ke Master & Resep (Form Nama & Dosis) -->
    <div x-show="showModalObatBaru" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);"
        class="flex items-center justify-center p-4 no-print"
        @keydown.escape.window="showModalObatBaru = false">
        <div @click.outside="showModalObatBaru = false"
            class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-lg w-full overflow-hidden text-start">
            
            <!-- Header Modal -->
            <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50/50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-xs shrink-0">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Tambah Obat Baru ke Master</h3>
                        <p class="text-xs text-gray-500">Daftarkan obat baru dan masukkan langsung ke resep pasien</p>
                    </div>
                </div>
                <button type="button" @click="showModalObatBaru = false" class="size-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 inline-flex items-center justify-center transition-colors">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-5 space-y-4">
                <!-- Pilihan Kategori Obat -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kategori Obat</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="relative flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                            :class="formObatBaru.kategori === 'prb' ? 'bg-teal-50/70 border-teal-400 ring-2 ring-teal-200' : 'bg-white border-gray-200 hover:bg-gray-50'">
                            <input type="radio" name="modal_kategori" value="prb" x-model="formObatBaru.kategori" class="sr-only">
                            <span class="size-2.5 rounded-full bg-teal-500 shrink-0"></span>
                            <div>
                                <span class="block text-xs font-bold text-gray-800">Obat PRB</span>
                                <span class="block text-[10px] text-gray-500">Program Rujuk Balik</span>
                            </div>
                        </label>

                        <label class="relative flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                            :class="formObatBaru.kategori === 'non_prb' ? 'bg-indigo-50/70 border-indigo-400 ring-2 ring-indigo-200' : 'bg-white border-gray-200 hover:bg-gray-50'">
                            <input type="radio" name="modal_kategori" value="non_prb" x-model="formObatBaru.kategori" class="sr-only">
                            <span class="size-2.5 rounded-full bg-indigo-500 shrink-0"></span>
                            <div>
                                <span class="block text-xs font-bold text-gray-800">Non-PRB (SIMBOK)</span>
                                <span class="block text-[10px] text-gray-500">Katalog Umum / Apotek</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Input Nama Obat -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Nama Obat / Merek / Zat Aktif <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        x-ref="modalNamaInput"
                        x-model="formObatBaru.nama" 
                        placeholder="misal: Atorvastatin, Acarbose, Candesartan"
                        class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-sm font-semibold focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                    <span class="text-[10px] text-gray-400 mt-1 block">Ketik nama obat tanpa dosis (dosis diisi di kolom bawah)</span>
                </div>

                <!-- Input Dosis / Kekuatan -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Dosis / Kekuatan (Berapa mg / gram / dll)
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                x-ref="modalDosisInput"
                                x-model="formObatBaru.dosisAngka" 
                                placeholder="misal: 10, 20, 100, 500"
                                class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-sm font-semibold focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                        </div>
                        <div class="w-32">
                            <select x-model="formObatBaru.dosisSatuan" 
                                class="py-2 px-2.5 block w-full border border-gray-300 rounded-lg text-xs font-bold bg-white text-gray-700 focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                                <option value="mg">mg</option>
                                <option value="gram">gram</option>
                                <option value="mcg">mcg</option>
                                <option value="ml">ml</option>
                                <option value="IU">IU</option>
                                <option value="tablet">tablet</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Cepat Dosis Populer -->
                    <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                        <span class="text-[10.5px] font-medium text-gray-500">Pilihan cepat:</span>
                        <template x-for="d in ['5 mg', '10 mg', '20 mg', '50 mg', '100 mg', '500 mg', '1 gram']" :key="d">
                            <button type="button" 
                                @click="pilihDosisCepat(d.split(' ')[0], d.split(' ')[1])"
                                class="px-2 py-0.5 rounded-md text-[11px] font-semibold border transition-all"
                                :class="formObatBaru.dosisAngka === d.split(' ')[0] && formObatBaru.dosisSatuan === d.split(' ')[1] 
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-xs' 
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200'">
                                <span x-text="d"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Live Preview Format di Database Master -->
                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="size-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 8.5 7 7" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-blue-600 tracking-wider block">Preview di Master Obat</span>
                            <span class="text-sm font-bold text-gray-900" x-text="namaLengkapObatBaru || 'Nama obat belum diisi...'"></span>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full shrink-0"
                        :class="formObatBaru.kategori === 'prb' ? 'bg-teal-100 text-teal-800' : 'bg-indigo-100 text-indigo-800'"
                        x-text="formObatBaru.kategori === 'prb' ? 'PRB' : 'SIMBOK'"></span>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-5 py-3.5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" @click="showModalObatBaru = false"
                    class="py-2 px-4 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs transition-colors">
                    Batal
                </button>
                <button type="button" 
                    @click="simpanObatBaruModal()"
                    :disabled="!namaLengkapObatBaru.trim()"
                    class="py-2 px-4 inline-flex items-center gap-1.5 text-xs font-bold rounded-lg bg-rose-600 text-white hover:bg-rose-700 disabled:opacity-50 disabled:pointer-events-none shadow-xs transition-colors cursor-pointer">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Simpan & Masukkan ke Resep
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Toast Notification -->
    <div x-show="alertMessage"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        style="display: none; position: fixed; top: 1.25rem; right: 1.25rem; z-index: 99999; max-width: 24rem; width: 100%;"
        class="fixed top-5 end-5 z-[9999] max-w-sm w-full bg-white border border-amber-200 rounded-xl shadow-xl p-4 no-print"
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
</div>

@push('scripts')
<script>
function riwayatForm() {
    return {
        isSubmitting: false,
        tanggalDiambil: new Date().toISOString().split('T')[0],
        tanggalJatuhTempo: '',
        programPasien: @json($programPasien ?? []),
        presetDiagnosaPrb: @json($presetDiagnosaPrb ?? []),
        selectedProgramKey: '',
        kodeDiagnosa: '',
        diagnosa: '',

        init() {
            if (this.programPasien && this.programPasien.length > 0) {
                const first = this.programPasien[0];
                this.selectedProgramKey = first.key;
                this.kodeDiagnosa = first.kode || '';
                this.diagnosa = first.diagnosa || '';
                this.lastResep = first.obat_list || [];
            } else {
                this.selectedProgramKey = 'baru';
                this.kodeDiagnosa = @json($prb->diagnosa ?? '');
                this.diagnosa = @json($prb->diagnosa ?? '');
            }

            if (!this.tanggalJatuhTempo && this.tanggalDiambil) {
                let d = new Date(this.tanggalDiambil);
                d.setDate(d.getDate() + 30);
                this.tanggalJatuhTempo = d.toISOString().split('T')[0];
            }

            // Parse signa string dari lastResep server menjadi signa1 & signa2 dan simpan signa string
            this.lastResep = this.lastResep.map(item => {
                const parts = this.parseSignaToParts(item.signa || '1 x 1');
                const signaStr = item.signa || `${parts.s1} x ${parts.s2}`;
                return { ...item, signa: signaStr, signa1: parts.s1, signa2: parts.s2 };
            });

            // Sinkronkan obatList jika ada initial items
            this.obatList = this.obatList.map(item => {
                const parts = this.parseSignaToParts(item.signa || `${item.signa1 || 1} x ${item.signa2 || 1}`);
                const signaStr = item.signa || `${parts.s1} x ${parts.s2}`;
                return { ...item, signa: signaStr, signa1: parts.s1, signa2: parts.s2 };
            });
        },

        pilihProgram(key) {
            this.selectedProgramKey = key;
            if (key === 'baru') {
                this.kodeDiagnosa = '';
                this.diagnosa = '';
                this.showAlert('Silakan tentukan kode ICD-10 dan nama diagnosa baru.');
            } else {
                const prog = this.programPasien.find(p => p.key === key);
                if (prog) {
                    this.kodeDiagnosa = prog.kode || '';
                    this.diagnosa = prog.diagnosa || '';
                    this.lastResep = prog.obat_list || [];
                    
                    if (this.obatList.length === 0 && prog.obat_list && prog.obat_list.length > 0) {
                        this.salinResepTerakhir();
                    } else if (prog.obat_list && prog.obat_list.length > 0) {
                        this.showAlert(`Program diganti ke: ${prog.label}. Klik "Salin Resep Bulan Lalu" jika ingin memuat paket obat ini.`);
                    }
                }
            }
        },

        pilihPresetDiagnosa(kode, nama) {
            this.kodeDiagnosa = kode;
            this.diagnosa = nama;
        },

        masterPrb: {!! json_encode($masterObatPrb ?? []) !!},
        masterNonPrb: {!! json_encode($masterObatNonPrb ?? []) !!},
        lastResep: {!! json_encode($lastRiwayat ? $lastRiwayat->obatPrb->map(function($o) {
            return [
                'nama' => $o->nama_obat,
                'kategori' => $o->kategori,
                'signa' => $o->signa ?? '1 x 1',
                'jumlah_hari' => $o->jumlah_hari ?? 30,
                'jumlah_obat' => $o->jumlah_obat ?? 30,
                'aturan_minum' => $o->aturan_minum ?? '',
            ];
        }) : []) !!},
        obatList: [],
        selectedKategori: 'prb',
        searchQuery: '',
        activeFilter: '',
        isSearching: false,
        isDropdownOpen: false,
        alertMessage: '',
        alertTimeout: null,
        showConfirmKosongkan: false,
        showModalObatBaru: false,
        
        // State Modal Cetak Resep Dokter
        showModalCetak: false,
        cetakFilter: 'all', // 'all', 'prb', 'non_prb'
        selectedDokterId: @json($defaultDokterId),
        cetakDokter: @json($defaultDokterNama),
        cetakSip: @json($defaultDokterSip),
        cetakKota: 'Surabaya',
        cetakTanggal: '',
        cetakFormatNo: 'romawi', // 'romawi' atau 'angka'
        catatanKhusus: '',
        isGeneratingPdf: false,
        
        formObatBaru: {
            nama: '',
            dosisAngka: '',
            dosisSatuan: 'mg',
            kategori: 'prb',
        },
        signaPresets: [
            { val: '1 x 1', desc: '1 kali sehari 1 tablet' },
            { val: '2 x 1', desc: '2 kali sehari 1 tablet' },
            { val: '3 x 1', desc: '3 kali sehari 1 tablet' },
            { val: '4 x 1', desc: '4 kali sehari 1 tablet' },
            { val: '1 x 0.5', desc: '1 kali sehari 1/2 tablet' },
            { val: '2 x 0.5', desc: '2 kali sehari 1/2 tablet' },
            { val: '0-0-1', desc: 'Malam hari 1 tablet' },
            { val: '1-0-1', desc: 'Pagi & Malam 1 tablet' },
            { val: '1-1-1', desc: 'Pagi, Siang, Malam 1 tablet' },
            { val: '1 x 1 PRN', desc: 'Bila Perlu (PRN)' },
        ],
        aturanOptions: [
            { val: 'Sesudah Makan', dotClass: 'bg-emerald-500' },
            { val: 'Sebelum Makan', dotClass: 'bg-amber-500' },
            { val: 'Bersama Makan', dotClass: 'bg-blue-500' },
            { val: 'Malam Hari', dotClass: 'bg-purple-500' },
            { val: 'Pagi Hari', dotClass: 'bg-sky-500' },
            { val: 'Bila Perlu (PRN)', dotClass: 'bg-rose-500' },
        ],

        get namaLengkapObatBaru() {
            const nama = (this.formObatBaru.nama || '').trim();
            if (!nama) return '';
            const angka = (this.formObatBaru.dosisAngka || '').trim();
            if (angka) {
                return `${nama} ${angka} ${this.formObatBaru.dosisSatuan}`.trim();
            }
            return nama;
        },

        get filteredObatCetak() {
            if (this.cetakFilter === 'prb') {
                return this.obatList.filter(o => o.kategori === 'prb');
            }
            if (this.cetakFilter === 'non_prb') {
                return this.obatList.filter(o => o.kategori === 'non_prb');
            }
            return this.obatList;
        },

        pilihDokterPemeriksa(docId) {
            if (!docId) return;
            const doctors = @json($daftarDokterPemeriksa ?? []);
            const doc = doctors.find(d => String(d.id) === String(docId));
            if (doc) {
                this.selectedDokterId = doc.id;
                this.cetakDokter = doc.nama;
                this.cetakSip = doc.sip;
            }
        },

        bukaModalCetak() {
            if (this.obatList.length === 0) {
                this.showAlert('Belum ada obat dalam resep untuk dicetak.');
                return;
            }
            this.cetakTanggal = this.tanggalDiambil ? this.formatTanggalIndo(this.tanggalDiambil) : this.formatTanggalIndo(new Date());
            this.showModalCetak = true;
        },

        formatTanggalIndo(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },

        toRoman(num) {
            num = parseInt(num) || 0;
            if (num <= 0) return '';
            const lookup = {
                M: 1000, CM: 900, D: 500, CD: 400,
                C: 100, XC: 90, L: 50, XL: 40,
                X: 10, IX: 9, V: 5, IV: 4, I: 1
            };
            let roman = '';
            for (let i in lookup) {
                while (num >= lookup[i]) {
                    roman += i;
                    num -= lookup[i];
                }
            }
            return roman;
        },

        formatSignaResep(signaOrObat) {
            let s = '';
            if (typeof signaOrObat === 'object' && signaOrObat !== null) {
                s = (signaOrObat.signa || '').trim();
                if (!s && signaOrObat.signa1) {
                    s = `${signaOrObat.signa1} x ${signaOrObat.signa2 || 1}`;
                }
            } else {
                s = (signaOrObat || '').trim();
            }
            if (!s) s = '1 x 1';

            let cleanSigna = s.replace(/^s\.?\s*/i, '').trim();
            if (cleanSigna) {
                return `S. ${cleanSigna}`;
            }
            return 'S. 1 x 1';
        },

        downloadResepPdf() {
            if (this.filteredObatCetak.length === 0 || this.isGeneratingPdf) return;
            this.isGeneratingPdf = true;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.prb.resep.download-pdf") }}';
            form.style.display = 'none';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const fields = {
                pasien_nama: @json($prb->pasien->nama ?? 'Pasien'),
                pasien_umur: @json($prb->pasien->tanggal_lahir ? \Carbon\Carbon::parse($prb->pasien->tanggal_lahir)->age . ' Thn' : '-'),
                pasien_no_rm: @json($prb->pasien->no_rm ?? ($prb->pasien->nik ?? '-')),
                pasien_alamat: @json($prb->pasien->alamat ?? '-'),
                dokter: this.cetakDokter || '',
                sip: this.cetakSip || '',
                kota: this.cetakKota || 'Surabaya',
                tanggal: this.cetakTanggal || '',
                catatan: this.catatanKhusus || '',
                filter: this.cetakFilter || 'all',
                format_nomor: this.cetakFormatNomor || 'romawi',
                obat_list: JSON.stringify(this.obatList || [])
            };

            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key] || '';
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            setTimeout(() => {
                if (form.parentNode) {
                    form.parentNode.removeChild(form);
                }
                this.isGeneratingPdf = false;
            }, 2000);
        },

        eksekusiPrint() {
            window.print();
        },

        bukaModalObatBaru(initialQuery = '') {
            this.formObatBaru.nama = (initialQuery || this.searchQuery || '').trim();
            this.formObatBaru.dosisAngka = '';
            this.formObatBaru.dosisSatuan = 'mg';
            this.formObatBaru.kategori = this.selectedKategori || 'prb';
            this.isDropdownOpen = false;
            this.showModalObatBaru = true;
            this.$nextTick(() => {
                if (!this.formObatBaru.nama) {
                    this.$refs.modalNamaInput?.focus();
                } else {
                    this.$refs.modalDosisInput?.focus();
                }
            });
        },

        pilihDosisCepat(angka, satuan = 'mg') {
            this.formObatBaru.dosisAngka = angka;
            this.formObatBaru.dosisSatuan = satuan;
        },

        simpanObatBaruModal() {
            const namaFinal = this.namaLengkapObatBaru;
            if (!namaFinal) {
                this.showAlert('Nama obat tidak boleh kosong.');
                return;
            }

            this.selectedKategori = this.formObatBaru.kategori;
            this.tambahObat(namaFinal);
            this.showModalObatBaru = false;
        },

        salinResepTerakhir() {
            if (!this.lastResep || this.lastResep.length === 0) {
                this.showAlert('Tidak ada data resep bulan lalu yang tersimpan untuk disalin.');
                return;
            }

            this.obatList = this.lastResep.map(item => ({
                id: Date.now() + Math.random(),
                nama: item.nama,
                kategori: item.kategori,
                signa: item.signa || `${item.signa1 || 1} x ${item.signa2 || 1}`,
                signa1: item.signa1 || 1,
                signa2: item.signa2 || 1,
                jumlah_hari: this.defaultHari || item.jumlah_hari || 30,
                jumlah_obat: item.jumlah_obat || 30,
                aturan_minum: item.aturan_minum || '',
                is_new: false
            }));

            this.updateAllHari();
            this.showAlert(`Berhasil menyalin ${this.obatList.length} obat dari resep bulan lalu.`);
        },

        get defaultHari() {
            if (this.tanggalDiambil && this.tanggalJatuhTempo) {
                const d1 = new Date(this.tanggalDiambil);
                const d2 = new Date(this.tanggalJatuhTempo);
                const diffTime = d2.getTime() - d1.getTime();
                const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                if (diffDays > 0) return diffDays;
            }
            return 30;
        },

        getSignaDesc(signa) {
            if (!signa) return '';
            const s = String(signa).trim().toLowerCase().replace(/\s+/g, ' ');
            const preset = this.signaPresets.find(p => p.val.toLowerCase() === s);
            if (preset) return preset.desc;

            const normalized = s.replace(/\s*([x*])\s*/g, ' x ');
            const presetNorm = this.signaPresets.find(p => p.val.toLowerCase() === normalized);
            if (presetNorm) return presetNorm.desc;

            const match = s.match(/^(\d+(?:\.\d+)?)\s*(?:x|\*)\s*(\d+(?:\.\d+)?|\d+\s*\/\s*\d+)(?:\s+(prn|bila perlu))?$/i);
            if (match) {
                const freq = match[1];
                let dose = match[2];
                if (dose === '0.5' || dose === '1/2') dose = '1/2';
                const prn = match[3] ? ' bila perlu' : '';
                return `${freq} kali sehari ${dose} tablet${prn}`;
            }

            if (s === '0-0-1') return 'Malam hari 1 tablet';
            if (s === '1-0-1') return 'Pagi & Malam 1 tablet';
            if (s === '1-1-1') return 'Pagi, Siang, Malam 1 tablet';

            return '';
        },

        parseSigna(signaStr) {
            if (!signaStr) return 1;
            let s = String(signaStr).trim().toLowerCase().replace(',', '.');

            if (s.includes('-')) {
                const parts = s.split('-');
                let sum = 0;
                let valid = false;
                for (const p of parts) {
                    const trimmed = p.trim();
                    if (trimmed.includes('/')) {
                        const frac = trimmed.split('/');
                        const val = parseFloat(frac[0]) / (parseFloat(frac[1]) || 1);
                        if (!isNaN(val)) { sum += val; valid = true; }
                    } else {
                        const val = parseFloat(trimmed);
                        if (!isNaN(val)) { sum += val; valid = true; }
                    }
                }
                if (valid && sum > 0) return sum;
            }

            const match = s.match(/(\d+(?:\.\d+)?)\s*(?:x|\*)\s*(\d+(?:\.\d+)?|\d+\s*\/\s*\d+)/i);
            if (match) {
                const freq = parseFloat(match[1]) || 1;
                let dose = 1;
                if (match[2].includes('/')) {
                    const frac = match[2].split('/');
                    dose = (parseFloat(frac[0]) || 1) / (parseFloat(frac[1]) || 1);
                } else {
                    dose = parseFloat(match[2]) || 1;
                }
                return freq * dose;
            }

            const singleNum = s.match(/\d+(?:\.\d+)?/);
            if (singleNum) {
                return parseFloat(singleNum[0]) || 1;
            }

            return 1;
        },

        parseSignaToParts(signaStr) {
            if (!signaStr) return { s1: 1, s2: 1 };
            const s = String(signaStr).trim();
            const match = s.match(/^(\d+(?:[.,]\d+)?)\s*(?:x|\*)\s*(\d+(?:[.,]\d+)?)$/i);
            if (match) {
                return {
                    s1: parseFloat(match[1].replace(',', '.')) || 1,
                    s2: parseFloat(match[2].replace(',', '.')) || 1,
                };
            }
            const single = parseFloat(s.replace(',', '.'));
            if (!isNaN(single) && single > 0) {
                return { s1: single, s2: 1 };
            }
            return { s1: 1, s2: 1 };
        },

        hitungOtomatisJumlah(obat) {
            const s1 = parseFloat(obat.signa1) || 0;
            const s2 = parseFloat(obat.signa2) || 0;
            const days = parseInt(obat.jumlah_hari) || 0;
            if (s1 > 0 && s2 > 0 && days > 0) {
                obat.jumlah_obat = Math.ceil(s1 * s2 * days);
            }
            obat.signa = (obat.signa1 || 1) + ' x ' + (obat.signa2 || 1);
        },

        updateAllHari() {
            const days = this.defaultHari;
            this.obatList.forEach(obat => {
                obat.jumlah_hari = days;
                this.hitungOtomatisJumlah(obat);
            });
        },
        
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

        setSaranJatuhTempo() {
            if (!this.tanggalDiambil) return;
            let date = new Date(this.tanggalDiambil);
            date.setDate(date.getDate() + 30);
            this.tanggalJatuhTempo = date.toISOString().split('T')[0];
            this.updateAllHari();
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

            const exists = this.obatList.some(o => o.nama.toLowerCase() === obatNama.toLowerCase() && o.kategori === this.selectedKategori);
            if (exists) {
                this.showAlert(`Obat "${obatNama}" sudah ada di dalam resep ini.`);
                return;
            }

            const targetMaster = this.selectedKategori === 'prb' ? this.masterPrb : this.masterNonPrb;
            const isNew = !targetMaster.some(m => m.toLowerCase() === obatNama.toLowerCase());

            const hari = this.defaultHari;
            const defaultS1 = 1;
            const defaultS2 = 1;
            const jumlah = Math.ceil(defaultS1 * defaultS2 * hari);

            this.obatList.push({
                id: Date.now() + Math.random(),
                nama: obatNama,
                kategori: this.selectedKategori,
                signa: `${defaultS1} x ${defaultS2}`,
                signa1: defaultS1,
                signa2: defaultS2,
                jumlah_hari: hari,
                jumlah_obat: jumlah,
                aturan_minum: '',
                is_new: isNew
            });

            if (isNew) {
                targetMaster.push(obatNama);
                targetMaster.sort();
            }

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
