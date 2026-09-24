@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<header class="mb-8">
    <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Dashboard Admin</h1>
    <p class="mt-2 text-lg text-gray-600">Selamat datang kembali, {{ auth()->user()->nama }}.</p>
</header>
<!-- End Page Heading -->

<!-- Grid -->
<div class="grid sm:grid-cols-2 gap-4 sm:gap-6">
    <!-- Card 1 -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="p-4 md:p-5">
            <div class="flex items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total Pasien</p>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-xl sm:text-2xl font-medium text-gray-800">{{ number_format($totalPasien ?? 0) }}</h3>
            </div>
            <div class="mt-4">
                <a class="inline-flex items-center gap-x-1 text-sm text-blue-600 decoration-2 hover:underline font-medium" href="{{ route('admin.pasien.index') }}">
                    Lihat Data Pasien
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
        </div>
    </div>
    <!-- End Card -->

    <!-- Card 2 -->
    <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="p-4 md:p-5">
            <div class="flex items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500">Jadwal Layanan</p>
            </div>
            <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-xl sm:text-2xl font-medium text-gray-800">{{ number_format($totalJadwal ?? 0) }}</h3>
            </div>
            <div class="mt-4">
                <a class="inline-flex items-center gap-x-1 text-sm text-blue-600 decoration-2 hover:underline font-medium" href="{{ route('admin.jadwal.index') }}">
                    Kelola Jadwal
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
        </div>
    </div>
    <!-- End Card -->
</div>
<!-- End Grid -->

@endsection
