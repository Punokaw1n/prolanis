@extends('layouts.app')

@section('content')
<!-- Page Heading with Back Button and Actions -->
<header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-x-4">
        <a href="{{ route('admin.pasien.index') }}" class="inline-flex justify-center items-center size-10 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Detail Pasien</h1>
            <p class="mt-1 text-sm text-gray-600">Informasi profil dan riwayat pemeriksaan pasien.</p>
        </div>
    </div>
    <div class="sm:ms-auto flex items-center gap-2" x-data="{ toggleStatusModalOpen: false, selectedAlasan: 'Meninggal Dunia', customAlasan: '', editPasienModalOpen: @json($errors->any()) }">
        <button type="button" @click="editPasienModalOpen = true" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-blue-200 bg-blue-50/70 text-blue-700 shadow-sm hover:bg-blue-100 hover:border-blue-300 transition-colors">
            <svg class="flex-shrink-0 size-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
            Edit Profil Pasien
        </button>

        <button type="button" @click="toggleStatusModalOpen = true" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 transition-colors">
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            {{ ($pasien->status_keaktifan ?? 'aktif') === 'aktif' ? 'Nonaktifkan Pasien' : 'Aktifkan Pasien' }}
        </button>

        @include('admin.pasien._modal_edit', ['pasien' => $pasien])

        <!-- Toggle Status Modal (Alpine.js) -->
        <template x-teleport="body">
            <div x-show="toggleStatusModalOpen" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none; z-index: 9999;">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);"></div>
                <div class="fixed" style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 32rem; padding: 1rem; z-index: 10000;">
                    <div x-show="toggleStatusModalOpen" 
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="w-full text-left overflow-hidden shadow-xl transform transition-all" style="background-color: #ffffff; border-radius: 0.75rem;">
                        
                        <div class="p-6 sm:p-8 text-center">
                            @if(($pasien->status_keaktifan ?? 'aktif') === 'aktif')
                                <span class="mb-4 inline-flex justify-center items-center size-[62px] rounded-full border-4" style="border-color: #fee2e2; background-color: #fecaca; color: #dc2626;">
                                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                </span>
                                <h3 class="mb-2 text-xl font-bold" id="modal-title" style="color: #1f2937;">
                                    Nonaktifkan Pasien
                                </h3>
                                <p class="text-sm whitespace-normal mb-4" style="color: #6b7280;">
                                    Apakah Anda yakin ingin menonaktifkan pasien <strong>{{ $pasien->nama }}</strong>?
                                    <br><span class="text-xs text-gray-500">Pasien tidak akan muncul pada pencarian jadwal Prolanis baru. Seluruh data rekam medis dan riwayatnya tetap tersimpan utuh di sistem.</span>
                                </p>

                                <form action="{{ route('admin.pasien.toggle-status', $pasien->id) }}" method="POST" class="text-start">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-4">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Penonaktifan:</label>
                                        <select x-model="selectedAlasan" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                                            <option value="Meninggal Dunia">Meninggal Dunia</option>
                                            <option value="Pindah Faskes / Domisili">Pindah Faskes / Domisili</option>
                                                            <option value="Menolak Mengikuti Program">Menolak Mengikuti Program</option>
                                            <option value="Lainnya">Alasan Lainnya...</option>
                                        </select>
                                    </div>
                                    <div class="mb-4" x-show="selectedAlasan === 'Lainnya'" style="display: none;">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tuliskan Alasan:</label>
                                        <input type="text" x-model="customAlasan" placeholder="Masukkan alasan penonaktifan..." class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                                    </div>
                                    <input type="hidden" name="alasan" :value="selectedAlasan === 'Lainnya' ? customAlasan : selectedAlasan">

                                    <div class="mt-6 flex justify-end gap-x-3">
                                        <button type="button" @click="toggleStatusModalOpen = false" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="submit" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-red-600 text-white hover:bg-red-700 shadow-sm">
                                            Ya, Nonaktifkan
                                        </button>
                                    </div>
                                </form>
                            @else
                                <span class="mb-4 inline-flex justify-center items-center size-[62px] rounded-full border-4" style="border-color: #dcfce7; background-color: #bbf7d0; color: #16a34a;">
                                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                </span>
                                <h3 class="mb-2 text-xl font-bold" id="modal-title" style="color: #1f2937;">
                                    Aktifkan Kembali Pasien
                                </h3>
                                <p class="text-sm whitespace-normal mb-6" style="color: #6b7280;">
                                    Apakah Anda yakin ingin mengaktifkan kembali status pasien <strong>{{ $pasien->nama }}</strong>?
                                    <br><span class="text-xs text-gray-500">Pasien akan kembali aktif dan dapat dipilih dalam jadwal kegiatan Prolanis.</span>
                                </p>

                                <form action="{{ route('admin.pasien.toggle-status', $pasien->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex justify-center gap-x-3">
                                        <button type="button" @click="toggleStatusModalOpen = false" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="submit" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-green-600 text-white hover:bg-green-700 shadow-sm">
                                            Ya, Aktifkan Kembali
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</header>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Profil Pasien Card -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="p-4 md:p-6">
                <div class="flex items-center gap-x-4 mb-6">
                    <div class="inline-flex justify-center items-center size-[62px] rounded-full bg-blue-100 text-blue-800">
                        <span class="text-2xl font-bold">{{ strtoupper(substr($pasien->nama, 0, 1)) }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-x-2">
                            <h2 class="text-lg font-bold text-gray-800">{{ $pasien->nama }}</h2>
                            @if($pasien->tipe_pasien)
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-2 rounded-md text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    {{ $pasien->tipe_pasien }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            @if(($pasien->status_keaktifan ?? 'aktif') === 'aktif')
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-2 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                    <span class="size-1.5 inline-block rounded-full bg-green-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-2 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                    <span class="size-1.5 inline-block rounded-full bg-red-500"></span>
                                    Tidak Aktif
                                </span>
                            @endif
                            <span class="text-sm text-gray-500">No RM: <span class="font-medium text-gray-800">{{ $pasien->no_rm ?? '-' }}</span></span>
                        </div>
                    </div>
                </div>

                @if(($pasien->status_keaktifan ?? 'aktif') === 'tidak_aktif')
                <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="size-4 text-red-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        Status Pasien Tidak Aktif
                    </p>
                    <p class="mt-1 text-gray-700"><strong>Alasan:</strong> {{ $pasien->alasan_tidak_aktif ?? 'Tidak dicantumkan' }}</p>
                    @if($pasien->tanggal_tidak_aktif)
                        <p class="text-gray-500 mt-0.5">Sejak {{ \Carbon\Carbon::parse($pasien->tanggal_tidak_aktif)->translatedFormat('d F Y') }}</p>
                    @endif
                </div>
                @endif

                <dl class="space-y-4">
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">NIK</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->nik }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Tgl Lahir</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">
                            {{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                            @if($pasien->tanggal_lahir)
                                <span class="text-gray-500 font-normal">({{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->age }} th)</span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">
                            @if($pasien->jenis_kelamin == 'L') Laki-laki @elseif($pasien->jenis_kelamin == 'P') Perempuan @else - @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">No HP</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->no_hp ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">BPJS</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->no_bpjs ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Pekerjaan</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->pekerjaan ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Pendidikan</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->pendidikan ?? '-' }}</dd>
                    </div>
                    @if($pasien->diagnosa)
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Diagnosa PRB</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $pasien->diagnosa }}
                            </span>
                        </dd>
                    </div>
                    @endif
                    <div class="grid grid-cols-3 gap-1">
                        <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                        <dd class="text-sm font-medium text-gray-800 col-span-2">{{ $pasien->alamat ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Riwayat Kunjungan -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Tren Kesehatan Pasien (Charts) -->
        @if($pasien->pemeriksaans->count() >= 1)
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden p-4 md:p-5">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Tren Kesehatan</h3>
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Grafik Tensi -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">Tekanan Darah (mmHg)</h4>
                    <div class="relative h-72" style="height: 288px; width: 100%;">
                        <canvas id="tensi-chart"></canvas>
                    </div>
                </div>
                <!-- Grafik BB -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">Berat Badan (kg)</h4>
                    <div class="relative h-72" style="height: 288px; width: 100%;">
                        <canvas id="bb-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <h3 class="text-lg font-bold text-gray-800">Riwayat Pemeriksaan</h3>
        
        @forelse($pasien->pemeriksaans as $pemeriksaan)
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <!-- Header Kunjungan -->
            <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-x-2">
                    <svg class="size-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($pemeriksaan->tanggal_periksa)->translatedFormat('l, d F Y') }}</span>
                </div>
                @if($pemeriksaan->nakes)
                <span class="text-sm text-gray-600">Dokter: <span class="font-medium text-gray-800">{{ $pemeriksaan->nakes->nama }}</span></span>
                @endif
            </div>
            
            <div class="p-4 md:p-5 space-y-4">
                <!-- TTV Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">BB / TB</span>
                        <div class="flex flex-wrap items-center gap-2 mt-0.5">
                            <span class="text-sm font-medium text-gray-800">
                                {{ $pemeriksaan->berat_badan ?? '-' }} kg / {{ $pemeriksaan->tinggi_badan ?? '-' }} cm
                            </span>
                            @if($pemeriksaan->nilai_imt)
                                @php $imtStatus = $pemeriksaan->status_imt; @endphp
                                <span class="inline-flex items-center gap-x-1 py-0.5 px-1.5 rounded-md text-[10px] font-semibold {{ $imtStatus['color'] }}" title="Nilai IMT: {{ $pemeriksaan->nilai_imt }}">
                                    {{ $imtStatus['label'] }} ({{ $pemeriksaan->nilai_imt }})
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">Tensi</span>
                        <span class="block text-sm font-medium text-gray-800">
                            {{ $pemeriksaan->tensi_sistol ?? '-' }} / {{ $pemeriksaan->tensi_diastol ?? '-' }} mmHg
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">Heart Rate</span>
                        <span class="block text-sm font-medium text-gray-800">
                            {{ $pemeriksaan->heart_rate ?? '-' }} bpm
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 uppercase">Suhu</span>
                        <span class="block text-sm font-medium text-gray-800">
                            {{ $pemeriksaan->temperature ?? '-' }} °C
                        </span>
                    </div>
                </div>

                <!-- Keluhan & Diagnosa -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-1">Keluhan</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 rounded p-2 border border-gray-100 min-h-[60px]">{{ $pemeriksaan->keluhan ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-1">Diagnosa</h4>
                        <div class="text-sm text-gray-600 bg-gray-50 rounded p-2 border border-gray-100 min-h-[60px]">
                            @if($pemeriksaan->kode_icd)
                                <span class="inline-flex items-center gap-x-1.5 py-0.5 px-1.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                    {{ $pemeriksaan->kode_icd }}
                                </span>
                                <br>
                            @endif
                            {{ $pemeriksaan->nama_diagnosa ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Obat -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-1">
                        <svg class="size-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        Obat yang Diberikan
                    </h4>
                    @if($pemeriksaan->pemberianObats->count() > 0)
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($pemeriksaan->pemberianObats as $obat)
                                <li class="inline-flex items-center gap-x-2 py-2 px-3 text-sm font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg">
                                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    {{ $obat->nama_obat }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 italic">Tidak ada data obat untuk kunjungan ini.</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-8 text-center">
            <svg class="mx-auto size-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-800">Belum Ada Riwayat</h3>
            <p class="mt-1 text-sm text-gray-500">Pasien ini belum memiliki riwayat pemeriksaan poliklinik.</p>
        </div>
        @endforelse
    </div>
</div>

@if($pasien->pemeriksaans->count() >= 1)
@php
    // Urutkan dari yang terlama ke terbaru untuk grafik (kiri ke kanan)
    $chartData = $pasien->pemeriksaans->sortBy('tanggal_periksa')->values();
    $categories = $chartData->map(function($item) {
        return \Carbon\Carbon::parse($item->tanggal_periksa)->format('d M Y');
    })->toJson();
    $sistol = $chartData->pluck('tensi_sistol')->map(fn($v) => (float)$v)->toJson();
    $diastol = $chartData->pluck('tensi_diastol')->map(fn($v) => (float)$v)->toJson();
    $bb = $chartData->pluck('berat_badan')->map(fn($v) => (float)$v)->toJson();
@endphp
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.1.0/dist/chartjs-plugin-annotation.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const labels = {!! $categories !!};
        const tensiSistol = {!! $sistol !!};
        const tensiDiastol = {!! $diastol !!};
        const bbData = {!! $bb !!};
        
        // Chart Tensi (Line Chart dengan 2 data: Sistol & Diastol)
        const tensiCtx = document.getElementById('tensi-chart').getContext('2d');
        
        new Chart(tensiCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sistolik',
                        data: tensiSistol,
                        borderColor: 'rgb(220, 38, 38)', // Red 600
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: 'rgb(220, 38, 38)',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Diastolik',
                        data: tensiDiastol,
                        borderColor: 'rgb(59, 130, 246)', // Blue 500
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    annotation: {
                        annotations: {
                            lineSistolik: {
                                type: 'line',
                                yMin: 120,
                                yMax: 120,
                                borderColor: 'rgba(220, 38, 38, 0.4)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                label: {
                                    display: true,
                                    content: 'Batas Normal Sistolik (120)',
                                    position: 'start',
                                    backgroundColor: 'rgba(220, 38, 38, 0.7)',
                                    color: 'white',
                                    font: { size: 10 }
                                }
                            },
                            lineDiastolik: {
                                type: 'line',
                                yMin: 80,
                                yMax: 80,
                                borderColor: 'rgba(59, 130, 246, 0.4)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                label: {
                                    display: true,
                                    content: 'Batas Normal Diastolik (80)',
                                    position: 'start',
                                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                                    color: 'white',
                                    font: { size: 10 }
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        offset: true,
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: false,
                        suggestedMin: 60,
                        suggestedMax: 180,
                        title: { display: true, text: 'mmHg' }
                    }
                }
            }
        });

        // Chart Berat Badan (Bar/Line Chart)
        const bbCtx = document.getElementById('bb-chart').getContext('2d');
        
        let minBb = 40;
        let maxBb = 100;
        const validBb = bbData.filter(v => v > 0);
        if (validBb.length > 0) {
            minBb = Math.min(...validBb) - 5;
            maxBb = Math.max(...validBb) + 5;
        }

        new Chart(bbCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Berat Badan (kg)',
                    data: bbData,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)', // Emerald 500
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        offset: true,
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: false,
                        suggestedMin: minBb,
                        suggestedMax: maxBb,
                        title: { display: true, text: 'Kilogram (kg)' }
                    }
                }
            }
        });
    });
</script>
@endpush
@endif

@endsection
