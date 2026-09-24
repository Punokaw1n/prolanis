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
                        <div class="relative">
                            <input type="text" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" class="py-3 ps-10 pe-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 border" placeholder="Pilih tanggal">
                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5">
                                <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            </div>
                        </div>
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

                    <!-- Checkbox -->
                    <div class="flex items-center mt-4">
                        <div class="flex">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="shrink-0 mt-0.5 border-gray-200 rounded text-blue-600 focus:ring-blue-500" {{ old('is_active') ? 'checked' : '' }}>
                        </div>
                        <div class="ms-3">
                            <label for="is_active" class="text-sm text-gray-800 font-medium">Jadikan sebagai jadwal aktif</label>
                            <p class="text-xs text-gray-500">Jadwal aktif akan digunakan di pesan WhatsApp.</p>
                        </div>
                    </div>
                    <!-- End Checkbox -->
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

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#tanggal", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d F Y",
            minDate: "today", // opsional: mencegah pilih tanggal masa lalu
        });
    });
</script>
@endpush
