@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<header class="mb-8">
    <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Dashboard</h1>
    <p class="mt-2 text-lg text-gray-600">Selamat datang kembali, {{ auth()->user()->nama }}.</p>
</header>
<!-- End Page Heading -->

{{-- 
    KARTU STATISTIK & JADWAL DISEMBUNYIKAN SEMENTARA SESUAI PERMINTAAN
--}}
{{-- 
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    ...
</div>
--}}
@endsection
