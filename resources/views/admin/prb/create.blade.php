@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8">
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Daftar Pasien PRB</h1>
        <p class="mt-2 text-lg text-gray-600">Daftarkan pasien ke dalam Program Rujuk Balik.</p>
    </header>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 mb-6" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" x-data="prbRegistration()">
        <form action="{{ route('admin.prb.store') }}" method="POST">
            @csrf
            
            <!-- Step 1: Input NIK -->
            <div class="mb-6">
                <label for="nik" class="block text-sm font-medium mb-2">Nomor Induk Kependudukan (NIK) <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    <input type="number" id="nik" name="nik" x-model="nik" @input="resetState" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan 16 digit NIK..." required maxlength="16" pattern="\d{16}">
                    <button type="button" @click="checkNik" :disabled="nik.length !== 16 || isChecking" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-gray-800 text-white hover:bg-gray-900 disabled:opacity-50 min-w-[120px] justify-center">
                        <span x-show="!isChecking">Cari Pasien</span>
                        <span x-show="isChecking" class="animate-spin inline-block size-4 border-[3px] border-current border-t-transparent text-white rounded-full" role="status" aria-label="loading"></span>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Ketik 16 digit NIK lalu klik "Cari Pasien" untuk mengecek apakah pasien sudah ada di sistem.</p>
            </div>

            <!-- Existing Patient Found -->
            <template x-if="status === 'found'">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-6">
                    <h3 class="text-blue-800 font-semibold mb-2">Pasien Ditemukan!</h3>
                    <p class="text-sm text-blue-800 mb-4">Pasien berikut sudah terdaftar di sistem. Anda tinggal menandainya sebagai peserta PRB.</p>
                    <div class="grid grid-cols-2 gap-4 text-sm bg-white p-4 rounded-md border border-blue-100">
                        <div>
                            <span class="block text-gray-500 mb-1">Nama Pasien</span>
                            <span class="font-medium text-gray-800" x-text="patientData.nama"></span>
                        </div>
                        <div>
                            <span class="block text-gray-500 mb-1">No. RM</span>
                            <span class="font-medium text-gray-800" x-text="patientData.no_rm || '-'"></span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Error: Already in PRB -->
            <template x-if="status === 'already_prb'">
                <div class="bg-red-50 border border-red-200 rounded-lg p-5 mb-6">
                    <div class="flex items-center gap-3">
                        <svg class="flex-shrink-0 size-5 text-red-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <div>
                            <h3 class="text-red-800 font-semibold">Pendaftaran Ditolak</h3>
                            <p class="text-sm text-red-800 mt-1">Pasien bernama <strong x-text="patientData.nama"></strong> dengan NIK tersebut sudah terdaftar aktif di Program Rujuk Balik (PRB).</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- New Patient Form -->
            <template x-if="status === 'not_found'">
                <div class="border border-gray-200 rounded-lg p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-100">
                        <h3 class="text-gray-800 font-semibold">Pasien Belum Terdaftar</h3>
                        <p class="text-sm text-gray-600">Lengkapi formulir di bawah ini untuk mendaftarkan pasien baru sekaligus mendaftarkannya ke PRB.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">Nomor Rekam Medis</label>
                            <input type="text" name="no_rm" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Nomor Kartu BPJS</label>
                            <input type="text" name="no_bpjs" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="13 digit angka...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Nomor Handphone (WhatsApp)</label>
                            <input type="number" name="no_hp" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="08xxx...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select name="jenis_kelamin" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="" disabled selected>Pilih...</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-2">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end gap-x-2">
                <a href="{{ route('admin.prb.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50">
                    Batal
                </a>
                <button type="submit" x-show="status === 'found' || status === 'not_found'" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
                    Daftarkan sebagai Peserta PRB
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function prbRegistration() {
    return {
        nik: '',
        status: 'idle', // idle, found, not_found, already_prb
        isChecking: false,
        patientData: {},
        
        resetState() {
            this.status = 'idle';
            this.patientData = {};
        },

        async checkNik() {
            if (this.nik.length !== 16) return;
            
            this.isChecking = true;
            this.resetState();

            try {
                const response = await fetch(`{{ route('admin.prb.check-nik') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ nik: this.nik })
                });

                const result = await response.json();
                
                if (result.found) {
                    this.patientData = result.data;
                    this.status = result.is_prb ? 'already_prb' : 'found';
                } else {
                    this.status = 'not_found';
                }
            } catch (error) {
                console.error('Error checking NIK:', error);
                alert('Terjadi kesalahan saat mengecek NIK. Silakan coba lagi.');
            } finally {
                this.isChecking = false;
            }
        }
    }
}
</script>
@endpush
@endsection
