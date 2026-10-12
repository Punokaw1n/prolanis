@extends('layouts.app')

@section('content')
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
                        <div class="flex items-center gap-3" x-data="{ modalOpen: false }">
                            <a href="{{ route('admin.prb.riwayat.edit', $r->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </a>
                            <button type="button" @click="modalOpen = true" class="text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
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
                    
                    <div class="mt-3 bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Daftar Obat</p>
                        @if($r->obatPrb->count() > 0)
                            <ul class="list-disc list-inside text-sm text-gray-800">
                                @foreach($r->obatPrb as $obat)
                                    <li>
                                        {{ $obat->nama_obat }} 
                                        @if($obat->kategori == 'prb')
                                            <span class="inline-flex text-[10px] bg-blue-100 text-blue-800 px-1.5 rounded ms-1">PRB</span>
                                        @else
                                            <span class="inline-flex text-[10px] bg-purple-100 text-purple-800 px-1.5 rounded ms-1">Non-PRB</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 italic">Belum ada rincian obat.</p>
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
@endsection
