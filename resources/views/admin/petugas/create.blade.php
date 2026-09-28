@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Petugas</h1>
        <p class="text-sm text-gray-500 mt-1">Masukkan data lengkap untuk membuat akun petugas baru.</p>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm max-w-3xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl text-center">
        <h2 class="text-lg font-semibold text-gray-800">Form Data Petugas</h2>
    </div>
    
    <div class="p-6">
        @if ($errors->any())
        <div class="mb-5 bg-red-50 border-s-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} kesalahan</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('admin.petugas.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                <!-- Nama Lengkap -->
                <div class="sm:col-span-2">
                    <label for="nama" class="block text-sm font-medium mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required placeholder="Contoh: dr. Budi Santoso, Sp.PD">
                </div>
                
                <!-- NIK -->
                <div>
                    <label for="nik" class="block text-sm font-medium mb-2">NIK <span class="text-red-500">*</span></label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" placeholder="Nomor Induk Kependudukan (16 digit)" required inputmode="numeric" pattern="[0-9]{16}" maxlength="16" title="NIK harus terdiri dari 16 digit angka" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <p class="text-xs text-gray-500 mt-2">Wajib 16 digit angka. Dapat digunakan sebagai username saat login.</p>
                </div>

                <!-- NIP -->
                <div>
                    <label for="nip" class="block text-sm font-medium mb-2">NIP <span class="text-red-500">*</span></label>
                    <input type="text" id="nip" name="nip" value="{{ old('nip') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" placeholder="Nomor Induk Pegawai" required inputmode="numeric" pattern="[0-9]*" title="NIP harus berupa angka" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <p class="text-xs text-gray-500 mt-2">Dapat digunakan sebagai username saat login.</p>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                    <p class="text-xs text-blue-600 mt-2 font-medium">Otomatis menjadi password default (Format: DDMMYYYY)</p>
                </div>

                <!-- No HP -->
                <div>
                    <label for="no_hp" class="block text-sm font-medium mb-2">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" placeholder="Contoh: 08123456789" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <!-- Jabatan -->
                <div>
                    <label for="jabatan" class="block text-sm font-medium mb-2">Jabatan Profesi <span class="text-red-500">*</span></label>
                    <select id="jabatan" name="jabatan" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                        <option value="" disabled selected>Pilih Jabatan</option>
                        <option value="Dokter Umum" {{ old('jabatan') == 'Dokter Umum' ? 'selected' : '' }}>Dokter Umum</option>
                        <option value="Dokter Spesialis" {{ old('jabatan') == 'Dokter Spesialis' ? 'selected' : '' }}>Dokter Spesialis</option>
                        <option value="Perawat" {{ old('jabatan') == 'Perawat' ? 'selected' : '' }}>Perawat</option>
                        <option value="Apoteker" {{ old('jabatan') == 'Apoteker' ? 'selected' : '' }}>Apoteker</option>
                        <option value="Bidan" {{ old('jabatan') == 'Bidan' ? 'selected' : '' }}>Bidan</option>
                        <option value="Staf Admin" {{ old('jabatan') == 'Staf Admin' ? 'selected' : '' }}>Staf Admin</option>
                        <option value="Lainnya" {{ old('jabatan') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <!-- Peran (Akses Sistem) -->
                <div>
                    <label for="peran" class="block text-sm font-medium mb-2">Hak Akses Sistem <span class="text-red-500">*</span></label>
                    <select id="peran" name="peran" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                        <option value="petugas" {{ old('peran') == 'petugas' ? 'selected' : '' }}>Petugas (Standar)</option>
                        <option value="admin" {{ old('peran') == 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh)</option>
                    </select>
                </div>

                <!-- Alamat -->
                <div class="sm:col-span-2">
                    <label for="alamat" class="block text-sm font-medium mb-2">Alamat Lengkap <span class="text-xs text-gray-500 font-normal">(Opsional)</span></label>
                    <textarea id="alamat" name="alamat" rows="3" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" placeholder="Alamat tempat tinggal...">{{ old('alamat') }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-x-2">
                <a href="{{ route('admin.petugas.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none shadow-sm">
                    Simpan Petugas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
