@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Petugas</h1>
            <p class="text-sm text-gray-500 mt-1">Ubah data informasi akun dan profil kepegawaian {{ $petugas->nama }}.</p>
        </div>
        <div>
            <a href="{{ route('admin.petugas.index') }}" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm max-w-4xl mx-auto overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
            <svg class="size-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
            Perbarui Data Petugas & Pegawai
        </h2>
        <span class="text-xs text-gray-500 font-medium">* Wajib diisi</span>
    </div>
    
    <div class="p-6">
        @if ($errors->any())
        <div class="mb-6 bg-red-50 border-s-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ms-3">
                    <h3 class="text-sm font-semibold text-red-800">Terdapat {{ $errors->count() }} kesalahan input:</h3>
                    <div class="mt-2 text-xs text-red-700">
                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('admin.petugas.update', $petugas->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Bagian 1: Data Identitas Pegawai -->
            <div class="mb-6 pb-6 border-b border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-4 flex items-center gap-1.5">
                    <span class="size-2 rounded-full bg-blue-600"></span>
                    1. Identitas Pokok & Pribadi
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2">
                        <label for="nama" class="block text-sm font-medium mb-1.5 text-gray-800">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama', $petugas->nama) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                    </div>
                    
                    <!-- NIK -->
                    <div>
                        <label for="nik" class="block text-sm font-medium mb-1.5 text-gray-800">NIK (16 Digit) <span class="text-red-500">*</span></label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik', $petugas->nik) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required inputmode="numeric" pattern="[0-9]{16}" maxlength="16" title="NIK harus terdiri dari 16 digit angka" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <p class="text-[11px] text-gray-500 mt-1">Digunakan sebagai username login.</p>
                    </div>

                    <!-- NIP -->
                    <div>
                        <label for="nip" class="block text-sm font-medium mb-1.5 text-gray-800">NIP <span class="text-xs text-gray-400 font-normal">(Opsional / Khusus PNS)</span></label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip', $petugas->nip) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" placeholder="Kosongkan jika Non-PNS" inputmode="numeric" pattern="[0-9]*" title="NIP harus berupa angka" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium mb-1.5 text-gray-800">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('jenis_kelamin', $petugas->pegawai?->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $petugas->pegawai?->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label for="tanggal_lahir" class="block text-sm font-medium mb-1.5 text-gray-800">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $petugas->tanggal_lahir) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                    </div>

                    <!-- Pendidikan Terakhir -->
                    <div class="sm:col-span-2">
                        <label for="pendidikan" class="block text-sm font-medium mb-1.5 text-gray-800">Pendidikan Terakhir <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" id="pendidikan" name="pendidikan" list="daftar-pendidikan" value="{{ old('pendidikan', $petugas->pegawai?->pendidikan) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" placeholder="Contoh: S1 Kedokteran, D3 Keperawatan, S1 Farmasi">
                        <datalist id="daftar-pendidikan">
                            <option value="S2 Kedokteran">
                            <option value="S1 Kedokteran">
                            <option value="S1 Kedokteran Gigi">
                            <option value="S1 Keperawatan">
                            <option value="D3 Keperawatan">
                            <option value="D3 Kebidanan">
                            <option value="S1 Farmasi / Profesi Apoteker">
                            <option value="D3 Farmasi / TTK">
                            <option value="D3 Analis Kesehatan / TLM">
                            <option value="S1 Gizi / Nutrisionis">
                            <option value="D3 Sanitasi Lingkungan">
                            <option value="D3 Rekam Medis (RMIK)">
                            <option value="S1 Kesehatan Masyarakat">
                            <option value="S1 Psikologi">
                            <option value="S1 Akuntansi / Ekonomi">
                            <option value="SMA / SMK Sederajat">
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Jabatan & Hak Akses Sistem -->
            <div class="mb-6 pb-6 border-b border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-4 flex items-center gap-1.5">
                    <span class="size-2 rounded-full bg-blue-600"></span>
                    2. Jabatan Puskesmas & Hak Akses
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5">
                    <!-- Jabatan (Optgroup Lengkap Sesuai Dataset + Custom Text) -->
                    <div x-data="{ 
                        selectedJabatan: '{{ old('jabatan', $petugas->jabatan) }}',
                        customJabatan: '',
                        get isCustom() {
                            const standard = [
                                'Kepala Puskesmas', 'PJ TU', 'Analis Data dan Informasi', 'Staf Admin', 'Akuntan',
                                'Dokter Umum', 'Dokter Gigi', 'Dokter Spesialis',
                                'Perawat', 'Bidan', 'Pembantu Paramedis',
                                'Apoteker', 'Asisten Apoteker', 'Pranata Laboratorium Kesehatan',
                                'Nutrisionis', 'Sanitasi Lingkungan', 'Perekam Medis', 'Administrator Kesehatan', 
                                'Tenaga Promosi Kesehatan Dan Ilmu Perilaku', 'Fisioterapis', 'Psikolog', 'Nakestrad',
                                'Driver', 'Tenaga Keamanan', 'Tenaga Kebersihan'
                            ];
                            return this.selectedJabatan === '__custom__' || (this.selectedJabatan && !standard.includes(this.selectedJabatan));
                        },
                        init() {
                            const standard = [
                                'Kepala Puskesmas', 'PJ TU', 'Analis Data dan Informasi', 'Staf Admin', 'Akuntan',
                                'Dokter Umum', 'Dokter Gigi', 'Dokter Spesialis',
                                'Perawat', 'Bidan', 'Pembantu Paramedis',
                                'Apoteker', 'Asisten Apoteker', 'Pranata Laboratorium Kesehatan',
                                'Nutrisionis', 'Sanitasi Lingkungan', 'Perekam Medis', 'Administrator Kesehatan', 
                                'Tenaga Promosi Kesehatan Dan Ilmu Perilaku', 'Fisioterapis', 'Psikolog', 'Nakestrad',
                                'Driver', 'Tenaga Keamanan', 'Tenaga Kebersihan'
                            ];
                            if (this.selectedJabatan && !standard.includes(this.selectedJabatan)) {
                                this.customJabatan = this.selectedJabatan;
                                this.selectedJabatan = '__custom__';
                            }
                        }
                    }">
                        <label for="jabatan_select" class="block text-sm font-medium mb-1.5 text-gray-800">Jabatan Profesi <span class="text-red-500">*</span></label>
                        <select id="jabatan_select" x-model="selectedJabatan" :name="isCustom ? '' : 'jabatan'" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                            <optgroup label="Pimpinan & Manajemen">
                                <option value="Kepala Puskesmas">Kepala Puskesmas</option>
                                <option value="PJ TU">PJ TU (Tata Usaha)</option>
                                <option value="Analis Data dan Informasi">Analis Data dan Informasi</option>
                                <option value="Staf Admin">Staf Admin</option>
                                <option value="Akuntan">Akuntan / Keuangan</option>
                            </optgroup>
                            <optgroup label="Tenaga Medis & Dokter">
                                <option value="Dokter Umum">Dokter Umum</option>
                                <option value="Dokter Gigi">Dokter Gigi</option>
                                <option value="Dokter Spesialis">Dokter Spesialis</option>
                            </optgroup>
                            <optgroup label="Keperawatan & Kebidanan">
                                <option value="Perawat">Perawat</option>
                                <option value="Bidan">Bidan</option>
                                <option value="Pembantu Paramedis">Pembantu Paramedis</option>
                            </optgroup>
                            <optgroup label="Kefarmasian & Laboratorium">
                                <option value="Apoteker">Apoteker</option>
                                <option value="Asisten Apoteker">Asisten Apoteker / TTK</option>
                                <option value="Pranata Laboratorium Kesehatan">Pranata Laboratorium Kesehatan</option>
                            </optgroup>
                            <optgroup label="Kesehatan Masyarakat & Penunjang">
                                <option value="Nutrisionis">Nutrisionis / Gizi</option>
                                <option value="Sanitasi Lingkungan">Sanitasi Lingkungan / Sanitarian</option>
                                <option value="Perekam Medis">Perekam Medis (RMIK)</option>
                                <option value="Administrator Kesehatan">Administrator Kesehatan</option>
                                <option value="Tenaga Promosi Kesehatan Dan Ilmu Perilaku">Promosi Kesehatan (Promkes)</option>
                                <option value="Fisioterapis">Fisioterapis</option>
                                <option value="Psikolog">Psikolog Klinis</option>
                                <option value="Nakestrad">Nakestrad (Tradisional)</option>
                            </optgroup>
                            <optgroup label="Operasional & Pendukung">
                                <option value="Driver">Driver / Pengemudi Ambulans</option>
                                <option value="Tenaga Keamanan">Tenaga Keamanan</option>
                                <option value="Tenaga Kebersihan">Tenaga Kebersihan</option>
                            </optgroup>
                            <optgroup label="Lainnya">
                                <option value="__custom__">Lainnya (Ketik Manual...)</option>
                            </optgroup>
                        </select>

                        <div x-show="isCustom" x-cloak class="mt-2.5">
                            <input type="text" x-model="customJabatan" :name="isCustom ? 'jabatan' : ''" :required="isCustom" class="py-2.5 px-4 block w-full border-blue-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border bg-blue-50/30" placeholder="Ketik nama jabatan spesifik...">
                            <p class="text-[11px] text-gray-500 mt-1">Masukkan nama jabatan spesifik jika tidak ada dalam pilihan daftar.</p>
                        </div>
                    </div>

                    <!-- Peran (Hak Akses Sistem) -->
                    <div>
                        <label for="peran" class="block text-sm font-medium mb-1.5 text-gray-800">Hak Akses Sistem <span class="text-red-500">*</span></label>
                        <select id="peran" name="peran" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                            <option value="petugas" {{ old('peran', $petugas->peran) == 'petugas' ? 'selected' : '' }}>Petugas (Standar)</option>
                            <option value="admin" {{ old('peran', $petugas->peran) == 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Kontak & Informasi Pelengkap -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-4 flex items-center gap-1.5">
                    <span class="size-2 rounded-full bg-blue-600"></span>
                    3. Kontak & Domisili
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5">
                    <!-- No HP -->
                    <div>
                        <label for="no_hp" class="block text-sm font-medium mb-1.5 text-gray-800">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $petugas->no_hp) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1.5 text-gray-800">Email Pegawai <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $petugas->email) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border">
                    </div>

                    <!-- No BPJS -->
                    <div>
                        <label for="no_bpjs" class="block text-sm font-medium mb-1.5 text-gray-800">No. Kartu BPJS <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" id="no_bpjs" name="no_bpjs" value="{{ old('no_bpjs', $petugas->pegawai?->no_bpjs) }}" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" placeholder="Contoh: 0001234567890" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <!-- Alamat Lengkap -->
                    <div class="sm:col-span-2">
                        <label for="alamat" class="block text-sm font-medium mb-1.5 text-gray-800">Alamat Tempat Tinggal / Domisili <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <textarea id="alamat" name="alamat" rows="2" class="py-2.5 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border">{{ old('alamat', $petugas->alamat ?: $petugas->pegawai?->alamat) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-100 flex items-center justify-end gap-x-3">
                <a href="{{ route('admin.petugas.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
