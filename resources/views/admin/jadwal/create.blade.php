@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                Tambah Jadwal Layanan
            </h2>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.jadwal.store') }}" method="POST">
                @csrf
                
                <div class="space-y-5">
                    <!-- Form Group -->
                    <div>
                        <label for="tanggal" class="block text-sm font-medium mb-2">Tanggal Layanan</label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 border" required>
                        @error('tanggal')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- End Form Group -->

                    <!-- Form Group -->
                    <div>
                        <label for="keterangan" class="block text-sm font-medium mb-2">Keterangan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <textarea id="keterangan" name="keterangan" rows="3" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 border" placeholder="Misal: Prolanis Bulan Agustus (Senam & Edukasi)">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- End Form Group -->
                </div>

                <div class="mt-6 flex justify-end gap-x-3">
                    <a href="{{ route('admin.jadwal.index') }}" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- End Card -->
</div>
@endsection
