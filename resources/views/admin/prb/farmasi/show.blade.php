@extends('layouts.app')

@php
    $pasien = $riwayat->pasienPrb?->pasien;
    
    // Hitung umur format SIMPUS
    $umurLengkap = '-';
    $tglLahirFormatted = '-';
    if ($pasien && $pasien->tanggal_lahir) {
        $tglAcuan = $riwayat->tanggal_diambil ?? $riwayat->created_at ?? now();
        $diff = \Carbon\Carbon::parse($pasien->tanggal_lahir)->diff(\Carbon\Carbon::parse($tglAcuan));
        $umurLengkap = "{$diff->y} Tahun";
        $tglLahirFormatted = \Carbon\Carbon::parse($pasien->tanggal_lahir)->isoFormat('D MMM Y');
    }

    // Tentukan jenis asuransi & tipe pasien
    $tipePasien = $pasien->tipe_pasien ?? 'Umum';
    $asuransi = 'Umum';
    if (!empty($pasien->no_bpjs) || stripos($tipePasien, 'BPJS') !== false) {
        $asuransi = 'BPJS';
    } elseif (stripos($tipePasien, 'APBD') !== false || stripos($tipePasien, 'Jamkesda') !== false) {
        $asuransi = 'APBD';
    }

    // Dokter / Pencatat resep
    $rawDokter = $riwayat->pencatat?->nama ?? 'Dokter Pemeriksa';
    $dokterFormatted = (!preg_match('/^(dr\.|drg\.|dokter)/i', $rawDokter) && !in_array($rawDokter, ['Admin', 'Admin Test', 'Dokter Pemeriksa']))
        ? 'dr. ' . $rawDokter 
        : $rawDokter;
    $dokterSip = $riwayat->pencatat?->nip ? 'SIP. ' . $riwayat->pencatat->nip : 'SIP. -';

    $diagnosaTeks = $riwayat->diagnosa_lengkap 
        ?: ($riwayat->pasienPrb?->keterangan_diagnosa 
        ?? $riwayat->pasienPrb?->diagnosa 
        ?? 'Program Rujuk Balik (PRB)');

    $initialObatStatus = [];
    foreach ($obatPrbs as $o) {
        $initialObatStatus[$o->id] = [
            'id' => $o->id,
            'status' => $o->status_pemberian ?? 'sudah', // default beri obat siap diberikan
            'catatan' => $o->catatan_farmasi ?? ''
        ];
    }

    $tanggalCetakResep = $riwayat->tanggal_diambil 
        ? $riwayat->tanggal_diambil->format('d/m/Y') 
        : ($riwayat->created_at ? $riwayat->created_at->format('d/m/Y') : date('d/m/Y'));

    $printPayload = [
        'tanggal' => $tanggalCetakResep,
        'dokter' => $dokterFormatted,
        'sip' => $dokterSip,
        'catatan' => $riwayat->catatan ?? '',
        'obatList' => $obatPrbs->map(function($o) {
            return [
                'nama' => $o->nama_obat,
                'kategori' => $o->kategori ?? 'prb',
                'signa' => $o->signa ?? '',
                'jumlah_obat' => $o->jumlah_obat ?? 1,
                'aturan_minum' => $o->aturan_minum ?? '',
            ];
        })->values()->toArray(),
    ];
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
    #sidebar, header, nav, .no-print, aside, div[role="dialog"] {
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
        width: 135mm !important;
        padding: 6mm 8mm !important;
        margin: 0 auto !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-shadow: none !important;
        border: none !important;
        font-family: 'Times New Roman', Times, serif !important;
    }
    @page {
        size: auto;
        margin: 5mm;
    }
}
.print-only {
    display: none;
}
</style>
@endpush

@section('content')
<div class="max-w-[1400px] mx-auto px-3 sm:px-6 py-4 space-y-4" x-data="kunjunganObatApp({{ json_encode($initialObatStatus) }}, {{ json_encode($printPayload) }})">

    <!-- Top Breadcrumb / Title Bar Puskesmas Kalijudan -->
    <div class="flex items-center justify-between bg-white px-4 py-2.5 rounded-xl border border-gray-200/80 shadow-2xs">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.prb.farmasi.index', ['tanggal' => $riwayat->tanggal_diambil?->format('Y-m-d')]) }}" 
               class="p-1.5 rounded-lg text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors" title="Kembali ke Antrean Farmasi">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <h1 class="text-base sm:text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
                <span>Puskesmas Kalijudan</span>
                <span class="text-xs font-normal text-gray-400">/</span>
                <span class="text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 px-2.5 py-0.5 rounded-full">
                    Detail Kunjungan Farmasi PRB
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500 font-mono hidden sm:inline">
                Tgl: {{ $riwayat->tanggal_diambil ? $riwayat->tanggal_diambil->format('d/m/Y') : $riwayat->created_at->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <!-- Banner Info Pasien Sesuai Format SIMPUS Kalijudan (Baris 1) -->
    <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <!-- Nama Pasien -->
            <span class="font-bold text-sm sm:text-base text-gray-900 pr-2">
                {{ strtoupper($pasien->nama ?? 'Tanpa Nama') }}
            </span>

            <!-- Badge Gender -->
            @if(($pasien->jenis_kelamin ?? '') === 'P')
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-cyan-500 text-white shadow-2xs">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2a6 6 0 0 0-6 6c0 2.97 2.16 5.44 5 5.92V16H9a1 1 0 0 0 0 2h2v3a1 1 0 0 0 2 0v-3h2a1 1 0 0 0 0-2h-2v-2.08c2.84-.48 5-2.95 5-5.92a6 6 0 0 0-6-6Zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                    </svg>
                    Perempuan
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-cyan-600 text-white shadow-2xs">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 3h-5a1 1 0 0 0 0 2h2.59l-4.29 4.29a6 6 0 1 0 1.41 1.41L19 6.41V9a1 1 0 0 0 2 0V4a1 1 0 0 0-1-1ZM9 19a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                    </svg>
                    Laki-laki
                </span>
            @endif

            <!-- Badge Umur -->
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-600 text-white shadow-2xs">
                📅 {{ $umurLengkap }} ({{ $tglLahirFormatted }})
            </span>

            <!-- Badge Gol Darah / Info Alamat -->
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-semibold bg-cyan-500 text-white shadow-2xs">
                🩸 Gol Darah: {{ $pasien->golongan_darah ?: 'Tidak Tahu' }}
            </span>

            <!-- Badge Asuransi BPJS / Jamkesda -->
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-700 text-white shadow-2xs">
                ℹ️ {{ $asuransi }}
            </span>
            @if($tipePasien && $tipePasien !== 'Umum')
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-cyan-600 text-white shadow-2xs">
                    {{ $tipePasien }}
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.prb.show', $riwayat->pasien_prb_id) }}" 
               class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                <span>Rekam Medis Pasien</span>
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Banner Info Poli & Dokter (Baris 2 SIMPUS) -->
    <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <!-- Poli PRB -->
            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-bold bg-cyan-500 text-white uppercase shadow-2xs">
                POLI PRB
            </span>

            <!-- Dokter Pemeriksa -->
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded text-xs font-bold bg-[#e05344] text-white shadow-2xs">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5Zm0 12a8 8 0 0 0-8 8 1 1 0 0 0 1 1h14a1 1 0 0 0 1-1 8 8 0 0 0-8-8Z"/>
                </svg>
                {{ $dokterFormatted }}
            </span>

            @if($diagnosaTeks)
            <span class="text-xs text-gray-600 pl-2 hidden md:inline">
                Diagnosa: <strong class="text-teal-800">{{ $diagnosaTeks }}</strong>
            </span>
            @endif
        </div>

        <div class="text-xs text-gray-500">
            Status Saat Ini: 
            <span class="font-bold px-2 py-0.5 rounded {{ $riwayat->status_farmasi_badge['class'] }}">
                {{ $riwayat->status_farmasi_badge['label'] }}
            </span>
        </div>
    </div>

    <!-- Form Submit Pemberian Obat -->
    <form action="{{ route('admin.prb.farmasi.beri-obat', $riwayat->id) }}" method="POST" id="formBeriObat">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- Kolom Kiri: Tabel / Card Kunjungan Obat (Ala SIMPUS Puskesmas Kalijudan) -->
            <div class="lg:col-span-9 bg-white border border-gray-200 rounded-xl shadow-xs overflow-hidden">
                
                <!-- Header Kunjungan Obat Merah/Coral SIMPUS -->
                <div class="text-white px-4 py-2.5 flex items-center justify-between" style="background-color: #d9534f; color: #ffffff;">
                    <div class="flex items-center gap-2">
                        <svg class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">Kunjungan Obat</h2>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="bg-white/20 text-white px-2 py-0.5 rounded font-mono font-bold">
                            Total: {{ $obatPrbs->count() }} Item Obat
                        </span>
                    </div>
                </div>

                <!-- Toolbar Aksi Cepat Farmasi (Set Semua & Reset) -->
                <div class="bg-slate-50 border-b border-gray-200 px-4 py-2 flex items-center justify-end gap-2">
                    <!-- Tombol Set Semua Ada (Lengkap) -->
                        <button type="button" 
                                @click="setSemua('sudah')" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white shadow-xs hover:shadow hover:opacity-95 active:scale-95 transition-all cursor-pointer"
                                style="background-color: #059669; color: #ffffff;"
                                title="Tandai semua obat sebagai Sudah Diberikan (Lengkap)">
                            <svg class="size-3.5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                            <span>Set Semua Ada (Lengkap)</span>
                        </button>

                        <!-- Tombol Reset Semua -->
                        <button type="button" 
                                @click="resetSemua()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-gray-700 border border-gray-300 shadow-2xs hover:bg-gray-100 hover:text-gray-900 active:scale-95 transition-all cursor-pointer"
                                title="Reset semua obat kembali ke Belum Diproses">
                            <svg class="size-3.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span>Reset Semua</span>
                        </button>
                    </div>

                <!-- Daftar Item Obat (Persis Tampilan Kotak SIMPUS Kalijudan) -->
                <div class="divide-y divide-gray-200">
                    @forelse($obatPrbs as $idx => $obat)
                        @php
                            $kategoriLabel = strtoupper($obat->kategori ?? 'PRB');
                            $qty = $obat->jumlah_obat ?: 30;
                            $signa = $obat->signa ?: '1x1';
                        @endphp
                        <div class="p-4 hover:bg-gray-50/60 transition-colors" :class="obatStatus[{{ $obat->id }}].status === 'kosong' ? 'bg-amber-50/40' : ''">
                            
                            <!-- Input Hidden untuk Form Submission -->
                            <input type="hidden" name="obat[{{ $idx }}][id]" value="{{ $obat->id }}">
                            <input type="hidden" name="obat[{{ $idx }}][status_pemberian]" :value="obatStatus[{{ $obat->id }}].status">

                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                
                                <!-- Sisi Kiri: Nama Obat & Badges -->
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 text-sm">
                                            {{ $idx + 1 }}. ({{ $kategoriLabel }})-{{ strtoupper($obat->nama_obat) }}
                                        </span>
                                    </div>

                                    <!-- Badges: qty, Signa (ala SIMPUS) -->
                                    <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                        <!-- Qty Badge (Cyan) -->
                                        <span class="inline-block px-2 py-0.5 rounded font-bold text-white shadow-2xs font-mono" style="background-color: #0891b2; color: #ffffff;">
                                            qty: {{ $qty }}
                                        </span>

                                        <!-- Signa Badge (Green) -->
                                        <span class="inline-block px-2 py-0.5 rounded font-bold text-white shadow-2xs font-mono" style="background-color: #059669; color: #ffffff;">
                                            Signa: {{ $signa }}
                                        </span>

                                        <!-- Hari -->
                                        @if($obat->jumlah_hari)
                                        <span class="inline-block px-2 py-0.5 rounded font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $obat->jumlah_hari }} Hari
                                        </span>
                                        @endif

                                        <!-- Aturan Minum -->
                                        @if($obat->aturan_minum)
                                        <span class="inline-block px-2 py-0.5 rounded font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $obat->aturan_minum }}
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Sisi Kanan: Pilihan Status Obat (Sudah Diberikan vs Stok Kosong) -->
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 shrink-0">
                                    <!-- Tombol Pilihan Status: SUDAH DIBERIKAN (HIJAU) -->
                                    <button type="button" 
                                            @click="setStatus({{ $obat->id }}, 'sudah')"
                                            class="py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs hover:opacity-95 active:scale-95"
                                            :style="obatStatus[{{ $obat->id }}].status === 'sudah' ? 'background-color: #059669; color: #ffffff;' : 'background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb;'"
                                            title="Klik untuk tandai Sudah Diberikan (klik lagi untuk batalkan)">
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Sudah Diberikan</span>
                                    </button>

                                    <!-- Tombol Pilihan Status: STOK KOSONG (KUNING) -->
                                    <button type="button" 
                                            @click="setStatus({{ $obat->id }}, 'kosong')"
                                            class="py-1.5 px-3 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs hover:opacity-95 active:scale-95"
                                            :style="obatStatus[{{ $obat->id }}].status === 'kosong' ? 'background-color: #fbbf24; color: #111827; border: 1px solid #f59e0b; font-weight: 800;' : 'background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb;'"
                                            title="Klik untuk tandai Stok Kosong (klik lagi untuk batalkan)">
                                        <svg class="size-3.5 text-amber-800" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Stok Kosong</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 italic">
                            Tidak ada rincian obat pada kunjungan ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Kolom Kanan: Kotak "Action" Sesuai Format SIMPUS Kalijudan -->
            <div class="lg:col-span-3 space-y-4">
                
                <!-- Action Box Ala SIMPUS (Collapsible) -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-xs overflow-hidden transition-all">
                    <!-- Header Action (Bisa diklik untuk buka/tutup Action) -->
                    <button type="button" 
                            @click="actionOpen = !actionOpen"
                            class="w-full bg-gray-100 hover:bg-gray-200/80 transition-colors text-gray-800 px-4 py-2.5 flex items-center justify-between border-b border-gray-200 cursor-pointer select-none"
                            title="Klik untuk membuka / menutup Action">
                        <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                            Action
                            <span class="text-[10px] font-normal text-gray-500 font-sans" x-text="actionOpen ? '(buka)' : '(tutup)'"></span>
                        </span>
                        <svg class="size-4 text-gray-500 transition-transform duration-200" 
                             :class="actionOpen ? '' : '-rotate-90'"
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Body Action: Tombol Beri Obat & Nota Resep/Cetak Aktif -->
                    <div x-show="actionOpen" x-transition.duration.200ms class="p-4 space-y-2.5">
                        <!-- Tombol Utama: OBAT / BERI OBAT (Merah/Coral SIMPUS) -->
                        <button type="submit" 
                                class="w-full py-3 px-4 rounded-lg text-sm font-bold text-white shadow-md hover:shadow-lg hover:opacity-90 active:opacity-100 transition-all flex items-center justify-center gap-2 cursor-pointer"
                                style="background-color: #e05344; color: #ffffff;">
                            <svg class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Beri Obat</span>
                        </button>

                        <!-- Tombol Nota Resep / Cetak (Cyan SIMPUS) - Langsung Cetak Resep Dokter -->
                        <button type="button" 
                                @click="bukaModalCetak()"
                                class="w-full py-2.5 px-3 rounded-lg text-xs font-bold text-white shadow-2xs hover:opacity-90 active:opacity-100 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                style="background-color: #0891b2; color: #ffffff;"
                                title="Cetak Lembar Resep Dokter Resmi">
                            <svg class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                            </svg>
                            <span>Nota Resep / Cetak</span>
                        </button>
                    </div>
                </div>

                <!-- Live Status Preview Box -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-2xs space-y-3">
                    <span class="text-xs font-bold text-gray-700 block uppercase tracking-wide">
                        Status Luar di Tabel Antrean:
                    </span>

                    <!-- Preview Badge -->
                    <div class="p-3 rounded-lg border text-center transition-all"
                         :class="previewStatus.class">
                        <span class="block text-xs font-extrabold" x-text="previewStatus.label"></span>
                        <span class="block text-[10px] mt-0.5 opacity-90" x-text="previewStatus.desc"></span>
                    </div>

                    <div class="text-[11px] text-gray-500 space-y-1 pt-1 border-t border-gray-100 font-mono">
                        <div class="flex justify-between">
                            <span>Total Obat:</span>
                            <span class="font-bold text-gray-800" x-text="Object.keys(obatStatus).length"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Diberikan:</span>
                            <span class="font-bold text-emerald-700" x-text="countStatus('sudah')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Stok Kosong:</span>
                            <span class="font-bold text-amber-700" x-text="countStatus('kosong')"></span>
                        </div>
                        <div class="flex justify-between" x-show="countStatus('belum') > 0">
                            <span>Belum Diproses:</span>
                            <span class="font-bold text-gray-500" x-text="countStatus('belum')"></span>
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
                    
                    <!-- 1. Pilihan Kategori Obat -->
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
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1">
                            Informasi Dokter Pemeriksa
                        </label>
                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Nama Dokter</label>
                            <input type="text" x-model="cetakDokter" placeholder="Nama Dokter" class="py-1.5 px-3 block w-full border border-gray-300 rounded-lg text-xs font-semibold focus:border-blue-500 shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Nomor SIP (Opsional)</label>
                            <input type="text" x-model="cetakSip" placeholder="Nomor SIP (Opsional)" class="py-1.5 px-3 block w-full border border-gray-300 rounded-lg text-xs focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <!-- 3. Format Angka Resep (Romawi vs Angka Biasa) -->
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs space-y-2">
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1">
                            Format Jumlah Obat (No.)
                        </label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer"
                                :class="cetakFormatNo === 'romawi' ? 'bg-blue-50 border-blue-400 font-semibold text-blue-900' : 'border-gray-200 text-gray-700'">
                                <input type="radio" value="romawi" x-model="cetakFormatNo">
                                <span>Romawi (No. XXX)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer"
                                :class="cetakFormatNo === 'angka' ? 'bg-blue-50 border-blue-400 font-semibold text-blue-900' : 'border-gray-200 text-gray-700'">
                                <input type="radio" value="angka" x-model="cetakFormatNo">
                                <span>Angka (No. 30)</span>
                            </label>
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
                    <div class="w-full max-w-[360px] bg-white border border-gray-300 rounded-lg shadow-md p-6 font-serif text-xs text-black leading-normal select-none">
                        
                        <!-- KOP SURAT PUSKESMAS KALIJUDAN -->
                        <div class="border-b-2 border-black pb-2 mb-3">
                            <div class="flex items-center gap-2.5">
                                <!-- Logo Surabaya -->
                                <div class="shrink-0 flex items-center justify-center">
                                    <img src="{{ asset('surabaya-city.png') }}?v=2" alt="Logo Kota Surabaya" class="w-14 h-14 object-contain">
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
                                            <span class="underline underline-offset-2" x-text="cetakFormatNo === 'romawi' ? toRoman(obat.jumlah_obat) : obat.jumlah_obat"></span>
                                        </div>
                                    </div>
                                    <div class="ps-5 text-[11px] text-gray-800 mt-0.5 font-serif italic" x-show="formatSignaResep(obat.signa, obat.aturan_minum)">
                                        <span x-text="formatSignaResep(obat.signa, obat.aturan_minum)"></span>
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

                            <!-- Paraf Penutup Akhir Resep (Garis panjang menyudut tanda tangan dokter ala foto) -->
                            <div class="pt-1 flex justify-end" x-show="filteredObatCetak.length > 0">
                                <div class="w-24 text-center">
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
                                <div class="col-span-10">: <strong class="uppercase text-xs">{{ $pasien->nama ?? 'Tanpa Nama' }}</strong></div>
                                
                                <div class="col-span-2 font-bold">Umur</div>
                                <div class="col-span-10 flex items-center justify-between">
                                    <span>: {{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age : '-' }} Thn</span>
                                    <span>BB : ............... kg</span>
                                </div>

                                <div class="col-span-2 font-bold">No. RM</div>
                                <div class="col-span-10">: {{ $pasien->no_rm ?? ($pasien->nik ? 'RM-'.$pasien->nik : '-') }}</div>

                                <div class="col-span-2 font-bold">Alamat</div>
                                <div class="col-span-10 truncate">: {{ $pasien->alamat ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-5 py-3.5 bg-white border-t border-gray-200 flex items-center justify-end gap-2 shrink-0">
                <button type="button" @click="showModalCetak = false"
                    class="py-2 px-4 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs transition-colors">
                    Batal
                </button>
                <button type="button" 
                    @click="eksekusiPrint()"
                    :disabled="filteredObatCetak.length === 0"
                    class="py-2 px-5 inline-flex items-center gap-2 text-xs font-bold rounded-lg bg-sky-600 text-white hover:bg-sky-700 disabled:opacity-50 disabled:pointer-events-none shadow-xs transition-colors cursor-pointer">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Lembar Resep Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- ================= DOKUMEN CETAK RESMI (HANYA MUNCUL SAAT CETAK KE PRINTER) ================= -->
    <div id="lembar-resep-asli" class="print-only">
        <!-- KOP SURAT PUSKESMAS KALIJUDAN -->
        <div style="border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Logo Surabaya -->
                <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('surabaya-city.png') }}?v=2" alt="Logo Kota Surabaya" style="width: 52px; height: 52px; object-fit: contain; display: block;">
                </div>
                <!-- Teks KOP -->
                <div style="text-align: center; flex: 1; font-family: 'Times New Roman', Times, serif;">
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">PEMERINTAH KOTA SURABAYA</div>
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">DINAS KESEHATAN KOTA SURABAYA</div>
                    <div style="font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.8px; line-height: 1.2; margin-top: 1px;">PUSKESMAS KALIJUDAN</div>
                    <div style="font-size: 9px; font-family: Arial, sans-serif; line-height: 1.2; margin-top: 1px;">Jl. Kalijudan No. 123 Surabaya 60114</div>
                    <div style="font-size: 9px; font-family: Arial, sans-serif; line-height: 1.2;">Telp. (031) 3824566</div>
                </div>
            </div>
        </div>

        <!-- Tanggal & Dokter -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; font-size: 11.5px; font-family: 'Times New Roman', Times, serif; margin-bottom: 12px;">
            <div>
                <p style="font-weight: bold; font-size: 12.5px; margin: 0;" x-text="cetakDokter || 'Dokter Pemeriksa'"></p>
                <p style="font-size: 10.5px; margin: 2px 0 0 0;" x-text="cetakSip || 'SIP.'"></p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0;"><span x-text="cetakKota || 'Surabaya'"></span>, <span x-text="cetakTanggal"></span></p>
            </div>
        </div>

        <!-- Daftar R/ Obat -->
        <div style="font-family: 'Times New Roman', Times, serif; font-size: 11.5px; margin: 10px 0;">
            <template x-for="(obat, idx) in filteredObatCetak" :key="idx">
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; align-items: baseline; justify-content: space-between;">
                        <div style="display: flex; align-items: baseline; gap: 8px;">
                            <span style="font-size: 17px; font-weight: bold; font-style: italic; line-height: 1;">R/</span>
                            <span style="font-weight: bold; font-size: 12.5px;" x-text="obat.nama"></span>
                        </div>
                        <div style="text-align: right; font-weight: bold; font-size: 11.5px;">
                            <span>no. </span>
                            <span style="text-decoration: underline;" x-text="cetakFormatNo === 'romawi' ? toRoman(obat.jumlah_obat) : obat.jumlah_obat"></span>
                        </div>
                    </div>
                    <div style="padding-left: 22px; font-size: 11px; font-style: italic; margin-top: 1px;" x-show="formatSignaResep(obat.signa, obat.aturan_minum)">
                        <span x-text="formatSignaResep(obat.signa, obat.aturan_minum)"></span>
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

            <!-- Paraf Penutup Akhir Resep -->
            <div style="display: flex; justify-content: flex-end; padding-top: 2px;" x-show="filteredObatCetak.length > 0">
                <div style="width: 90px; text-align: center;">
                    <svg style="width: 70px; height: 30px;" viewBox="0 0 120 50" fill="none" stroke="#000">
                        <path d="M10 10 Q40 50 60 25 T110 40" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Catatan Khusus / Anjuran Dokter -->
        <div style="border-top: 1px dashed #333; padding-top: 5px; margin-top: 8px; font-family: 'Times New Roman', Times, serif; font-size: 11px; line-height: 1.4;" x-show="catatanKhusus && catatanKhusus.trim()">
            <div style="font-weight: bold; font-style: italic;">Catatan / Anjuran Dokter:</div>
            <div style="white-space: pre-line; margin-top: 2px; padding-left: 4px;" x-text="catatanKhusus"></div>
        </div>

        <!-- Footer Pro Pasien -->
        <div style="border-top: 1.5px solid #000; padding-top: 8px; margin-top: 12px; font-family: 'Times New Roman', Times, serif; font-size: 11px; line-height: 1.5;">
            <table style="width: 100%; border-collapse: collapse; border: none;">
                <tr style="border: none;">
                    <td style="width: 55px; font-weight: bold; border: none; padding: 1px 0;">Pro</td>
                    <td style="border: none; padding: 1px 0;">: <strong style="text-transform: uppercase; font-size: 11.5px;">{{ $pasien->nama ?? 'Tanpa Nama' }}</strong></td>
                </tr>
                <tr style="border: none;">
                    <td style="font-weight: bold; border: none; padding: 1px 0;">Umur</td>
                    <td style="border: none; padding: 1px 0;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>: {{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age : '-' }} Thn</span>
                            <span>BB : ............... kg</span>
                        </div>
                    </td>
                </tr>
                <tr style="border: none;">
                    <td style="font-weight: bold; border: none; padding: 1px 0;">No. RM</td>
                    <td style="border: none; padding: 1px 0;">: {{ $pasien->no_rm ?? ($pasien->nik ? 'RM-'.$pasien->nik : '-') }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="font-weight: bold; border: none; padding: 1px 0;">Alamat</td>
                    <td style="border: none; padding: 1px 0;">: {{ $pasien->alamat ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<script>
    function kunjunganObatApp(initialData, printPayload) {
        return {
            actionOpen: true,
            obatStatus: initialData,

            // State & Logika Cetak Lembar Resep Dokter Resmi
            showModalCetak: false,
            cetakFilter: 'all', // 'all', 'prb', 'non_prb'
            cetakDokter: printPayload?.dokter || 'Dokter Pemeriksa',
            cetakSip: printPayload?.sip || 'SIP. -',
            cetakKota: 'Surabaya',
            cetakTanggal: printPayload?.tanggal || '',
            cetakFormatNo: 'romawi', // 'romawi' atau 'angka'
            catatanKhusus: printPayload?.catatan || '',
            obatList: printPayload?.obatList || [],

            get filteredObatCetak() {
                if (this.cetakFilter === 'prb') {
                    return this.obatList.filter(o => o.kategori === 'prb');
                }
                if (this.cetakFilter === 'non_prb') {
                    return this.obatList.filter(o => o.kategori === 'non_prb');
                }
                return this.obatList;
            },

            bukaModalCetak() {
                this.cetakFilter = 'all';
                this.showModalCetak = true;
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

            formatSignaResep(signa, aturan) {
                const s = (signa || '').trim();
                let a = (aturan || '').trim();
                if (a === '-' || a.toLowerCase() === 'pilih') a = '';

                if (!s && !a) return '';

                let cleanSigna = s.replace(/^s\.?\s*/i, '').trim();

                if (cleanSigna && a) {
                    return `S. ${cleanSigna} (${a})`;
                } else if (cleanSigna) {
                    return `S. ${cleanSigna}`;
                } else if (a) {
                    return `S. (${a})`;
                }
                return '';
            },

            eksekusiPrint() {
                window.print();
            },

            setStatus(id, status) {
                if (this.obatStatus[id]) {
                    // Jika klik status yang sama, toggle off kembali ke 'belum'
                    this.obatStatus[id].status = (this.obatStatus[id].status === status) ? 'belum' : status;
                }
            },
            setSemua(status) {
                for (let k in this.obatStatus) {
                    this.obatStatus[k].status = status;
                }
            },
            resetSemua() {
                for (let k in this.obatStatus) {
                    this.obatStatus[k].status = 'belum';
                    this.obatStatus[k].catatan = '';
                }
            },
            countStatus(st) {
                let count = 0;
                for (let k in this.obatStatus) {
                    if (this.obatStatus[k].status === st) count++;
                }
                return count;
            },
            get previewStatus() {
                let kosong = this.countStatus('kosong');
                let sudah = this.countStatus('sudah');
                let belum = this.countStatus('belum');
                let total = Object.keys(this.obatStatus).length;

                if (kosong > 0) {
                    return {
                        label: 'Sudah Diberikan Tidak Lengkap',
                        class: 'bg-amber-400 text-gray-900 border-amber-500 shadow-xs font-bold',
                        desc: 'Ada ' + kosong + ' obat dengan stok kosong'
                    };
                } else if (sudah === total && total > 0) {
                    return {
                        label: 'Sudah',
                        class: 'bg-teal-500 text-white border-teal-600 shadow-xs font-bold',
                        desc: 'Semua ' + total + ' obat siap diberikan'
                    };
                } else if (sudah > 0 && belum > 0) {
                    return {
                        label: 'Sebagian Diberikan',
                        class: 'bg-sky-100 text-sky-800 border-sky-300 font-bold',
                        desc: sudah + ' dari ' + total + ' obat sudah disiapkan'
                    };
                } else {
                    return {
                        label: 'Belum Diracik',
                        class: 'bg-gray-100 text-gray-700 border-gray-300 font-semibold',
                        desc: 'Belum diproses farmasi'
                    };
                }
            }
        };
    }
</script>
@endsection
