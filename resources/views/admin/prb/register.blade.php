@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.prb.index') }}" class="hover:text-blue-600 transition-colors">Program Rujuk Balik</a>
                <span>/</span>
                <span class="text-gray-800 font-semibold">Buku Register & Absensi</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight sm:text-3xl">Buku Register Pasien PRB</h1>
            <p class="mt-1 text-sm text-gray-600">
                Lembar absensi dan rekap serah terima obat bagi pasien PRB yang datang mengambil obat di Puskesmas.
            </p>
        </div>

        <!-- Tombol Aksi Cetak -->
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.prb.index') }}" class="py-2 px-3.5 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs transition-colors">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Data Pasien PRB
            </a>

            @php
                $cetakParams = request()->all();
            @endphp
            <a href="{{ route('admin.prb.register.cetak', array_merge($cetakParams, ['autoprint' => 1])) }}" target="_blank" class="py-2 px-4 inline-flex items-center gap-x-2 text-xs font-bold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-all hover:shadow hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.64-2.124-1.22-3.089m0 0a15.34 15.34 0 0 1 2.22-2.179m-2.22 2.179c1.077-.24 2.124-.64 3.089-1.22M17.28 13.829c.24-1.076.64-2.124 1.22-3.089m0 0a15.34 15.34 0 0 0-2.22-2.179m2.22 2.179c-1.077-.24-2.124-.64-3.089-1.22M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v6.75a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 15.75V9a2.25 2.25 0 0 1 2.25-2.25Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25m-9 9h9m-9 3h9" />
                </svg>
                Cetak Lembar Register (A4/F4)
            </a>
        </div>
    </div>

    <!-- Filter Card & Periode Selector -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs p-5" x-data="{ currentMode: '{{ $mode }}' }">
        <form action="{{ route('admin.prb.register') }}" method="GET" class="space-y-4">
            <!-- Mode Switcher Tabs -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Pilih Mode Periode Register:</span>
                
                <div class="inline-flex p-1 bg-gray-100 border border-gray-200 rounded-lg self-start sm:self-auto">
                    <button type="button" 
                        @click="currentMode = 'harian'"
                        class="py-1.5 px-3 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5"
                        :class="currentMode === 'harian' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-gray-600 hover:text-gray-900'">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        Per Tanggal (Harian)
                    </button>
                    
                    <button type="button" 
                        @click="currentMode = 'bulan'"
                        class="py-1.5 px-3 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5"
                        :class="currentMode === 'bulan' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-gray-600 hover:text-gray-900'">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        Per Bulan (Format Buku Lembar Fisik)
                    </button>

                    <button type="button" 
                        @click="currentMode = 'rentang'"
                        class="py-1.5 px-3 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5"
                        :class="currentMode === 'rentang' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-gray-600 hover:text-gray-900'">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                        </svg>
                        Rentang Tanggal
                    </button>
                </div>
            </div>

            <!-- Hidden Input Mode -->
            <input type="hidden" name="mode" :value="currentMode">

            <!-- Filter Inputs Area -->
            <div class="flex flex-col lg:flex-row lg:items-end gap-3.5 pt-1">
                <!-- Mode: Harian -->
                <div class="shrink-0" x-show="currentMode === 'harian'">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 whitespace-nowrap">
                        Tanggal Kedatangan Pasien <span class="text-red-500">*</span>
                    </label>
                    <div class="inline-flex items-center gap-1.5">
                        <input type="date" name="tanggal" value="{{ $tanggal }}" class="py-2.5 px-3 block w-44 sm:w-48 border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                        <button type="button" onclick="document.querySelector('input[name=tanggal]').value = '{{ now()->toDateString() }}'; this.form.submit();" class="py-2.5 px-3 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg border border-gray-200 whitespace-nowrap transition-colors cursor-pointer" title="Set ke Hari Ini">
                            Hari Ini
                        </button>
                    </div>
                </div>

                <!-- Mode: Bulanan (Mirip Foto Kertas Asli) -->
                <div class="shrink-0" x-show="currentMode === 'bulan'">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 whitespace-nowrap">
                        Bulan & Tahun Pengambilan <span class="text-red-500">*</span>
                    </label>
                    <div class="inline-flex items-center gap-1.5">
                        <select name="bulan" class="py-2.5 px-3 block w-40 border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                            @foreach($namaBulanIndo as $num => $namaB)
                                <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                                    {{ $namaB }}
                                </option>
                            @endforeach
                        </select>
                        <select name="tahun" class="py-2.5 px-3 block w-28 border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                            @for($y = now()->year + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Mode: Rentang Tanggal -->
                <div class="shrink-0" x-show="currentMode === 'rentang'">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 whitespace-nowrap">
                        Rentang Tanggal <span class="text-red-500">*</span>
                    </label>
                    <div class="inline-flex items-center gap-1.5">
                        <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="py-2.5 px-3 block w-36 sm:w-40 border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                        <span class="text-xs text-gray-400 font-semibold px-0.5">s/d</span>
                        <input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir }}" class="py-2.5 px-3 block w-36 sm:w-40 border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs">
                    </div>
                </div>

                <!-- Search Input -->
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pencarian Pasien / Obat</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5 text-gray-400">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ $search }}" class="py-2.5 ps-10 pe-3 block w-full border border-gray-300 rounded-lg text-xs font-medium text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-2xs" placeholder="Ketik Nama Pasien, NIK, No. BPJS, atau Nama Obat...">
                    </div>
                </div>

                <!-- Action Filter Buttons -->
                <div class="shrink-0 flex items-center gap-2 pt-2 lg:pt-0">
                    <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-1.5 text-xs font-bold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs hover:shadow transition-all cursor-pointer">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        Terapkan
                    </button>
                    @if(request()->hasAny(['tanggal', 'bulan', 'tahun', 'tanggal_mulai', 'tanggal_akhir', 'search', 'mode']))
                        <a href="{{ route('admin.prb.register') }}" class="py-2.5 px-3 inline-flex items-center gap-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 transition-colors" title="Reset Filter">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Stat Cards Ringkasan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/40 border border-blue-200/80 rounded-xl p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Periode Aktif</p>
                    <p class="text-lg font-extrabold text-blue-900 mt-0.5">{{ $periodeLabel }}</p>
                </div>
                <div class="size-10 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-teal-50/40 border border-emerald-200/80 rounded-xl p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Total Pasien Hadir</p>
                    <p class="text-2xl font-black text-emerald-900 mt-0.5">
                        {{ $totalPasien }} <span class="text-xs font-semibold text-emerald-700">Orang</span>
                    </p>
                    <p class="text-[11px] text-emerald-600">({{ $totalKunjungan }} kali pengambilan obat)</p>
                </div>
                <div class="size-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-violet-50 to-purple-50/40 border border-violet-200/80 rounded-xl p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Total Butir Obat Diberikan</p>
                    <p class="text-2xl font-black text-violet-900 mt-0.5">
                        {{ number_format($totalButirObat, 0, ',', '.') }} <span class="text-xs font-semibold text-violet-700">Butir / Unit</span>
                    </p>
                    <p class="text-[11px] text-violet-600">Dari seluruh resep periode ini</p>
                </div>
                <div class="size-10 rounded-lg bg-violet-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 8.5 7 7" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Register Pasien Rujuk Balik (Format Sesuai Kertas Fisik) -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/70">
            <div>
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">
                    Lembar Absensi / Register Meja Farmasi PRB
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Menampilkan pasien yang hadir pada periode: <strong class="text-gray-700">{{ $periodeLabel }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    <span class="size-1.5 rounded-full bg-blue-600"></span>
                    {{ $riwayatList->total() }} Data Ditemukan
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-gray-200">
                    <tr>
                        <th scope="col" class="py-3 px-3 text-center w-12 border-r border-gray-200">NO</th>
                        <th scope="col" class="py-3 px-3 text-center w-24 border-r border-gray-200">TGL</th>
                        <th scope="col" class="py-3 px-4 min-w-[200px] border-r border-gray-200">NAMA PASIEN</th>
                        <th scope="col" class="py-3 px-4 min-w-[280px] border-r border-gray-200">NAMA OBAT & SIGNA (ATURAN)</th>
                        <th scope="col" class="py-3 px-4 min-w-[200px] border-r border-gray-200">ALAMAT / NO HP</th>
                        <th scope="col" class="py-3 px-3 text-center w-16">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($riwayatList as $index => $r)
                        @php
                            $pasien = $r->pasienPrb?->pasien;
                            $rowNum = $riwayatList->firstItem() ? $riwayatList->firstItem() + $index : $index + 1;
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <!-- 1. NO -->
                            <td class="py-3.5 px-3 text-center font-bold text-gray-700 font-mono border-r border-gray-100">
                                {{ $rowNum }}
                            </td>

                            <!-- 2. TGL -->
                            <td class="py-3.5 px-3 text-center border-r border-gray-100 font-mono">
                                <span class="font-bold text-gray-800 text-xs block">
                                    {{ $r->tanggal_diambil ? $r->tanggal_diambil->format('d/m/y') : '-' }}
                                </span>
                                <span class="text-[10px] text-gray-400 block">
                                    {{ $r->tanggal_diambil ? $r->tanggal_diambil->translatedFormat('D') : '' }}
                                </span>
                            </td>

                            <!-- 3. NAMA PASIEN (Nama, No BPJS/NIK, Catatan Tambahan) -->
                            <td class="py-3.5 px-4 border-r border-gray-100 align-top">
                                <div class="font-bold text-gray-900 text-sm leading-snug">
                                    {{ $pasien?->nama ?? 'Pasien Tidak Ditemukan' }}
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center text-[10.5px] font-mono font-semibold text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60" title="No. Kartu BPJS">
                                        {{ $pasien?->no_bpjs ?: ($pasien?->nik ?: '-') }}
                                    </span>
                                </div>
                                @if($r->catatan)
                                    <div class="mt-1.5 inline-flex items-center gap-1 text-[10.5px] font-medium text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/80">
                                        <svg class="size-3 text-amber-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                        <span>{{ $r->catatan }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- 4. NAMA OBAT (Nama, Signa, Qty seperti format tulisan foto) -->
                            <td class="py-3.5 px-4 border-r border-gray-100 align-top">
                                @if($r->obatPrb->count() > 0)
                                    <div class="space-y-1.5">
                                        @foreach($r->obatPrb as $obat)
                                            <div class="flex items-baseline justify-between gap-2 border-b border-gray-100/80 pb-1 last:border-0 last:pb-0">
                                                <div class="flex items-baseline gap-1.5">
                                                    <span class="text-[9.5px] font-bold font-mono px-1 py-0.2 rounded {{ $obat->kategori === 'prb' ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                                                        {{ $obat->kategori === 'prb' ? 'PRB' : 'NON' }}
                                                    </span>
                                                    <span class="font-semibold text-gray-900 text-xs">
                                                        {{ $obat->nama_obat }}
                                                    </span>
                                                    <span class="font-mono text-gray-600 text-[11px]">
                                                        {{ $obat->signa ?: '1 x 1' }}
                                                    </span>
                                                </div>
                                                <div class="font-mono font-bold text-blue-700 text-xs shrink-0">
                                                    ({{ $obat->jumlah_obat ?? 30 }})
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">- Belum ada obat yang tercatat -</span>
                                @endif
                            </td>

                            <!-- 5. ALAMAT / NO HP -->
                            <td class="py-3.5 px-4 border-r border-gray-100 align-top">
                                <div class="text-xs font-medium text-gray-800 leading-relaxed">
                                    {{ $pasien?->alamat ?: '-' }}
                                </div>
                                @if($pasien?->no_hp)
                                    <div class="mt-1 flex items-center gap-1 font-mono text-[11px] text-gray-600">
                                        <svg class="size-3 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                        </svg>
                                        <span>{{ $pasien->no_hp }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- 6. AKSI -->
                            <td class="py-3.5 px-3 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.prb.show', $r->pasien_prb_id) }}" class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Lihat Detail Pasien">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.prb.riwayat.edit', $r->id) }}" class="p-1 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors" title="Edit Riwayat Resep">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center bg-gray-50/50">
                                <div class="max-w-sm mx-auto text-center">
                                    <div class="size-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Belum Ada Pasien Mengambil Obat</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ditemukan riwayat pengambilan obat PRB pada periode <strong class="text-gray-700">{{ $periodeLabel }}</strong>.
                                    </p>
                                    <div class="mt-4 flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.prb.index') }}" class="py-2 px-3 text-xs font-semibold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-2xs">
                                            Buka Data Pasien PRB
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($riwayatList->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $riwayatList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
