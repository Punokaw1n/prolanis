@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center py-20">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Selamat Datang di Sistem Informasi Prolanis</h2>
    <p class="text-gray-600 mb-8 text-center max-w-2xl">
        Aplikasi ini digunakan untuk memantau data pasien Prolanis dari rekam medis poliklinik dan data farmasi/apotek.
    </p>
    
    <!-- Preline Button for Test -->
    <button type="button" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
        Test Preline Button
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="m12 5 7 7-7 7"></path>
        </svg>
    </button>
</div>
@endsection
