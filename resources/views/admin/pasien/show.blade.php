@extends('layouts.app')

@section('content')
<!-- Page Heading with Back Button -->
<header class="mb-8 flex items-center gap-x-4">
    <a href="{{ route('admin.pasien.index') }}" class="inline-flex justify-center items-center size-10 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    </a>
    <div>
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Detail Pasien</h1>
        <p class="mt-1 text-sm text-gray-600">Informasi profil dan riwayat pemeriksaan pasien.</p>
    </div>
</header>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Profil Pasien Card -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm flex flex-col h-full">
            <div class="p-4 md:p-6">
                <div class="flex items-center gap-x-4 mb-6">
                    <div class="inline-flex justify-center items-center size-[62px] rounded-full bg-blue-100 text-blue-800">
                        <span class="text-2xl font-bold">{{ strtoupper(substr($pasien->nama, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">{{ $pasien->nama }}</h2>
                        <p class="text-sm text-gray-500">No RM: <span class="font-medium text-gray-800">{{ $pasien->no_rm ?? '-' }}</span></p>
                    </div>
                </div>

                <dl class="space-y-4">
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">NIK</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->nik }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Tgl Lahir</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">
                            {{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('d F Y') : '-' }}
                            @if($pasien->tanggal_lahir)
                                <span class="text-gray-500 font-normal">({{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->age }} th)</span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">
                            @if($pasien->jenis_kelamin == 'L') Laki-laki @elseif($pasien->jenis_kelamin == 'P') Perempuan @else - @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">No HP</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->no_hp ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">BPJS</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->no_bpjs ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Pekerjaan</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->pekerjaan ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Pendidikan</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->pendidikan ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->alamat ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Riwayat Kunjungan -->
    <div class="lg:col-span-2 space-y-6">
        <h3 class="text-lg font-bold text-gray-800">Riwayat Pemeriksaan</h3>
        
        @forelse($pasien->pemeriksaans as $pemeriksaan)
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <!-- Header Kunjungan -->
            <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-x-2">
                    <svg class="size-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($pemeriksaan->tanggal_periksa)->translatedFormat('l, d F Y') }}</span>
                </div>
                @if($pemeriksaan->nakes)
                <span class="text-sm text-gray-600">Dokter: <span class="font-medium text-gray-800">{{ $pemeriksaan->nakes->nama }}</span></span>
                @endif
            </div>
            
            <div class="p-4 md:p-5 space-y-4">
                <!-- TTV Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">BB / TB</span>
                        <span class="block text-sm font-medium text-gray-800">
                            {{ $pemeriksaan->berat_badan ?? '-' }} kg / {{ $pemeriksaan->tinggi_badan ?? '-' }} cm
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">Tensi</span>
                        <span class="block text-sm font-medium text-gray-800">
                            {{ $pemeriksaan->tensi_sistol ?? '-' }} / {{ $pemeriksaan->tensi_diastol ?? '-' }} mmHg
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">Heart Rate</span>
                        <span class="block text-sm font-medium text-gray-800">
                            {{ $pemeriksaan->heart_rate ?? '-' }} bpm
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">Suhu</span>
                        <span class="block text-sm font-medium text-gray-800">
                            {{ $pemeriksaan->temperature ?? '-' }} °C
                        </span>
                    </div>
                </div>

                <!-- Keluhan & Diagnosa -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-1">Keluhan</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 rounded p-2 border border-gray-100 min-h-[60px]">{{ $pemeriksaan->keluhan ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-1">Diagnosa</h4>
                        <div class="text-sm text-gray-600 bg-gray-50 rounded p-2 border border-gray-100 min-h-[60px]">
                            @if($pemeriksaan->kode_icd)
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-1.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                    {{ $pemeriksaan->kode_icd }}
                                </span>
                                <br>
                            @endif
                            {{ $pemeriksaan->nama_diagnosa ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Obat -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-1">
                        <svg class="size-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        Obat yang Diberikan
                    </h4>
                    @if($pemeriksaan->pemberianObats->count() > 0)
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($pemeriksaan->pemberianObats as $obat)
                                <li class="inline-flex items-center gap-x-2 py-2 px-3 text-sm font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    {{ $obat->nama_obat }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 italic">Tidak ada data obat untuk kunjungan ini.</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-8 text-center">
            <svg class="mx-auto size-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-800">Belum Ada Riwayat</h3>
            <p class="mt-1 text-sm text-gray-500">Pasien ini belum memiliki riwayat pemeriksaan poliklinik.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
