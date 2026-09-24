@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
<!-- Page Heading -->
<header class="mb-8 text-center">
    <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Import Register Poliklinik</h1>
    <p class="mt-2 text-lg text-gray-600">Upload file Excel register poliklinik untuk mengimpor data pasien dan pemeriksaan.</p>
</header>

{{-- Ringkasan Hasil Import --}}
<div id="summary-container">
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
</div>

<!-- Upload Form Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Upload File Excel</h2>
            <p class="mt-1 text-sm text-gray-500">
                File yang diterima: <code class="text-xs bg-gray-100 rounded px-1 py-0.5">.xlsx</code> atau <code class="text-xs bg-gray-100 rounded px-1 py-0.5">.xls</code>, maksimal 10MB.
            </p>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.import.poliklinik.process') }}" method="POST" enctype="multipart/form-data" x-data="uploadForm()" @submit="startUpload">
                @csrf

                <div class="space-y-5">
                    <div x-show="status === 'idle'">
                        <label for="file" class="block text-sm font-medium mb-2">Pilih File Register Poliklinik</label>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls" required @change="handleFileChange"
                            class="block w-full border border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500
                            file:bg-gray-50 file:border-0 file:me-4 file:py-3 file:px-4 file:text-sm file:font-medium file:text-gray-800">
                        @error('file')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Uploading Progress Form -->
                    <div x-show="status !== 'idle'" style="display: none;" class="w-full bg-white border border-gray-200 p-4 rounded-xl shadow-sm">
                      <!-- Uploading File Content -->
                      <div class="mb-4 flex flex-wrap sm:flex-nowrap justify-between items-center gap-3">
                        <div class="flex items-center gap-x-3 min-w-0">
                          <span class="size-10 flex shrink-0 justify-center items-center bg-white border border-gray-200 text-gray-800 rounded-lg shadow-sm">
                            <svg class="shrink-0 size-6" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M15.0243 1.43996H7.08805C6.82501 1.43996 6.57277 1.54445 6.38677 1.73043C6.20077 1.91642 6.09631 2.16868 6.09631 2.43171V6.64796L15.0243 11.856L19.4883 13.7398L23.9523 11.856V6.64796L15.0243 1.43996Z" fill="#21A366"/>
                              <path d="M6.09631 6.64796H15.0243V11.856H6.09631V6.64796Z" fill="#107C41"/>
                              <path d="M22.9605 1.43996H15.0243V6.64796H23.9523V2.43171C23.9523 2.16868 23.8478 1.91642 23.6618 1.73043C23.4758 1.54445 23.2235 1.43996 22.9605 1.43996Z" fill="#33C481"/>
                              <path d="M15.0243 11.856H6.09631V21.2802C6.09631 21.5433 6.20077 21.7955 6.38677 21.9815C6.57277 22.1675 6.82501 22.272 7.08805 22.272H22.9606C23.2236 22.272 23.4759 22.1675 23.6618 21.9815C23.8478 21.7955 23.9523 21.5433 23.9523 21.2802V17.064L15.0243 11.856Z" fill="#185C37"/>
                              <path d="M15.0243 11.856H23.9523V17.064H15.0243V11.856Z" fill="#107C41"/>
                              <path opacity="0.1" d="M12.5446 5.15996H6.09631V19.296H12.5446C12.8073 19.2952 13.0591 19.1904 13.245 19.0046C13.4308 18.8188 13.5355 18.567 13.5363 18.3042V6.1517C13.5355 5.88892 13.4308 5.63712 13.245 5.4513C13.0591 5.26548 12.8073 5.16074 12.5446 5.15996Z" fill="black"/>
                              <path opacity="0.2" d="M11.8006 5.90396H6.09631V20.04H11.8006C12.0633 20.0392 12.3151 19.9344 12.501 19.7486C12.6868 19.5628 12.7915 19.311 12.7923 19.0482V6.8957C12.7915 6.6329 12.6868 6.38114 12.501 6.19532C12.3151 6.0095 12.0633 5.90475 11.8006 5.90396Z" fill="black"/>
                              <path opacity="0.2" d="M11.8006 5.90396H6.09631V18.552H11.8006C12.0633 18.5512 12.3151 18.4464 12.501 18.2606C12.6868 18.0748 12.7915 17.823 12.7923 17.5602V6.8957C12.7915 6.6329 12.6868 6.38114 12.501 6.19532C12.3151 6.0095 12.0633 5.90475 11.8006 5.90396Z" fill="black"/>
                              <path opacity="0.2" d="M11.0566 5.90396H6.09631V18.552H11.0566C11.3193 18.5512 11.5711 18.4464 11.757 18.2606C11.9428 18.0748 12.0475 17.823 12.0483 17.5602V6.8957C12.0475 6.6329 11.9428 6.38114 11.757 6.19532C11.5711 6.0095 11.3193 5.90475 11.0566 5.90396Z" fill="black"/>
                              <path d="M1.13604 5.90396H11.0566C11.3195 5.90396 11.5718 6.00842 11.7578 6.19442C11.9438 6.38042 12.0483 6.63266 12.0483 6.8957V16.8162C12.0483 17.0793 11.9438 17.3315 11.7578 17.5175C11.5718 17.7035 11.3195 17.808 11.0566 17.808H1.13604C0.873012 17.808 0.620754 17.7035 0.434765 17.5175C0.248775 17.3315 0.144287 17.0793 0.144287 16.8162V6.8957C0.144287 6.63266 0.248775 6.38042 0.434765 6.19442C0.620754 6.00842 0.873012 5.90396 1.13604 5.90396Z" fill="#107C41"/>
                              <path d="M2.77283 15.576L5.18041 11.8455L2.9752 8.13596H4.74964L5.95343 10.5071C6.06401 10.7318 6.14015 10.8994 6.18185 11.01H6.19745C6.27683 10.8305 6.35987 10.6559 6.44669 10.4863L7.73309 8.13596H9.36167L7.09991 11.8247L9.41897 15.576H7.68545L6.29489 12.972C6.22943 12.861 6.17387 12.7445 6.12899 12.6238H6.10817C6.06761 12.7419 6.01367 12.855 5.94748 12.9608L4.51676 15.576H2.77283Z" fill="white"/>
                            </svg>
                          </span>
                          <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="fileName">preline-ui.xls</p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="fileSize">7 KB</p>
                          </div>
                        </div>
                        <div class="inline-flex items-center gap-x-2 shrink-0">
                          <span x-show="status === 'uploading'" class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <span class="size-1.5 inline-block bg-blue-600 rounded-full animate-pulse"></span>
                            Memproses...
                          </span>
                          
                          <span x-show="status === 'complete'" style="display: none;" class="relative">
                            <svg class="shrink-0 size-5 text-teal-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                          </span>

                          <span x-show="status === 'error'" style="display: none;" class="relative">
                            <svg class="shrink-0 size-5 text-red-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                          </span>
                        </div>
                      </div>
                      <!-- End Uploading File Content -->

                      <p x-show="status === 'error'" style="display: none;" class="text-sm text-red-600 mb-2 font-medium" x-text="errorMessage"></p>

                      <!-- Progress Bar -->
                      <div class="flex items-center gap-x-3 whitespace-nowrap">
                        <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden" role="progressbar">
                          <div class="flex flex-col justify-center rounded-full overflow-hidden text-xs text-white text-center whitespace-nowrap transition-all duration-300 ease-out" 
                               :class="{
                                  'bg-blue-600': status === 'uploading',
                                  'bg-teal-500': status === 'complete',
                                  'bg-red-500': status === 'error'
                               }"
                               :style="`width: ${progress}%`"></div>
                        </div>
                        <div class="w-10 text-end">
                          <span class="text-sm text-gray-800" x-text="`${progress}%`"></span>
                        </div>
                      </div>
                      <!-- End Progress Bar -->
                      
                      <div class="mt-4 flex justify-end" x-show="status === 'complete' || status === 'error'" style="display: none;">
                          <button type="button" @click="resetForm" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Upload File Lain</button>
                      </div>
                    </div>
                    <!-- End File Uploading Progress Form -->

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

                <div class="mt-6 flex justify-end gap-x-3" x-show="status === 'idle'">
                    <button type="submit" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 min-w-[160px] justify-center" :disabled="!fileName">
                        <span class="inline-flex items-center gap-x-2">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                            Import Sekarang
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('uploadForm', () => ({
        status: 'idle', // idle, uploading, complete, error
        fileName: '',
        fileSize: '',
        progress: 0,
        errorMessage: '',
        
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.fileName = file.name;
                this.fileSize = (file.size / 1024).toFixed(0) + ' KB';
                this.status = 'idle';
            } else {
                this.fileName = '';
                this.fileSize = '';
            }
        },
        
        resetForm() {
            this.status = 'idle';
            this.progress = 0;
            this.errorMessage = '';
            document.getElementById('file').value = '';
            this.fileName = '';
            this.fileSize = '';
        },
        
        async startUpload(e) {
            e.preventDefault();
            if (!this.fileName) return;
            
            this.status = 'uploading';
            this.progress = 0;
            this.errorMessage = '';
            
            // Simulasikan progress cepat lalu melambat (seperti loading server)
            const interval = setInterval(() => {
                if (this.progress < 90) {
                    this.progress += Math.floor(Math.random() * 8) + 2;
                    if (this.progress > 90) this.progress = 90;
                }
            }, 300);
            
            try {
                const form = e.target;
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                clearInterval(interval);
                
                if (response.ok) {
                    this.progress = 100;
                    this.status = 'complete';
                    
                    // Laravel merespon redirect, fetch akan mengikutinya dan mengembalikan HTML
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Ekstrak summary container dan update di halaman saat ini
                    const newSummary = doc.querySelector('#summary-container');
                    if (newSummary) {
                        document.querySelector('#summary-container').innerHTML = newSummary.innerHTML;
                        // Scroll up to see the summary smoothly
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } else if (response.status === 422) {
                    const data = await response.json();
                    this.status = 'error';
                    this.errorMessage = data.errors?.file?.[0] || 'Gagal memvalidasi file Excel.';
                } else {
                    this.status = 'error';
                    this.errorMessage = 'Terjadi kesalahan pada server saat memproses file.';
                }
            } catch (error) {
                clearInterval(interval);
                this.status = 'error';
                this.errorMessage = 'Koneksi terputus. Silakan coba lagi.';
            }
        }
    }))
})
</script>
@endpush
