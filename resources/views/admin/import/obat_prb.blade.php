@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Page Heading -->
    <header class="mb-8 text-center">
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Import Master Obat PRB</h1>
        <p class="mt-2 text-lg text-gray-600">Upload file Excel daftar obat untuk memperbarui katalog obat Program Rujuk Balik (PRB).</p>
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
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Obat Baru -->
                    <div class="flex flex-col bg-teal-50 border border-teal-200 rounded-lg p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Obat Baru Disimpan</p>
                        <h3 class="mt-1 text-2xl font-bold text-teal-800">{{ $s['obat_baru'] ?? 0 }}</h3>
                    </div>
                    <!-- Obat Existing -->
                    <div class="flex flex-col bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Sudah Ada (Dilewati)</p>
                        <h3 class="mt-1 text-2xl font-bold text-blue-800">{{ $s['obat_existing'] ?? 0 }}</h3>
                    </div>
                    <!-- Gagal -->
                    <div class="flex flex-col bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Baris Gagal</p>
                        <h3 class="mt-1 text-2xl font-bold text-red-800">{{ $s['baris_gagal'] ?? 0 }}</h3>
                    </div>
                </div>

                @if(!empty($s['errors']) && count($s['errors']) > 0)
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
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Upload File Excel Obat PRB</h2>
                <p class="mt-1 text-sm text-gray-500">
                    File yang diterima: <code class="text-xs bg-gray-100 rounded px-1 py-0.5">.xlsx</code> atau <code class="text-xs bg-gray-100 rounded px-1 py-0.5">.xls</code>, maksimal 10MB.
                </p>
            </div>
            <a href="{{ route('admin.import.obat-prb.template') }}" class="inline-flex items-center gap-x-2 py-2 px-3 text-xs font-semibold rounded-lg border border-teal-200 bg-teal-50 text-teal-800 hover:bg-teal-100 transition-colors shadow-2xs shrink-0 self-start sm:self-auto" title="Download template format file Excel resmi">
                <svg class="size-4 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Template Excel
            </a>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.import.obat-prb.process') }}" method="POST" enctype="multipart/form-data" x-data="uploadForm()" @submit="startUpload">
                @csrf

                <div class="space-y-5">
                    <!-- Template Tip Box -->
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-lg flex items-start gap-3">
                        <svg class="size-5 text-teal-600 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-gray-600 space-y-1">
                            <p class="font-medium text-gray-800">Belum memiliki format Excel yang sesuai?</p>
                            <p>Unduh template resmi dengan klik tombol <a href="{{ route('admin.import.obat-prb.template') }}" class="font-semibold text-teal-700 underline hover:text-teal-900">Download Template Excel</a> di atas. Format file persis seperti file master obat PRB dengan 2 kolom standar (<code class="font-mono text-teal-800 bg-teal-100/70 px-1 py-0.5 rounded">No.</code> dan <code class="font-mono text-teal-800 bg-teal-100/70 px-1 py-0.5 rounded">Nama Barang</code>).</p>
                        </div>
                    </div>
                    <div x-show="status === 'idle'">
                        <label for="file" class="block text-sm font-medium mb-2 text-gray-700">Pilih File Master Obat PRB</label>
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
                                    <p class="text-sm font-medium text-gray-800 truncate" x-text="fileName">obat-PRB.xlsx</p>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="fileSize">10 KB</p>
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

                        <div class="mt-4 flex justify-end" x-show="status === 'complete' || status === 'error'" style="display: none;">
                            <button type="button" @click="resetForm" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Upload File Lain</button>
                        </div>
                    </div>

                    <!-- Informasi Format File Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="size-5 text-blue-600 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            </div>
                            <div class="ms-3">
                                <h3 class="text-sm font-semibold text-blue-800">Informasi Format File</h3>
                                <ul class="mt-1 text-sm text-blue-700 list-disc list-inside space-y-1">
                                    <li>Kolom wajib: <strong>Nama Barang</strong> (atau <strong>Nama Obat</strong>).</li>
                                    <li>Obat yang sudah terdaftar tidak akan diduplikasi (otomatis dilewati).</li>
                                    <li>Seluruh obat di file ini otomatis dikategorikan sebagai obat <strong>PRB</strong>.</li>
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

    <!-- Daftar Master Obat Card -->
    <div id="obat-list-container" class="mt-8">
        <div x-data="obatTable({{ $obats->map(fn($o) => [
            'id' => $o->id,
            'nama_obat' => $o->nama_obat,
            'kategori' => $o->kategori,
            'sumber' => $o->sumber,
            'is_active' => (bool)$o->is_active,
        ])->values()->toJson() }})" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            <!-- Card Header with Toggle & Count -->
            <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3 bg-gray-50/50">
                <div class="flex items-center gap-x-3">
                    <h2 class="text-lg font-semibold text-gray-800">Daftar Master Obat PRB</h2>
                    <span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                        Total: <span x-text="items.length"></span> Obat
                    </span>
                </div>
                <button type="button" @click="isOpen = !isOpen" class="inline-flex items-center gap-x-1.5 text-xs font-medium text-gray-600 hover:text-blue-600 py-1.5 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 shadow-sm transition-colors">
                    <span x-text="isOpen ? 'Sembunyikan Tabel' : 'Tampilkan Tabel'"></span>
                    <svg class="size-3.5 transform transition-transform duration-200" :class="{ 'rotate-180': isOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div x-show="isOpen">
                <!-- Search & Filters -->
                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="relative w-full sm:w-80">
                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5">
                            <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <input type="text" x-model="search" 
                            @input="isSearching = true"
                            @input.debounce.500ms="performSearch"
                            @keydown.enter.prevent="performSearch"
                            placeholder="Cari nama obat PRB..." 
                            class="py-2 ps-10 pe-10 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 placeholder-gray-400 shadow-sm">
                        
                        <!-- Bouncing Bars Loading Spinner -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isSearching" style="display: none;">
                            <span class="flex h-4 items-center justify-center gap-0.5" aria-hidden="true">
                                <span class="h-3.5 w-1 rounded-sm bg-gray-300 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0s;"></span>
                                <span class="h-3.5 w-1 rounded-sm bg-gray-400 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.12s;"></span>
                                <span class="h-3.5 w-1 rounded-sm bg-blue-600 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.24s;"></span>
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 w-full sm:w-auto text-end">
                        <span x-show="isSearching" class="inline-flex items-center text-blue-600 font-medium">
                            <span class="size-1.5 inline-block bg-blue-600 rounded-full animate-pulse me-1.5"></span>
                            Mencari data...
                        </span>
                        <span x-show="!isSearching" x-text="`Ditemukan: ${filteredItems.length} dari ${items.length} obat`"></span>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="relative overflow-x-auto">
                    <!-- Subtle Table Loading Overlay -->
                    <div x-show="isSearching" style="display: none;" class="absolute inset-0 z-20 flex items-center justify-center bg-white/60 backdrop-blur-sm">
                        <div class="inline-flex items-center gap-2 bg-white px-4 py-2.5 rounded-xl shadow-md border border-gray-100">
                            <span class="flex h-4 items-center justify-center gap-0.5" aria-hidden="true">
                                <span class="h-3.5 w-1 rounded-sm bg-gray-300 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0s;"></span>
                                <span class="h-3.5 w-1 rounded-sm bg-gray-400 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.12s;"></span>
                                <span class="h-3.5 w-1 rounded-sm bg-blue-600 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.24s;"></span>
                            </span>
                            <span class="text-xs font-medium text-gray-700">Memuat obat...</span>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 w-16">No</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Obat</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 w-32">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="(item, index) in paginatedItems" :key="item.id || index">
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs text-gray-500 font-mono" x-text="(currentPage - 1) * perPage + index + 1"></td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-x-2">
                                            <span class="size-2 rounded-full bg-teal-500"></span>
                                            <span class="text-sm font-semibold text-gray-800" x-text="item.nama_obat"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center py-0.5 px-2.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                            PRB
                                        </span>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr x-show="filteredItems.length === 0">
                                <td colspan="3" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="size-10 text-gray-300 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-800" x-text="items.length === 0 ? 'Belum ada data master obat PRB' : 'Tidak ada obat yang cocok dengan pencarian'"></p>
                                        <p class="text-xs text-gray-500 mt-1" x-text="items.length === 0 ? 'Silakan upload file Excel di atas untuk mengimpor master obat.' : 'Coba gunakan kata kunci pencarian yang lain.'"></p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer (Preline UI Style) -->
                <div class="px-6 py-4 grid gap-3 sm:flex sm:justify-between sm:items-center border-t border-gray-200" x-show="filteredItems.length > 0">
                    <div>
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold text-gray-800" x-text="(currentPage - 1) * perPage + 1"></span> s/d <span class="font-semibold text-gray-800" x-text="Math.min(currentPage * perPage, filteredItems.length)"></span> dari <span class="font-semibold text-gray-800" x-text="filteredItems.length"></span> obat
                        </p>
                    </div>

                    <div x-show="totalPages > 1">
                        <nav class="flex items-center gap-x-1" aria-label="Pagination">
                            <button type="button" @click="prevPage" :disabled="currentPage === 1" 
                                class="min-h-9 min-w-9 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-none disabled:opacity-40 disabled:pointer-events-none" aria-label="Sebelumnya">
                                <svg class="shrink-0 size-3.5 text-gray-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15 18-6-6 6-6"/>
                                </svg>
                                <span class="hidden sm:inline text-xs font-medium">Prev</span>
                            </button>

                            <div class="flex items-center gap-x-1">
                                <template x-for="p in visiblePages" :key="p">
                                    <button type="button" @click="goToPage(p)"
                                        :class="currentPage === p ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-800 hover:bg-gray-100'"
                                        :style="currentPage === p ? 'background-color: #2563eb !important; color: #ffffff !important;' : ''"
                                        class="min-h-9 min-w-9 flex justify-center items-center py-2 px-3 text-xs rounded-lg transition-colors focus:outline-none"
                                        x-text="p">
                                    </button>
                                </template>
                            </div>

                            <button type="button" @click="nextPage" :disabled="currentPage === totalPages" 
                                class="min-h-9 min-w-9 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-none disabled:opacity-40 disabled:pointer-events-none" aria-label="Berikutnya">
                                <span class="hidden sm:inline text-xs font-medium">Next</span>
                                <svg class="shrink-0 size-3.5 text-gray-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('obatTable', (initialData) => ({
        isOpen: true,
        search: '',
        activeFilter: '',
        isSearching: false,
        currentPage: 1,
        perPage: 10,
        items: initialData || [],
        
        get filteredItems() {
            const q = this.activeFilter.trim().toLowerCase();
            if (!q) return this.items;
            return this.items.filter(item => item.nama_obat.toLowerCase().includes(q));
        },
        
        get totalPages() {
            return Math.ceil(this.filteredItems.length / this.perPage) || 1;
        },
        
        get paginatedItems() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredItems.slice(start, start + this.perPage);
        },

        get visiblePages() {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - 2);
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },

        performSearch() {
            this.isSearching = true;
            setTimeout(() => {
                this.activeFilter = this.search;
                this.currentPage = 1;
                this.isSearching = false;
            }, 300);
        },
        
        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },

        goToPage(p) {
            this.currentPage = p;
        }
    }));

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
                    }

                    // Ekstrak dan perbarui daftar tabel master obat
                    const newList = doc.querySelector('#obat-list-container');
                    const targetList = document.querySelector('#obat-list-container');
                    if (newList && targetList) {
                        targetList.innerHTML = newList.innerHTML;
                        if (window.Alpine) {
                            window.Alpine.initTree(targetList);
                        }
                    }

                    // Scroll up to see the summary smoothly
                    window.scrollTo({ top: 0, behavior: 'smooth' });
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
