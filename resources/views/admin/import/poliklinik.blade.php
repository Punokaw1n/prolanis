@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<header class="mb-8">
    <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Import Register Poliklinik</h1>
    <p class="mt-2 text-lg text-gray-600">Upload file Excel register poliklinik untuk mengimpor data pasien dan pemeriksaan.</p>
</header>

{{-- Ringkasan Hasil Import --}}
@if(session('import_summary'))
@php $s = session('import_summary'); @endphp
<div class="mb-6 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Ringkasan Hasil Import</h3>
    </div>
    <div class="p-6">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pasien Baru -->
            <div class="flex flex-col bg-teal-50 border border-teal-200 rounded-lg p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Pasien Baru</p>
                <h3 class="mt-1 text-2xl font-bold text-teal-800">{{ $s['pasien_baru'] }}</h3>
            </div>
            <!-- Pasien Existing -->
            <div class="flex flex-col bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Pasien Sudah Ada</p>
                <h3 class="mt-1 text-2xl font-bold text-blue-800">{{ $s['pasien_existing'] }}</h3>
            </div>
            <!-- Kunjungan Baru -->
            <div class="flex flex-col bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-600">Kunjungan Baru</p>
                <h3 class="mt-1 text-2xl font-bold text-green-800">{{ $s['kunjungan_baru'] }}</h3>
            </div>
            <!-- Dilewati / Gagal -->
            <div class="flex flex-col bg-amber-50 border border-amber-200 rounded-lg p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Dilewati (Duplikat)</p>
                <h3 class="mt-1 text-2xl font-bold text-amber-800">{{ $s['kunjungan_skip'] }}</h3>
            </div>
        </div>

        @if($s['baris_gagal'] > 0)
        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm font-semibold text-red-700 mb-2">{{ $s['baris_gagal'] }} baris gagal diproses:</p>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach(array_slice($s['errors'], 0, 20) as $error)
                    <li>{{ $error }}</li>
                @endforeach
                @if(count($s['errors']) > 20)
                    <li class="text-red-500 italic">...dan {{ count($s['errors']) - 20 }} error lainnya.</li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</div>
@endif

<!-- Upload Form Card -->
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Upload File Excel</h2>
            <p class="mt-1 text-sm text-gray-500">
                File yang diterima: <code class="text-xs bg-gray-100 rounded px-1 py-0.5">.xlsx</code> atau <code class="text-xs bg-gray-100 rounded px-1 py-0.5">.xls</code>, maksimal 10MB.
            </p>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.import.poliklinik.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="file" class="block text-sm font-medium mb-2">Pilih File Register Poliklinik</label>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls"
                            class="block w-full border border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500
                            file:bg-gray-50 file:border-0 file:me-4 file:py-3 file:px-4 file:text-sm file:font-medium file:text-gray-800">
                        @error('file')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="size-5 text-blue-600 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            </div>
                            <div class="ms-3">
                                <h3 class="text-sm font-semibold text-blue-800">Informasi Import</h3>
                                <ul class="mt-1 text-sm text-blue-700 list-disc list-inside space-y-1">
                                    <li>Pasien dikenali lewat NIK — jika sudah ada, tidak akan dibuat duplikat.</li>
                                    <li>Kunjungan dikenali lewat NIK + tanggal — duplikat akan dilewati.</li>
                                    <li>Data lama tidak akan terhapus atau tertimpa.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-x-3">
                    <button type="submit" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
