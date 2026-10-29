@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<header class="mb-8">
    <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Dashboard</h1>
    <p class="mt-2 text-lg text-gray-600">Selamat datang kembali, {{ auth()->user()->nama }}.</p>
</header>
<!-- End Page Heading -->

<!-- Stats Grid -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <!-- Card 1 -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="p-4 md:p-5">
            <div class="flex justify-between items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total Pasien</p>
                <div class="inline-flex items-center justify-center size-8 rounded-full bg-blue-100 text-blue-600">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalPasien ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <!-- End Card -->

    <!-- Card 2 -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="p-4 md:p-5">
            <div class="flex justify-between items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500">Petugas Aktif</p>
                <div class="inline-flex items-center justify-center size-8 rounded-full bg-teal-100 text-teal-600">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>
                </div>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalPetugas ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <!-- End Card -->

    <!-- Card 3 -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="p-4 md:p-5">
            <div class="flex justify-between items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500">Consent Menunggu</p>
                <div class="inline-flex items-center justify-center size-8 rounded-full bg-amber-100 text-amber-600">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                </div>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-2xl font-bold text-gray-800">{{ number_format($pendingConsent ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <!-- End Card -->

    <!-- Card 4 -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="p-4 md:p-5">
            <div class="flex justify-between items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total Jadwal Layanan</p>
                <div class="inline-flex items-center justify-center size-8 rounded-full bg-purple-100 text-purple-600">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                </div>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalJadwal ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <!-- End Card -->
</div>
<!-- End Stats Grid -->

<!-- Layout 2 Columns -->
<div class="grid lg:grid-cols-1 gap-4 sm:gap-6">
    <!-- Jadwal Terdekat (Dibuat full width) -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl lg:col-span-1">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Jadwal Prolanis Terdekat</h2>
            @if(auth()->user()?->peran === 'admin')
            <a href="{{ route('admin.jadwal.index') }}" class="text-sm font-medium text-blue-600 hover:underline">Lihat Semua</a>
            @endif
        </div>
        <div class="p-6">
            @if($upcomingJadwal->isEmpty())
                <div class="text-center py-6 text-gray-500">
                    <svg class="size-10 mx-auto text-gray-300 mb-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <p class="text-sm">Tidak ada jadwal terdekat di bulan ini.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($upcomingJadwal as $jadwal)
                    <div class="flex gap-x-4 relative">
                        <div class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-3.5 after:w-px after:-translate-x-[0.5px] after:bg-gray-200">
                            <div class="relative z-10 size-7 flex justify-center items-center">
                                <div class="size-2 rounded-full bg-blue-500"></div>
                            </div>
                        </div>
                        <div class="grow pt-0.5 pb-4">
                            <h3 class="flex gap-x-1.5 font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $jadwal->keterangan ?: 'Jadwal Rutin Prolanis' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <!-- End Jadwal Terdekat -->

    {{-- Aktivitas Terakhir (Disembunyikan Sesuai Permintaan)
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Log Aktivitas Terbaru</h2>
            <a href="{{ route('admin.log.index') }}" class="text-sm font-medium text-blue-600 hover:underline">Semua Log</a>
        </div>
        ...
    </div>
    --}}
</div>
<!-- End Layout -->

@endsection
