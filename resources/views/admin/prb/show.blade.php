@extends('layouts.app')

@section('content')
<header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Detail Pasien PRB</h1>
        <p class="mt-2 text-lg text-gray-600">Profil pasien dan riwayat pengambilan obat PRB.</p>
    </div>
    <div class="sm:ms-auto">
        <a href="{{ route('admin.prb.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
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
                        <p class="text-blue-100 text-sm">
                            @if($prb->status_keaktifan == 'aktif')
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-2 rounded-md text-xs font-medium bg-blue-50 text-blue-800">Aktif PRB</span>
                            @else
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-2 rounded-md text-xs font-medium bg-gray-50 text-gray-800">Tidak Aktif</span>
                            @endif
                        </p>
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
                    
                    <h3 class="flex items-center mb-1 text-base font-semibold text-gray-900">
                        Jatuh Tempo: {{ \Carbon\Carbon::parse($r->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                        @if($r->status)
                            <span class="{{ $r->status['color'] }} text-xs font-medium me-2 px-2.5 py-0.5 rounded ms-3">{{ $r->status['label'] }}</span>
                        @endif
                    </h3>
                    
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
