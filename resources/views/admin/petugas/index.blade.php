@extends('layouts.app')

@section('content')
<div x-data="{ importModalOpen: false }">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Petugas</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data dokter, perawat, dan staf admin klinik.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="importModalOpen = true" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 transition-colors cursor-pointer">
                <svg class="flex-shrink-0 size-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" x2="12" y1="3" y2="15"/>
                </svg>
                Import Pegawai
            </button>

            <a href="{{ route('admin.petugas.create') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none transition-colors shadow-sm">
                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Tambah Petugas
            </a>
        </div>
    </div>

    {{-- Ringkasan Hasil Import --}}
    @if(session('import_summary'))
        @php 
            $s = session('import_summary'); 
            $isNoNewData = ($s['petugas_baru'] === 0 && $s['petugas_existing'] > 0);
        @endphp
        <div class="mb-6 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 {{ $isNoNewData ? 'bg-amber-50/70' : 'bg-emerald-50/60' }} flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    @if($isNoNewData)
                        <svg class="size-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        Semua Data Pegawai Sudah Terdaftar (Tidak Ada Akun Baru)
                    @else
                        <svg class="size-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Hasil Import Data Pegawai Selesai
                    @endif
                </h3>
                <span class="text-xs {{ $isNoNewData ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }} font-bold px-2.5 py-1 rounded-full">
                    {{ $isNoNewData ? 'Tersinkron' : 'Selesai' }}
                </span>
            </div>
            <div class="p-5">
                @if($isNoNewData)
                    <div class="mb-4 text-xs text-amber-800 bg-amber-50/60 border border-amber-200/80 rounded-lg p-3">
                        <span class="font-bold">Informasi:</span> Seluruh data dalam file Excel sudah terdaftar di database. Sistem tidak membuat akun ganda, dan data profil telah disinkronkan.
                    </div>
                @endif
                <div class="grid sm:grid-cols-3 gap-4">
                    <div class="{{ $s['petugas_baru'] > 0 ? 'bg-emerald-50/70 border-emerald-200' : 'bg-gray-50 border-gray-200' }} border rounded-xl p-3.5">
                        <p class="text-xs font-bold uppercase tracking-wider {{ $s['petugas_baru'] > 0 ? 'text-emerald-700' : 'text-gray-500' }}">Akun Pegawai Baru</p>
                        <p class="text-2xl font-black {{ $s['petugas_baru'] > 0 ? 'text-emerald-900' : 'text-gray-700' }} mt-1">{{ $s['petugas_baru'] }}</p>
                        <p class="text-[11px] {{ $s['petugas_baru'] > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                            {{ $s['petugas_baru'] > 0 ? 'Berhasil didaftarkan' : 'Tidak ada data baru' }}
                        </p>
                    </div>
                    <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-3.5">
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Akun Tersinkron / Ada</p>
                        <p class="text-2xl font-black text-blue-900 mt-1">{{ $s['petugas_existing'] }}</p>
                        <p class="text-[11px] text-blue-600">Data profil tersinkron</p>
                    </div>
                    <div class="{{ $s['baris_gagal'] > 0 ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200' }} border rounded-xl p-3.5">
                        <p class="text-xs font-bold uppercase tracking-wider {{ $s['baris_gagal'] > 0 ? 'text-red-700' : 'text-gray-500' }}">Baris Gagal</p>
                        <p class="text-2xl font-black {{ $s['baris_gagal'] > 0 ? 'text-red-900' : 'text-gray-700' }} mt-1">{{ $s['baris_gagal'] }}</p>
                        <p class="text-[11px] {{ $s['baris_gagal'] > 0 ? 'text-red-600' : 'text-gray-400' }}">Gagal diproses</p>
                    </div>
                </div>

                @if(!empty($s['baris_gagal']) && $s['baris_gagal'] > 0)
                    <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                        <p class="font-bold mb-1">Rincian Error:</p>
                        <ul class="list-disc list-inside space-y-0.5 max-h-32 overflow-y-auto">
                            @foreach($s['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Modal Import Data Pegawai (Alpine.js dengan Loading State Sesuai Import Lain) -->
    <template x-teleport="body">
        <div x-show="importModalOpen" 
             x-data="uploadPegawaiForm()" 
             class="fixed inset-0 overflow-y-auto" 
             aria-labelledby="modal-import-pegawai" 
             role="dialog" 
             aria-modal="true" 
             style="display: none; z-index: 9999;">
            <div class="fixed inset-0 transition-opacity" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);" @click="if (status !== 'uploading') { importModalOpen = false; resetForm(); }"></div>
            
            <div class="min-h-full flex items-center justify-center p-4 sm:p-6" style="position: relative; z-index: 10000;">
                <div x-show="importModalOpen"
                     x-transition:enter="ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                     class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden text-left"
                     @click.outside="if (status !== 'uploading') { importModalOpen = false; resetForm(); }">

                    <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-teal-50/40 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="modal-import-pegawai">
                                    Import Data Pegawai
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Upload file Excel data pegawai untuk membuat akun petugas.
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="if (status !== 'uploading') { importModalOpen = false; resetForm(); }" class="size-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-white flex items-center justify-center transition-colors">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('admin.petugas.import') }}" method="POST" enctype="multipart/form-data" @submit="startUpload($event)">
                        @csrf
                        <div class="p-6 space-y-4">
                            <!-- Dropzone File (Tampil Saat Belum Mulai Upload) -->
                            <div x-show="status === 'idle'">
                                <div class="border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer"
                                     :class="isDragging ? 'border-emerald-500 bg-emerald-50/40' : 'border-gray-300 hover:border-gray-400 bg-gray-50/40'"
                                     @dragover.prevent="isDragging = true"
                                     @dragleave.prevent="isDragging = false"
                                     @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length) { $refs.modalFileInput.files = $event.dataTransfer.files; handleFileChange({ target: $refs.modalFileInput }); }"
                                     @click="$refs.modalFileInput.click()">
                                    
                                    <input type="file" name="file" x-ref="modalFileInput" accept=".xlsx,.xls" class="hidden" 
                                           @change="handleFileChange($event)" required>

                                    <div class="size-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3">
                                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                        </svg>
                                    </div>

                                    <template x-if="!fileName">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-700">
                                                <span class="text-emerald-600 hover:underline">Pilih file Excel</span> atau drag ke sini
                                            </p>
                                            <p class="text-[11px] text-gray-400 mt-1">Format .xlsx atau .xls (Maks 10MB)</p>
                                        </div>
                                    </template>

                                    <template x-if="fileName">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold">
                                            <svg class="size-3.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            <span x-text="fileName"></span>
                                            <span class="text-[10px] text-emerald-600 font-normal" x-text="`(${fileSize})`"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200/80 rounded-xl text-[11px] text-blue-900 space-y-1">
                                    <p class="font-bold flex items-center gap-1">
                                        <svg class="size-3.5 text-blue-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                        </svg>
                                        Aturan Pembuatan Akun:
                                    </p>
                                    <p>• <strong>Username:</strong> NIK (16 digit) atau NIP.</p>
                                    <p>• <strong>Password default:</strong> Tanggal lahir format <code>DDMMYYYY</code> (misal: <code>25091989</code>).</p>
                                    <p>• <strong>Role Admin:</strong> Kepala Puskesmas, PJ TU, Analis Data. Lainnya otomatis <strong>Petugas</strong>.</p>
                                </div>

                                <div class="mt-5 flex items-center justify-end gap-2">
                                    <button type="button" @click="importModalOpen = false; resetForm();" class="py-2 px-3 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                                        Batal
                                    </button>
                                    <button type="submit" class="py-2 px-4 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm flex items-center gap-1.5 transition-colors disabled:opacity-50" :disabled="!fileName">
                                        Mulai Import Pegawai
                                    </button>
                                </div>
                            </div>

                            <!-- File Uploading Progress Card (Persis Seperti Import Lain) -->
                            <div x-show="status !== 'idle'" style="display: none;" class="p-4 bg-gray-50 border border-gray-200 rounded-xl space-y-3.5">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Excel Icon -->
                                        <span class="size-10 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0">
                                            <svg class="size-6" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M19.5 2H8.5C7.39543 2 6.5 2.89543 6.5 4V28C6.5 29.1046 7.39543 30 8.5 30H23.5C24.6046 30 25.5 29.1046 25.5 28V8L19.5 2Z" fill="#E2E8F0"/>
                                                <path d="M19.5 2V8H25.5L19.5 2Z" fill="#CBD5E1"/>
                                                <path d="M22 13H10V25H22V13Z" fill="#107C41"/>
                                                <path d="M14.5 16.5L16 19L17.5 16.5H19L17 20L19 23.5H17.5L16 21L14.5 23.5H13L15 20L13 16.5H14.5Z" fill="white"/>
                                            </svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-800 truncate" x-text="fileName"></p>
                                            <p class="text-[11px] text-gray-400 mt-0.5" x-text="fileSize"></p>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        <template x-if="status === 'uploading'">
                                            <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">
                                                <span class="size-1.5 inline-block bg-blue-600 rounded-full animate-pulse"></span>
                                                Memproses Data Pegawai...
                                            </span>
                                        </template>

                                        <template x-if="status === 'complete'">
                                            <div>
                                                <template x-if="responseType === 'info'">
                                                    <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                                        <svg class="size-3.5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                        </svg>
                                                        Data Tersinkron (Sudah Ada)
                                                    </span>
                                                </template>
                                                <template x-if="responseType !== 'info'">
                                                    <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                        <svg class="size-3.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                        </svg>
                                                        Berhasil Diimpor!
                                                    </span>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="status === 'error'">
                                            <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800">
                                                <svg class="size-3.5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                                Gagal
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Pesan Error Jika Ada -->
                                <template x-if="status === 'error'">
                                    <div class="p-2.5 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700 font-medium">
                                        <span x-text="errorMessage"></span>
                                    </div>
                                </template>

                                <!-- Progress Bar -->
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden" role="progressbar">
                                        <div class="h-full rounded-full transition-all duration-300 ease-out" 
                                             :class="{
                                                'bg-blue-600': status === 'uploading',
                                                'bg-amber-500': status === 'complete' && responseType === 'info',
                                                'bg-emerald-500': status === 'complete' && responseType !== 'info',
                                                'bg-red-500': status === 'error'
                                             }"
                                             :style="`width: ${progress}%`"></div>
                                    </div>
                                    <span class="text-xs font-mono font-bold text-gray-700 w-9 text-end" x-text="`${progress}%`"></span>
                                </div>

                                <!-- Tombol Kembali / Reset Form Saat Error -->
                                <div class="flex justify-end pt-1" x-show="status === 'error'" style="display: none;">
                                    <button type="button" @click="resetForm" class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline">
                                        Upload File Lain & Coba Lagi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <!-- Header & Search -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <form action="{{ route('admin.petugas.index') }}" method="GET" class="flex items-center gap-2 max-w-md">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                    <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input class="py-2 ps-10 pe-3 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIK, NIP, atau Nama...">
            </div>
            <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
                Cari
            </button>
            @if(request('q'))
                <a href="{{ route('admin.petugas.index') }}" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-medium rounded-lg border border-transparent text-blue-600 hover:bg-blue-50">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Nama Lengkap</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Identitas (NIK/NIP)</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Jabatan & Peran</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <div class="flex items-center gap-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">Kontak</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-end"></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($petugas as $p)
                <tr class="hover:bg-gray-50 transition-colors {{ $p->is_active ? '' : 'bg-gray-100 opacity-60' }}">
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <div class="flex items-center gap-x-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $p->is_active ? 'bg-blue-100 text-blue-600' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($p->nama, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="block text-sm font-semibold {{ $p->is_active ? 'text-gray-800' : 'text-gray-500' }}">{{ $p->nama }}</span>
                                    <span class="block text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($p->tanggal_lahir)->translatedFormat('d M Y') }}
                                        @if($p->pegawai?->jenis_kelamin)
                                            • {{ $p->pegawai->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <span class="block text-sm font-mono {{ $p->is_active ? 'text-gray-800' : 'text-gray-500' }}">NIK: {{ $p->nik ?? '-' }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">
                                NIP: {{ $p->nip ?: '(Non-PNS)' }}
                                @if($p->pegawai?->no_bpjs)
                                    • BPJS: {{ $p->pegawai->no_bpjs }}
                                @endif
                            </span>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-md text-xs font-semibold {{ $p->is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-600' }}">
                                {{ $p->jabatan }}
                            </span>
                            <span class="block text-xs text-gray-500 mt-1">
                                {{ $p->pegawai?->pendidikan ? $p->pegawai->pendidikan . ' • ' : '' }}<span class="capitalize">{{ $p->peran }}</span>
                            </span>
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap">
                        <div class="px-6 py-3">
                            <span class="block text-sm font-medium text-gray-800">{{ $p->no_hp ?? '-' }}</span>
                            @if($p->email)
                                <span class="block text-xs text-gray-500 truncate max-w-[180px]" title="{{ $p->email }}">{{ $p->email }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="h-px w-auto whitespace-nowrap text-end">
                        <div class="px-6 py-1.5 flex justify-end gap-x-3 items-center">
                            <a href="{{ route('admin.petugas.edit', $p->id) }}" class="inline-flex items-center gap-x-1 text-sm text-blue-600 decoration-2 hover:underline font-medium">
                                Edit
                            </a>

                            <div x-data="{ open: false }" class="inline-block">
                                <button @click="open = true" type="button" class="inline-flex items-center gap-x-1 text-sm {{ $p->is_active ? 'text-amber-600' : 'text-emerald-600' }} decoration-2 hover:underline font-medium">
                                    {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>

                                <template x-teleport="body">
                                    <div x-show="open" style="display: none;" class="fixed inset-0 z-[80] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                        <!-- Backdrop -->
                                        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

                                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                            <!-- Modal Panel -->
                                            <div x-show="open" 
                                                x-transition:enter="ease-out duration-300" 
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                                x-transition:leave="ease-in duration-200" 
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                                @click.away="open = false"
                                                class="relative inline-block w-full max-w-lg p-4 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl sm:p-7 rounded-xl sm:my-8 sm:align-middle">
                                                
                                                <div class="text-center">
                                                    @if($p->is_active)
                                                    <svg class="size-14 mx-auto text-amber-500 mb-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                    <h3 class="text-lg font-bold text-gray-800" id="modal-title">Nonaktifkan Petugas?</h3>
                                                    <p class="text-sm text-gray-500 mt-2">Apakah Anda yakin ingin menonaktifkan petugas <strong>{{ $p->nama }}</strong>? Petugas tidak akan bisa login lagi ke sistem.</p>
                                                    @else
                                                    <svg class="size-14 mx-auto text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <h3 class="text-lg font-bold text-gray-800" id="modal-title">Aktifkan Petugas?</h3>
                                                    <p class="text-sm text-gray-500 mt-2">Apakah Anda yakin ingin mengaktifkan kembali petugas <strong>{{ $p->nama }}</strong>? Petugas akan bisa login lagi ke sistem.</p>
                                                    @endif
                                                </div>

                                                <div class="mt-6 flex justify-center gap-x-3">
                                                    <button @click="open = false" type="button" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                                                        Batal
                                                    </button>
                                                    <form action="{{ route('admin.petugas.toggle-status', $p->id) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent {{ $p->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white">
                                                            Ya, {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="size-10 text-gray-300 mb-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <p class="text-sm">Tidak ada data petugas yang ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($petugas->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-white">
        {{ $petugas->links() }}
    </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('uploadPegawaiForm', () => ({
        status: 'idle', // idle, uploading, complete, error
        responseType: 'success', // success, info, warning, error
        fileName: '',
        fileSize: '',
        progress: 0,
        errorMessage: '',
        isDragging: false,

        handleFileChange(event) {
            const file = event.target.files ? event.target.files[0] : null;
            if (file) {
                this.fileName = file.name;
                this.fileSize = (file.size / 1024).toFixed(0) + ' KB';
                this.status = 'idle';
                this.responseType = 'success';
                this.errorMessage = '';
            } else {
                this.fileName = '';
                this.fileSize = '';
            }
        },

        resetForm() {
            this.status = 'idle';
            this.responseType = 'success';
            this.progress = 0;
            this.errorMessage = '';
            if (this.$refs.modalFileInput) {
                this.$refs.modalFileInput.value = '';
            }
            this.fileName = '';
            this.fileSize = '';
            this.isDragging = false;
        },

        async startUpload(e) {
            e.preventDefault();
            if (!this.fileName) return;

            this.status = 'uploading';
            this.responseType = 'success';
            this.progress = 0;
            this.errorMessage = '';

            // Simulasikan progress bar halus seperti loading di import PRB & Poliklinik
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
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                clearInterval(interval);

                if (response.ok) {
                    const data = await response.json().catch(() => null);
                    this.progress = 100;
                    this.status = 'complete';
                    if (data?.type === 'info') {
                        this.responseType = 'info';
                    } else {
                        this.responseType = 'success';
                    }

                    // Beri jeda singkat agar animasi 100% dan status berhasil terlihat jelas oleh user
                    setTimeout(() => {
                        window.location.reload();
                    }, 850);
                } else if (response.status === 422) {
                    const data = await response.json();
                    this.status = 'error';
                    this.errorMessage = data.errors?.file?.[0] || data.message || 'Gagal memvalidasi file Excel.';
                } else {
                    const data = await response.json().catch(() => null);
                    this.status = 'error';
                    this.errorMessage = data?.message || 'Terjadi kesalahan pada server saat memproses file.';
                }
            } catch (error) {
                clearInterval(interval);
                this.status = 'error';
                this.errorMessage = 'Koneksi terputus atau gagal mengunggah file. Silakan coba lagi.';
            }
        }
    }));
});
</script>
@endpush
