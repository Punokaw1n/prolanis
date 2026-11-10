@extends('layouts.app')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5" x-data="farmasiPrbApp()">

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2">
            <svg class="size-5 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2.5 rounded-xl text-white shadow-xs" style="background: linear-gradient(135deg, #0d9488, #059669); color: #ffffff;">
                    <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">
                        Farmasi PRB
                        @if($tanggal && $tanggal !== 'semua')
                            <span class="text-sm font-normal text-teal-700 bg-teal-50 border border-teal-200 px-2.5 py-0.5 rounded-full ml-1">
                                Periode: {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM Y') }}
                            </span>
                        @else
                            <span class="text-sm font-normal text-gray-600 bg-gray-100 border border-gray-200 px-2.5 py-0.5 rounded-full ml-1">
                                Semua Riwayat Terdaftar
                            </span>
                        @endif
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Daftar resep dan verifikasi pemberian obat pasien Program Rujuk Balik (PRB)
                    </p>
                </div>
            </div>
        </div>

        <!-- Indikator Data Hari Ini -->
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-teal-50 border border-teal-200/80 rounded-xl text-center sm:text-right">
                <span class="block text-[11px] font-medium text-teal-600 uppercase tracking-wider">Resep Hari Ini</span>
                <span class="text-lg font-bold text-teal-900 font-mono">{{ $totalResepHariIni }} <span class="text-xs font-normal text-teal-700">Resep</span></span>
            </div>
        </div>
    </div>

    <!-- Filter & Toolbar (Format Fleksibel SIMPUS) -->
    <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.prb.farmasi.index') }}" id="filterFarmasiForm" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            <!-- Sisi Kiri: Tombol Tampilkan Data & Pilihan Tanggal -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <!-- Tombol Tampilkan Data (Penyegar/Refresh) -->
                <button type="submit" 
                        class="py-2 px-3.5 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-transparent text-white hover:opacity-90 active:opacity-100 transition-all shadow-2xs cursor-pointer"
                        style="background-color: #0891b2; color: #ffffff;">
                    <svg class="size-3.5 shrink-0 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Tampilkan Data</span>
                </button>

                <!-- Input Tanggal & Kalender Preline UI -->
                <div class="relative inline-block" x-data="prelineDatePicker('{{ $tanggal }}')" @click.outside="isOpen = false">
                    <!-- Hidden Input untuk Kirim Data Form -->
                    <input type="hidden" name="tanggal" :value="selectedDate" id="tanggalInput">

                    <!-- Trigger Button ala Preline Input Group -->
                    <button type="button" 
                            @click="toggleCalendar()" 
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs font-medium rounded-lg border border-gray-300 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 hover:border-gray-400 focus:outline-hidden focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all cursor-pointer"
                            :class="{ 'border-teal-500 ring-1 ring-teal-500 bg-teal-50/20 text-teal-900 font-semibold': selectedDate && selectedDate !== 'semua' }">
                        <svg class="size-4 shrink-0 text-teal-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" x2="16" y1="2" y2="6"/>
                            <line x1="8" x2="8" y1="2" y2="6"/>
                            <line x1="3" x2="21" y1="10" y2="10"/>
                        </svg>
                        <span class="text-gray-500 font-normal">Tgl:</span>
                        <span class="font-semibold text-gray-900" x-text="formattedDisplayDate"></span>
                        <svg class="size-3.5 text-gray-400 transition-transform duration-150 ms-1" :class="{ 'rotate-180 text-teal-600': isOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <!-- Preline UI Calendar Dropdown Menu Popover -->
                    <div x-show="isOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                         class="absolute left-0 mt-2 z-50 bg-white border border-gray-200 shadow-xl rounded-2xl p-4 w-72 sm:w-80 max-w-[calc(100vw-2rem)]"
                         style="display: none;">
                        
                        <!-- Header Kalender: Bulan, Tahun & Tombol Navigasi -->
                        <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100">
                            <div class="flex items-center gap-1">
                                <!-- Pilih Bulan -->
                                <select x-model.number="currentMonth" @change="renderDays()" class="text-xs font-bold text-gray-800 bg-transparent border-0 rounded p-1 hover:bg-gray-100 focus:ring-0 cursor-pointer">
                                    <template x-for="(monthName, idx) in monthNames" :key="idx">
                                        <option :value="idx" x-text="monthName" :selected="currentMonth === idx"></option>
                                    </template>
                                </select>
                                <!-- Pilih Tahun -->
                                <select x-model.number="currentYear" @change="renderDays()" class="text-xs font-bold text-gray-800 bg-transparent border-0 rounded p-1 hover:bg-gray-100 focus:ring-0 cursor-pointer">
                                    <template x-for="y in availableYears" :key="y">
                                        <option :value="y" x-text="y" :selected="currentYear === y"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Navigasi Panah Bulan -->
                            <div class="inline-flex items-center gap-0.5">
                                <button type="button" @click="prevMonth()" title="Bulan Sebelumnya" class="size-7 inline-flex justify-center items-center rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                                </button>
                                <button type="button" @click="nextMonth()" title="Bulan Berikutnya" class="size-7 inline-flex justify-center items-center rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Baris Hari Mingguan (Senin-Minggu Preline Style) -->
                        <div class="grid grid-cols-7 gap-1 text-center mb-1">
                            <span class="text-[11px] font-semibold text-gray-400 py-1">Min</span>
                            <span class="text-[11px] font-semibold text-gray-400 py-1">Sen</span>
                            <span class="text-[11px] font-semibold text-gray-400 py-1">Sel</span>
                            <span class="text-[11px] font-semibold text-gray-400 py-1">Rab</span>
                            <span class="text-[11px] font-semibold text-gray-400 py-1">Kam</span>
                            <span class="text-[11px] font-semibold text-gray-400 py-1">Jum</span>
                            <span class="text-[11px] font-semibold text-gray-400 py-1">Sab</span>
                        </div>

                        <!-- Grid Tanggal Bulan Terpilih -->
                        <div class="grid grid-cols-7 gap-1 text-center text-xs">
                            <!-- Hari Kosong Awal Bulan -->
                            <template x-for="blank in leadingDays" :key="'blank-'+blank">
                                <div class="size-8"></div>
                            </template>
                            <!-- Daftar Hari Dalam Sebulan -->
                            <template x-for="day in daysInMonth" :key="'day-'+day">
                                <button type="button" 
                                        @click="selectDay(day)" 
                                        class="size-8 mx-auto flex items-center justify-center rounded-lg text-xs font-medium transition-all cursor-pointer"
                                        :class="{
                                            'bg-teal-600 text-white font-bold shadow-xs hover:bg-teal-700': isSelected(day),
                                            'ring-1 ring-teal-500 font-bold text-teal-700 bg-teal-50/80 hover:bg-teal-100': isToday(day) && !isSelected(day),
                                            'text-gray-800 hover:bg-gray-100 hover:text-gray-900': !isSelected(day) && !isToday(day)
                                        }"
                                        x-text="day">
                                </button>
                            </template>
                        </div>

                    </div>
                </div>

                <!-- Shortcut Tanggal Cepat -->
                <div class="flex items-center gap-1 bg-gray-50 p-1 rounded-lg border border-gray-200">
                    <a href="{{ route('admin.prb.farmasi.index', ['tanggal' => \Carbon\Carbon::today()->format('Y-m-d'), 'q' => request('q')]) }}" 
                       class="py-1.5 px-2.5 rounded-lg text-xs font-semibold transition-colors {{ $tanggal === \Carbon\Carbon::today()->format('Y-m-d') ? 'bg-cyan-600 text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-200' }}">
                        Hari Ini
                    </a>

                    @if($latestDateAvailable && $latestDateAvailable !== \Carbon\Carbon::today()->format('Y-m-d'))
                    <a href="{{ route('admin.prb.farmasi.index', ['tanggal' => $latestDateAvailable, 'q' => request('q')]) }}" 
                       class="py-1.5 px-2.5 rounded-lg text-xs font-semibold transition-colors {{ $tanggal === $latestDateAvailable ? 'bg-teal-600 text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-200' }}"
                       title="Tanggal terakhir ada kunjungan pasien PRB">
                        Data Terakhir ({{ \Carbon\Carbon::parse($latestDateAvailable)->format('d/m') }})
                    </a>
                    @endif

                    <a href="{{ route('admin.prb.farmasi.index', ['tanggal' => 'semua', 'q' => request('q')]) }}" 
                       class="py-1.5 px-2.5 rounded-lg text-xs font-semibold transition-colors {{ $tanggal === 'semua' ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua Waktu
                    </a>
                </div>
            </div>

            <!-- Sisi Kanan: Kotak Pencarian Sesuai SIMPUS -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-600">Pencarian:</span>
                <div class="relative w-full sm:w-64">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama, RM, obat, dokter..." 
                           class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-sm focus:border-teal-500 focus:ring-teal-500 shadow-xs">
                </div>
                @if(request('q'))
                <a href="{{ route('admin.prb.farmasi.index', ['tanggal' => $tanggal]) }}" class="text-xs text-red-600 hover:underline">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Resep Farmasi (Identik Format SIMPUS Puskesmas Kalijudan) -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar-x">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50/80 font-bold text-gray-700">
                    <tr>
                        <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">No Antrean</th>
                        {{-- <th scope="col" class="py-3 px-3 text-start whitespace-nowrap">Kode RM</th> --}}
                        <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Tgl Resep</th>
                        <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Nota / Status</th>
                        <th scope="col" class="py-3 px-3 text-start whitespace-nowrap">Dokter</th>
                        <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Type Kunjungan</th>
                        <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Poli/Klinik</th>
                        <th scope="col" class="py-3 px-4 text-start whitespace-nowrap">Nama Pasien</th>
                        <th scope="col" class="py-3 px-4 text-start whitespace-nowrap min-w-[180px]">Alamat</th>
                        <th scope="col" class="py-3 px-2 text-center whitespace-nowrap">JK</th>
                        <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Umur</th>
                        {{-- <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Asuransi</th> --}}
                        {{-- <th scope="col" class="py-3 px-3 text-start whitespace-nowrap">Type Pasien</th> --}}
                        {{-- <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Medis</th> --}}
                        {{-- <th scope="col" class="py-3 px-3 text-center whitespace-nowrap">Aksi</th> --}}
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 text-gray-700">
                    @forelse($riwayats as $index => $item)
                        @php
                            $pasien = $item->pasienPrb?->pasien;
                            
                            // Hitung umur format SIMPUS: 46Y 4M 6D
                            $umurLengkap = '-';
                            if ($pasien && $pasien->tanggal_lahir) {
                                $tglAcuan = $item->tanggal_diambil ?? $item->created_at ?? now();
                                $diff = \Carbon\Carbon::parse($pasien->tanggal_lahir)->diff(\Carbon\Carbon::parse($tglAcuan));
                                $umurLengkap = "{$diff->y}Y {$diff->m}M {$diff->d}D";
                            }

                            // Tentukan jenis asuransi & tipe pasien
                            $tipePasien = $pasien->tipe_pasien ?? 'Umum';
                            $asuransi = 'Umum';
                            if (!empty($pasien->no_bpjs) || stripos($tipePasien, 'BPJS') !== false) {
                                $asuransi = 'BPJS';
                            } elseif (stripos($tipePasien, 'APBD') !== false || stripos($tipePasien, 'Jamkesda') !== false) {
                                $asuransi = 'APBD';
                            }

                            // Dokter / Pencatat resep
                            $dokterFormatted = $item->pencatat?->nama_gelar ?? 'Dokter Pemeriksa';

                            $jumlahObat = $item->obatPrb->count();
                            $diagnosaTeks = $item->pasienPrb?->keterangan_diagnosa 
                                ?? $item->pasienPrb?->diagnosa 
                                ?? 'Program Rujuk Balik (PRB)';

                            $tglTransaksi = $item->tanggal_diambil 
                                ? \Carbon\Carbon::parse($item->tanggal_diambil)->format('d/m/Y') 
                                : \Carbon\Carbon::parse($item->created_at)->format('d/m/Y');
                        @endphp
                        <tr class="hover:bg-teal-50/40 transition-colors">
                            <!-- No Antrean -->
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-gray-600">
                                {{ str_pad($riwayats->firstItem() + $index, 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Tgl Resep -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap font-mono font-semibold text-gray-800">
                                {{ $tglTransaksi }}
                            </td>

                            {{-- <!-- Kode RM dengan Ikon Gender SIMPUS (Dicomment sesuai request) -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 font-mono font-bold text-blue-700">
                                    @if(($pasien->jenis_kelamin ?? '') === 'P')
                                        <svg class="size-4 text-pink-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2a6 6 0 0 0-6 6c0 2.97 2.16 5.44 5 5.92V16H9a1 1 0 0 0 0 2h2v3a1 1 0 0 0 2 0v-3h2a1 1 0 0 0 0-2h-2v-2.08c2.84-.48 5-2.95 5-5.92a6 6 0 0 0-6-6Zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                                        </svg>
                                    @else
                                        <svg class="size-4 text-blue-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M20 3h-5a1 1 0 0 0 0 2h2.59l-4.29 4.29a6 6 0 1 0 1.41 1.41L19 6.41V9a1 1 0 0 0 2 0V4a1 1 0 0 0-1-1ZM9 19a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                                        </svg>
                                    @endif
                                    <span>{{ $pasien->no_rm ?: ($pasien->nik ? substr($pasien->nik, -6) : '-') }}</span>
                                </div>
                            </td> --}}

                            <!-- Nota / Status Farmasi -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                @php
                                    $badgeFarmasi = $item->status_farmasi_badge;
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold" style="{{ $badgeFarmasi['style'] }}">
                                    {{ $badgeFarmasi['label'] }}
                                </span>
                            </td>

                            <!-- Dokter -->
                            <td class="py-2.5 px-3 font-medium text-gray-800 whitespace-nowrap">
                                {{ $dokterFormatted }}
                            </td>

                            <!-- Type Kunjungan -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap text-gray-600">
                                Rawat Jalan
                            </td>

                            <!-- Poli/Klinik -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap font-bold text-gray-700 uppercase">
                                POLI PRB
                            </td>

                            <!-- Nama Pasien (Link Biru untuk masuk ke menu detail beri obat) -->
                            <td class="py-2.5 px-4 whitespace-nowrap">
                                <a href="{{ route('admin.prb.farmasi.show', $item->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 underline font-bold uppercase transition-colors" 
                                   title="Klik nama pasien untuk membuka halaman detail & memberi obat">
                                    {{ $pasien->nama ?? 'Tanpa Nama' }}
                                </a>
                                @if($item->diagnosa_lengkap)
                                    <div class="text-[11px] font-semibold text-teal-700 flex items-center gap-1 mt-0.5" title="Diagnosa / Program Resep Ini">
                                        <span class="size-1.5 rounded-full bg-teal-500 shrink-0"></span>
                                        <span>{{ $item->diagnosa_lengkap }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Alamat -->
                            <td class="py-2.5 px-4 text-gray-600 truncate max-w-[220px]" title="{{ $pasien->alamat ?? '-' }}">
                                {{ $pasien->alamat ?: '-' }}
                            </td>

                            <!-- JK -->
                            <td class="py-2.5 px-2 text-center font-semibold text-gray-700">
                                {{ $pasien->jenis_kelamin ?: '-' }}
                            </td>

                            <!-- Umur (SIMPUS Format) -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap font-mono text-gray-700">
                                {{ $umurLengkap }}
                            </td>

                            {{-- <!-- Asuransi (Dicomment sesuai request) -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap font-semibold">
                                <span class="{{ $asuransi === 'BPJS' ? 'text-emerald-700 font-bold' : 'text-amber-700 font-bold' }}">
                                    {{ $asuransi }}
                                </span>
                            </td> --}}

                            {{-- <!-- Type Pasien (Dicomment sesuai request) -->
                            <td class="py-2.5 px-3 whitespace-nowrap text-gray-600">
                                {{ $tipePasien ?: 'Lama' }}
                            </td> --}}

                            {{-- <!-- Medis (Dicomment sesuai request) -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <button type="button" @click="openResep({{ json_encode([
                                    'id' => $item->id,
                                    'pasien_prb_id' => $item->pasien_prb_id,
                                    'nama' => $pasien->nama ?? '-',
                                    'no_rm' => $pasien->no_rm ?: ($pasien->nik ? substr($pasien->nik, -6) : '-'),
                                    'nik' => $pasien->nik ?? '-',
                                    'no_bpjs' => $pasien->no_bpjs ?? '-',
                                    'dokter' => $dokterFormatted,
                                    'tanggal' => $tglTransaksi,
                                    'diagnosa' => $diagnosaTeks,
                                    'catatan' => $item->catatan ?: '-',
                                    'obat' => $item->obatPrb->map(function($o) {
                                        return [
                                            'nama_obat' => $o->nama_obat,
                                            'kategori' => strtoupper($o->kategori ?? 'PRB'),
                                            'signa' => $o->signa ?: '-',
                                            'jumlah_hari' => $o->jumlah_hari ?: '-',
                                            'jumlah_obat' => $o->jumlah_obat ?: '-',
                                            'aturan_minum' => $o->aturan_minum ?: '-'
                                        ];
                                    })
                                ]) }})" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-bold text-white cursor-pointer shadow-2xs transition-colors" style="background-color: #0891b2; color: #ffffff;">
                                    <svg class="size-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    <span>Ada</span>
                                </button>
                            </td> --}}

                            {{-- <!-- Aksi: Tombol Beri Obat & Rincian Modal (Dicomment sesuai request) -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.prb.farmasi.show', $item->id) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold text-white shadow-2xs hover:opacity-90 active:opacity-100 transition-all cursor-pointer"
                                       style="background-color: #e05344; color: #ffffff;"
                                       title="Buka halaman Kunjungan Obat & Beri Obat">
                                        <svg class="size-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        <span>Beri Obat</span>
                                    </a>
                                    <button type="button" @click="openResep({{ json_encode([
                                        'id' => $item->id,
                                        'pasien_prb_id' => $item->pasien_prb_id,
                                        'nama' => $pasien->nama ?? '-',
                                        'no_rm' => $pasien->no_rm ?: ($pasien->nik ? substr($pasien->nik, -6) : '-'),
                                        'nik' => $pasien->nik ?? '-',
                                        'no_bpjs' => $pasien->no_bpjs ?? '-',
                                        'dokter' => $dokterFormatted,
                                        'tanggal' => $tglTransaksi,
                                        'diagnosa' => $diagnosaTeks,
                                        'catatan' => $item->catatan ?: '-',
                                        'obat' => $item->obatPrb->map(function($o) {
                                            return [
                                                'nama_obat' => $o->nama_obat,
                                                'kategori' => strtoupper($o->kategori ?? 'PRB'),
                                                'signa' => $o->signa ?: '-',
                                                'jumlah_hari' => $o->jumlah_hari ?: '-',
                                                'jumlah_obat' => $o->jumlah_obat ?: '-',
                                                'aturan_minum' => $o->aturan_minum ?: '-'
                                            ];
                                        })
                                    ]) }})" class="p-1 rounded text-gray-400 hover:text-blue-600 hover:bg-gray-100 transition-colors" title="Lihat Modal Rincian">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                </div>
                            </td> --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-gray-400">
                                <div class="max-w-md mx-auto space-y-3">
                                    <svg class="size-10 mx-auto text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <p class="font-bold text-gray-700">Tidak ada data resep pasien PRB pada tanggal ini.</p>
                                    <p class="text-xs text-gray-400">
                                        @if($tanggal !== 'semua')
                                            Dokter belum memasukkan resep untuk tanggal <strong>{{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</strong>.
                                        @else
                                            Belum ada data resep pasien PRB yang tersimpan.
                                        @endif
                                    </p>
                                    @if($latestDateAvailable && $tanggal !== $latestDateAvailable)
                                    <div class="pt-2">
                                        <a href="{{ route('admin.prb.farmasi.index', ['tanggal' => $latestDateAvailable]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 py-1.5 px-3 rounded-lg hover:bg-teal-100 transition-colors">
                                            Buka Data Terakhir Tersedia ({{ \Carbon\Carbon::parse($latestDateAvailable)->format('d-m-Y') }}) &rarr;
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($riwayats->hasPages())
        <div class="px-5 py-4 border-t border-gray-200">
            {{ $riwayats->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Detail Resep Obat Dokter (Alpine.js) -->
    <template x-teleport="body">
        <div x-show="modalResepOpen" 
             style="display: none;" 
             class="fixed inset-0 z-[80] overflow-y-auto" 
             role="dialog" 
             aria-modal="true" 
             @keydown.escape.window="modalResepOpen = false">
            
            <!-- Backdrop -->
            <div x-show="modalResepOpen" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" 
                 @click="modalResepOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="modalResepOpen" 
                     x-transition:enter="ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-150" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                    
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <h3 class="text-base font-bold">Rincian Resep Obat Pasien PRB</h3>
                        </div>
                        <button type="button" @click="modalResepOpen = false" class="text-white/80 hover:text-white rounded-lg p-1 transition-colors">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-5 space-y-4 text-xs">
                        <!-- Ringkasan Identitas Pasien & Dokter -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-gray-50 p-3.5 rounded-xl border border-gray-200">
                            <div>
                                <span class="text-gray-500 block text-[11px]">Nama Pasien</span>
                                <span class="font-bold text-gray-900 text-sm uppercase" x-text="selectedResep.nama"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-[11px]">Kode RM / NIK</span>
                                <span class="font-mono font-bold text-blue-700" x-text="selectedResep.no_rm"></span>
                                <span class="text-gray-400 text-[10px] block font-mono" x-text="selectedResep.nik"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-[11px]">Dokter / Pemeriksa</span>
                                <span class="font-semibold text-gray-800" x-text="selectedResep.dokter"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-[11px]">Tgl Resep / Pengambilan</span>
                                <span class="font-medium text-gray-800" x-text="selectedResep.tanggal"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500 block text-[11px]">Diagnosa PRB</span>
                                <span class="font-medium text-teal-800" x-text="selectedResep.diagnosa"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-3" x-show="selectedResep.catatan && selectedResep.catatan !== '-'">
                                <span class="text-gray-500 block text-[11px]">Catatan Dokter</span>
                                <span class="italic text-gray-700" x-text="selectedResep.catatan"></span>
                            </div>
                        </div>

                        <!-- Daftar Obat yang Dimasukkan Dokter -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-bold text-gray-800 uppercase tracking-wide text-[11px] flex items-center gap-1.5">
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                    Daftar Obat yang Diberikan Dokter:
                                </h4>
                                <span class="text-[11px] text-gray-500 font-mono" x-text="'Total: ' + (selectedResep.obat ? selectedResep.obat.length : 0) + ' item obat'"></span>
                            </div>

                            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-100 font-bold text-gray-700">
                                        <tr>
                                            <th class="py-2.5 px-3 text-center w-10">No</th>
                                            <th class="py-2.5 px-3 text-center w-16">Kode</th>
                                            <th class="py-2.5 px-3 text-start">Nama Obat</th>
                                            <th class="py-2.5 px-3 text-center">Signa (Aturan)</th>
                                            <th class="py-2.5 px-3 text-center">Hari</th>
                                            <th class="py-2.5 px-3 text-center">Qty (Butir)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template x-for="(o, idx) in selectedResep.obat" :key="idx">
                                            <tr class="hover:bg-gray-50/60">
                                                <td class="py-2 px-3 text-center font-mono text-gray-500" x-text="idx + 1"></td>
                                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                                    <span :class="o.kategori === 'PRB' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-300'"
                                                          class="px-1.5 py-0.5 rounded text-[10px] font-bold" 
                                                          x-text="o.kategori"></span>
                                                </td>
                                                <td class="py-2 px-3 font-bold text-gray-900" x-text="o.nama_obat"></td>
                                                <td class="py-2 px-3 text-center font-mono font-semibold text-gray-700" x-text="o.signa"></td>
                                                <td class="py-2 px-3 text-center font-mono text-gray-600" x-text="o.jumlah_hari !== '-' ? o.jumlah_hari + ' hr' : '-'"></td>
                                                <td class="py-2 px-3 text-center font-mono font-bold text-teal-800" x-text="o.jumlah_obat !== '-' ? o.jumlah_obat + ' butir' : '-'"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!selectedResep.obat || selectedResep.obat.length === 0">
                                            <td colspan="6" class="py-4 text-center text-gray-400 italic">
                                                Belum ada rincian obat yang dicatat.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-3.5 border-t border-gray-200 flex items-center justify-between gap-3">
                        <a :href="'/admin/prb/' + selectedResep.pasien_prb_id" class="text-xs font-semibold text-teal-700 hover:text-teal-900 hover:underline inline-flex items-center gap-1">
                            <span>Buka Detail Pasien PRB</span>
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <button type="button" @click="modalResepOpen = false" class="py-2 px-4 rounded-xl text-xs font-semibold bg-gray-200 text-gray-800 hover:bg-gray-300 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function farmasiPrbApp() {
        return {
            modalResepOpen: false,
            selectedResep: {
                id: null,
                pasien_prb_id: null,
                nama: '',
                no_rm: '',
                nik: '',
                no_bpjs: '',
                dokter: '',
                tanggal: '',
                diagnosa: '',
                catatan: '',
                obat: []
            },
            openResep(data) {
                this.selectedResep = data;
                this.modalResepOpen = true;
            }
        };
    }

    function prelineDatePicker(initialDate) {
        const today = new Date();
        const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        
        let initYear = today.getFullYear();
        let initMonth = today.getMonth();
        let selected = initialDate || todayStr;

        if (initialDate && initialDate !== 'semua' && initialDate.includes('-')) {
            const parts = initialDate.split('-');
            if (parts.length === 3) {
                initYear = parseInt(parts[0], 10);
                initMonth = parseInt(parts[1], 10) - 1;
            }
        }

        const monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        const currentYearNum = today.getFullYear();
        const availableYears = [];
        for (let y = currentYearNum - 5; y <= currentYearNum + 2; y++) {
            availableYears.push(y);
        }

        return {
            isOpen: false,
            selectedDate: selected,
            todayStr: todayStr,
            currentYear: initYear,
            currentMonth: initMonth,
            monthNames: monthNames,
            availableYears: availableYears,
            leadingDays: [],
            daysInMonth: [],

            init() {
                this.renderDays();
            },

            get formattedDisplayDate() {
                if (!this.selectedDate || this.selectedDate === 'semua') {
                    return 'Semua Waktu';
                }
                if (this.selectedDate === this.todayStr) {
                    return 'Hari Ini (' + this.formatShort(this.selectedDate) + ')';
                }
                return this.formatLong(this.selectedDate);
            },

            formatShort(dateStr) {
                if (!dateStr || !dateStr.includes('-')) return dateStr;
                const p = dateStr.split('-');
                return p[2] + '/' + p[1];
            },

            formatLong(dateStr) {
                if (!dateStr || !dateStr.includes('-')) return dateStr;
                const p = dateStr.split('-');
                const d = parseInt(p[2], 10);
                const m = parseInt(p[1], 10) - 1;
                const y = p[0];
                const mName = this.monthNames[m] ? this.monthNames[m].substring(0, 3) : p[1];
                return d + ' ' + mName + ' ' + y;
            },

            toggleCalendar() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.renderDays();
                }
            },

            renderDays() {
                const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();
                const totalDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();

                this.leadingDays = Array.from({ length: firstDayIndex }, (_, i) => i);
                this.daysInMonth = Array.from({ length: totalDays }, (_, i) => i + 1);
            },

            prevMonth() {
                if (this.currentMonth === 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                } else {
                    this.currentMonth--;
                }
                this.renderDays();
            },

            nextMonth() {
                if (this.currentMonth === 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                } else {
                    this.currentMonth++;
                }
                this.renderDays();
            },

            getDateStr(day) {
                const m = String(this.currentMonth + 1).padStart(2, '0');
                const d = String(day).padStart(2, '0');
                return this.currentYear + '-' + m + '-' + d;
            },

            isSelected(day) {
                if (this.selectedDate === 'semua') return false;
                return this.selectedDate === this.getDateStr(day);
            },

            isToday(day) {
                return this.todayStr === this.getDateStr(day);
            },

            selectDay(day) {
                const dateStr = this.getDateStr(day);
                this.selectedDate = dateStr;
                this.isOpen = false;
                this.submitForm();
            },

            selectDate(dateStr) {
                if (!dateStr) return;
                this.selectedDate = dateStr;
                if (dateStr.includes('-')) {
                    const parts = dateStr.split('-');
                    this.currentYear = parseInt(parts[0], 10);
                    this.currentMonth = parseInt(parts[1], 10) - 1;
                }
                this.isOpen = false;
                this.submitForm();
            },

            selectToday() {
                this.selectedDate = this.todayStr;
                const parts = this.todayStr.split('-');
                this.currentYear = parseInt(parts[0], 10);
                this.currentMonth = parseInt(parts[1], 10) - 1;
                this.isOpen = false;
                this.submitForm();
            },

            selectAllTime() {
                this.selectedDate = 'semua';
                this.isOpen = false;
                this.submitForm();
            },

            submitForm() {
                this.$nextTick(() => {
                    const form = document.getElementById('filterFarmasiForm');
                    if (form) {
                        form.submit();
                    }
                });
            }
        };
    }
</script>
@endsection
