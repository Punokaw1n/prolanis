@extends('layouts.app')

@php
    $currentUser = auth()->user();
    $rawNamaUser = trim($currentUser?->nama ?? '');
    $defaultDokterNama = $rawNamaUser 
        ? (!preg_match('/^(dr\.|drg\.|dokter)/i', $rawNamaUser) ? 'dr. ' . $rawNamaUser : $rawNamaUser)
        : 'Dokter Pemeriksa';
    $defaultDokterSip = $currentUser?->nip ? 'SIP. ' . $currentUser->nip : 'SIP. -';
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
<div x-data="showPrbPage()">
<div class="no-print">
<header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-x-4">
        <a href="{{ route('admin.prb.index') }}" class="inline-flex justify-center items-center size-10 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none transition-colors" title="Kembali ke Data Pasien PRB">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Detail Pasien PRB</h1>
            <p class="mt-1 text-sm text-gray-600">Profil pasien dan riwayat pengambilan obat PRB.</p>
        </div>
    </div>
    <div class="sm:ms-auto flex items-center gap-2" x-data="{ toggleStatusModalOpen: false }">
        <button type="button" @click="toggleStatusModalOpen = true" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 transition-colors">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-1 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            {{ $prb->status_keaktifan == 'aktif' ? 'Nonaktifkan Pasien' : 'Aktifkan Pasien' }}
        </button>

        <!-- Toggle Status Modal (Alpine.js) -->
        <template x-teleport="body">
            <div x-show="toggleStatusModalOpen" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none; z-index: 9999;">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);"></div>
                <div class="fixed" style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 32rem; padding: 1rem; z-index: 10000;">
                    <div x-show="toggleStatusModalOpen" 
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="w-full text-left overflow-hidden shadow-xl transform transition-all" style="background-color: #ffffff; border-radius: 0.75rem;">
                        
                        <div class="p-6 sm:p-10 text-center">
                            <span class="mb-4 inline-flex justify-center items-center size-[62px] rounded-full border-4" style="border-color: #fef3c7; background-color: #fde68a; color: #d97706;">
                                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            </span>
                            <h3 class="mb-2 text-xl font-bold" id="modal-title" style="color: #1f2937;">
                                Konfirmasi Perubahan Status
                            </h3>
                            <p class="text-sm whitespace-normal" style="color: #6b7280;">
                                Apakah Anda yakin ingin <strong>{{ $prb->status_keaktifan == 'aktif' ? 'MENONAKTIFKAN' : 'MENGAKTIFKAN KEMBALI' }}</strong> status PRB atas nama pasien {{ $prb->pasien->nama }}?
                                @if($prb->status_keaktifan == 'aktif')
                                    <br><br>Pasien yang nonaktif tidak akan muncul lagi di Dashboard Pengingat WA.
                                @endif
                            </p>
                            
                            <div class="mt-6 flex justify-center gap-x-4">
                                <button type="button" @click="toggleStatusModalOpen = false" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border shadow-sm transition-colors" style="background-color: #ffffff; border-color: #e5e7eb; color: #1f2937;">
                                    Batal
                                </button>
                                <form action="{{ route('admin.prb.toggle-status', $prb->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent transition-colors" style="background-color: #d97706; color: #ffffff;">
                                        Ya, {{ $prb->status_keaktifan == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Profil Card -->
    <div class="lg:col-span-1 flex flex-col gap-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 bg-blue-600">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 size-12 bg-white text-blue-600 font-bold rounded-full flex items-center justify-center text-xl">
                        {{ strtoupper(substr($prb->pasien->nama, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">{{ $prb->pasien->nama }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            @if($prb->status_keaktifan == 'aktif')
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-2 rounded-md text-xs font-medium bg-blue-50 text-blue-800" title="Pasien PRB Aktif">Aktif PRB</span>
                            @else
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-2 rounded-md text-xs font-medium bg-gray-50 text-gray-800" title="Pasien sudah keluar, pindah faskes, atau meninggal">Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <dl class="grid sm:grid-cols-1 gap-x-4 gap-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">NIK</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $prb->pasien->nik }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">No. Rekam Medis</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ $prb->pasien->no_rm ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Nomor Handphone</dt>
                        <dd class="text-sm font-medium text-gray-800">
                            @if($prb->pasien->no_hp)
                                <a href="https://wa.me/{{ $prb->pasien->no_hp }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    {{ $prb->pasien->no_hp }}
                                </a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Tgl Terdaftar PRB</dt>
                        <dd class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($prb->tanggal_terdaftar)->translatedFormat('d M Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Riwayat Section -->
    <div class="lg:col-span-2">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Riwayat Pengambilan Obat</h2>
                <a href="{{ route('admin.prb.riwayat.create', $prb->id) }}" class="py-2 px-3 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                    + Catat Pengambilan
                </a>
            </div>

            <div class="border-l border-gray-200 ml-3">
                @forelse($riwayat as $r)
                <!-- Item -->
                <div class="mb-8 pl-6 relative">
                    <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 top-0 ring-4 ring-white {{ $r->tanggal_diambil ? 'bg-green-100' : 'bg-amber-100' }}">
                        @if($r->tanggal_diambil)
                            <svg class="size-3 text-green-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg class="size-3 text-amber-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        @endif
                    </span>
                    
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="flex items-center text-base font-semibold text-gray-900">
                            Jatuh Tempo: {{ \Carbon\Carbon::parse($r->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                            @if($r->status)
                                <span class="{{ $r->status['color'] }} text-xs font-medium me-2 px-2.5 py-0.5 rounded ms-3">{{ $r->status['label'] }}</span>
                            @endif
                        </h3>
                        <div class="flex items-center gap-2" x-data="{ modalOpen: false }">
                            @if($r->obatPrb->count() > 0)
                            @php
                                $rDoctor = $r->pencatat ?? auth()->user();
                                $rDoctorNama = trim($rDoctor?->nama ?? '');
                                $rDoctorNamaFormatted = $rDoctorNama 
                                    ? (!preg_match('/^(dr\.|drg\.|dokter)/i', $rDoctorNama) ? 'dr. ' . $rDoctorNama : $rDoctorNama)
                                    : 'Dokter Pemeriksa';
                                $rDoctorSip = $rDoctor?->nip ? 'SIP. ' . $rDoctor->nip : 'SIP. -';
                            @endphp
                            <button type="button" 
                                @click="bukaModalCetakRiwayat({{ json_encode([
                                    'tanggal' => $r->tanggal_diambil ? \Carbon\Carbon::parse($r->tanggal_diambil)->format('d/m/Y') : ($r->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($r->tanggal_jatuh_tempo)->format('d/m/Y') : date('d/m/Y')),
                                    'dokter' => $rDoctorNamaFormatted,
                                    'sip' => $rDoctorSip,
                                    'catatan' => $r->catatan ?? '',
                                    'obatList' => $r->obatPrb->map(function($o) {
                                        return [
                                            'nama' => $o->nama_obat,
                                            'kategori' => $o->kategori,
                                            'signa' => $o->signa,
                                            'jumlah_obat' => $o->jumlah_obat,
                                            'aturan_minum' => $o->aturan_minum,
                                        ];
                                    })->values()
                                ]) }})"
                                class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-lg text-xs font-semibold bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition-colors shadow-2xs" 
                                title="Cetak Lembar Resep Dokter (Pilihan PRB / Non-PRB / Semua)">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span>Cetak Resep</span>
                            </button>
                            @endif

                            <a href="{{ route('admin.prb.riwayat.edit', $r->id) }}" class="p-1 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </a>
                            <button type="button" @click="modalOpen = true" class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>

                            <!-- Delete Modal (Alpine.js) -->
                            <template x-teleport="body">
                                <div x-show="modalOpen" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none; z-index: 9999;">
                                    <!-- Overlay -->
                                    <div class="fixed inset-0 transition-opacity" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);"></div>
                                    
                                    <!-- Modal Panel -->
                                    <div class="fixed" style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 32rem; padding: 1rem; z-index: 10000;">
                                        <div x-show="modalOpen" 
                                             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                             class="w-full text-left overflow-hidden shadow-xl transform transition-all" style="background-color: #ffffff; border-radius: 0.75rem;">
                                            
                                            <div class="p-6 sm:p-10 text-center">
                                                <span class="mb-4 inline-flex justify-center items-center size-[62px] rounded-full border-4" style="border-color: #fef2f2; background-color: #fee2e2; color: #ef4444;">
                                                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                                    </svg>
                                                </span>
                                                <h3 class="mb-2 text-xl font-bold" id="modal-title" style="color: #1f2937;">
                                                    Hapus Riwayat Pengambilan?
                                                </h3>
                                                <p class="text-sm whitespace-normal" style="color: #6b7280;">
                                                    Tindakan ini tidak dapat dibatalkan. Menghapus riwayat juga akan menghapus daftar rincian obat yang tersimpan di dalamnya.
                                                </p>
                                                
                                                <div class="mt-6 flex justify-center gap-x-4">
                                                    <button type="button" @click="modalOpen = false" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border shadow-sm transition-colors" style="background-color: #ffffff; border-color: #e5e7eb; color: #1f2937;">
                                                        Batal
                                                    </button>
                                                    <form action="{{ route('admin.prb.riwayat.destroy', $r->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent transition-colors" style="background-color: #dc2626; color: #ffffff;">
                                                            Ya, Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <time class="block mb-2 text-sm font-normal leading-none text-gray-400">
                        Tgl Diambil: {{ $r->tanggal_diambil ? \Carbon\Carbon::parse($r->tanggal_diambil)->translatedFormat('d M Y') : '-' }}
                    </time>
                    
                    <div class="mt-3 bg-gray-50 border border-gray-200 rounded-xl p-3.5">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs text-gray-500 font-bold uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="size-3.5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Resep Obat ({{ $r->obatPrb->count() }} item)
                            </span>
                            @if($r->obatPrb->count() > 0 && $r->obatPrb->sum('jumlah_obat') > 0)
                                <span class="text-[11px] font-semibold text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded-md">
                                    Total: {{ $r->obatPrb->sum('jumlah_obat') }} butir/unit
                                </span>
                            @endif
                        </div>

                        @if($r->obatPrb->count() > 0)
                            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                    <thead class="bg-gray-50/80 text-gray-500 font-semibold uppercase text-[10px] tracking-wider">
                                        <tr>
                                            <th class="py-2 px-2.5 text-center w-8">No</th>
                                            <th class="py-2 px-2.5 w-16">Kode</th>
                                            <th class="py-2 px-3">Nama Obat</th>
                                            <th class="py-2 px-2.5 w-28 whitespace-nowrap">Signa (Aturan)</th>
                                            <th class="py-2 px-2 text-center w-16">Hari</th>
                                            <th class="py-2 px-2.5 text-center w-24">Qty</th>
                                            <th class="py-2 px-3 whitespace-nowrap">Waktu Minum</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($r->obatPrb as $idx => $obat)
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="py-2 px-2.5 text-center text-gray-400 font-mono">{{ $idx + 1 }}.</td>
                                                <td class="py-2 px-2.5">
                                                    <span class="font-bold text-[9.5px] font-mono px-1.5 py-0.2 rounded {{ $obat->kategori == 'prb' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                                                        {{ $obat->kategori == 'prb' ? '(PRB)' : '(NON)' }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3 font-semibold text-gray-900">
                                                    {{ $obat->nama_obat }}
                                                </td>
                                                <td class="py-2 px-2.5 font-mono text-gray-700">
                                                    {{ $obat->signa ?? '-' }}
                                                </td>
                                                <td class="py-2 px-2 text-center text-gray-600">
                                                    {{ $obat->jumlah_hari ? $obat->jumlah_hari . ' hr' : '-' }}
                                                </td>
                                                <td class="py-2 px-2.5 text-center">
                                                    <span class="font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200/60 text-[11px]">
                                                        {{ $obat->jumlah_obat ?? '-' }} butir
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3 text-gray-600 text-[11px]">
                                                    {{ $obat->aturan_minum ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-xs text-gray-500 italic">Belum ada rincian obat yang dicatat.</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="ms-6 mb-4">
                    <p class="text-sm text-gray-500 italic">Belum ada riwayat pengambilan obat.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div> <!-- End .no-print -->

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
                <td style="border: none; padding: 1px 0;">: <strong style="text-transform: uppercase; font-size: 11.5px;">{{ $prb->pasien->nama }}</strong></td>
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
</div> <!-- End x-data="showPrbPage()" -->
@endsection

@push('scripts')
<script>
function showPrbPage() {
    return {
        showModalCetak: false,
        cetakFilter: 'all', // 'all', 'prb', 'non_prb'
        cetakDokter: @json($defaultDokterNama),
        cetakSip: @json($defaultDokterSip),
        cetakKota: 'Surabaya',
        cetakTanggal: '',
        cetakFormatNo: 'romawi', // 'romawi' atau 'angka'
        catatanKhusus: '',
        obatList: [],

        get filteredObatCetak() {
            if (this.cetakFilter === 'prb') {
                return this.obatList.filter(o => o.kategori === 'prb');
            }
            if (this.cetakFilter === 'non_prb') {
                return this.obatList.filter(o => o.kategori === 'non_prb');
            }
            return this.obatList;
        },

        bukaModalCetakRiwayat(payload) {
            this.cetakTanggal = payload.tanggal || '';
            this.obatList = payload.obatList || [];
            this.cetakDokter = payload.dokter || @json($defaultDokterNama);
            this.cetakSip = payload.sip || @json($defaultDokterSip);
            this.catatanKhusus = payload.catatan || '';
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
        }
    }
}
</script>
@endpush
