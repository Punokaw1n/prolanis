@extends('layouts.app-pasien')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-14rem)]">
    <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Selamat Datang</h1>
            <p class="mt-2 text-sm text-gray-600">Silakan login untuk melihat riwayat kesehatan Anda.</p>
        </div>

        <form action="{{ route('pasien.login.process') }}" method="POST">
            @csrf
            
            <div class="space-y-5">
                <!-- NIK -->
                <div>
                    <label for="nik" class="block text-sm font-medium mb-2 text-gray-700">Nomor Induk Kependudukan (NIK)</label>
                    <div class="relative">
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:opacity-50 disabled:pointer-events-none @error('nik') border-red-500 @enderror" placeholder="Masukkan 16 digit NIK" required autofocus autocomplete="off">
                    </div>
                    @error('nik')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium mb-2 text-gray-700">Tanggal Lahir</label>
                    <div class="relative">
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:opacity-50 disabled:pointer-events-none @error('tanggal_lahir') border-red-500 @enderror" required>
                    </div>
                    @error('tanggal_lahir')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all">
                        Masuk Portal
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500 bg-gray-50 p-4 rounded-lg">
            <svg class="inline-block size-4 mr-1 text-emerald-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Data Anda dijamin keamanannya dan hanya dapat diakses oleh Anda sendiri menggunakan NIK yang terdaftar.
        </div>
    </div>
</div>
@endsection
