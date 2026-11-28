@extends('layouts.app')

@section('content')
<header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Pasien SRK</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar kontak pasien non-PRB hasil import Excel untuk dihubungi via WhatsApp.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
        <span class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
            Total Kontak: {{ $totalKontak }}
        </span>
        <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
            Sudah Dihubungi: {{ $totalDihubungi }}
        </span>
        <span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200">
            Belum Dihubungi: {{ max(0, $totalKontak - $totalDihubungi) }}
        </span>
    </div>
</header>

@if (session('success'))
    <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between gap-3">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center justify-between gap-3">
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc ps-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Import Excel -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h2 class="text-base font-bold text-gray-800">Import Data Pasien SRK</h2>
                <p class="text-xs text-gray-500 mt-0.5">Unggah file Excel (.xlsx, .xls, .csv) berisi kolom <strong>Nama</strong> dan <strong>No. Telepon</strong>.</p>
            </div>
            <a href="{{ route('admin.srk.template') }}" class="shrink-0 inline-flex items-center gap-x-1.5 py-2 px-3 text-xs font-semibold rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0-4-4m4 4 4-4M4 20h16"/></svg>
                Download Template
            </a>
        </div>

        <form action="{{ route('admin.srk.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="block w-full text-sm text-gray-600 file:me-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border border-gray-200 rounded-lg cursor-pointer">
            <div class="flex items-center justify-end">
                <button type="submit" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors cursor-pointer">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M12 4v12m0-12-4 4m4-4 4 4"/></svg>
                    Import Excel
                </button>
            </div>
        </form>
    </div>

    <!-- Template Pesan WhatsApp -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="mb-3">
            <h2 class="text-base font-bold text-gray-800">Template Pesan WhatsApp</h2>
            <p class="text-xs text-gray-500 mt-0.5">Gunakan <code class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 font-mono">{nama}</code> agar otomatis diganti dengan nama pasien saat tombol Hubungi diklik.</p>
        </div>

        <form action="{{ route('admin.srk.pesan.update') }}" method="POST" class="space-y-3">
            @csrf
            <textarea name="isi_pesan" rows="4" required class="py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-sm text-gray-800 focus:border-blue-500 focus:ring-blue-500 shadow-sm">{{ old('isi_pesan', $templatePesan) }}</textarea>
            <div class="flex items-center justify-end">
                <button type="submit" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors cursor-pointer">
                    Simpan Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Filter & Table -->
@php
    $srkQueueItems = $pasienSrk->getCollection()->map(function ($item) use ($templatePesan) {
        $pesanFinal = str_replace('{nama}', $item->nama, $templatePesan);

        return [
            'id' => $item->id,
            'nama' => $item->nama,
            'no_hp' => $item->no_hp,
            'pesan' => $pesanFinal,
            'waLink' => 'https://wa.me/' . $item->no_hp . '?text=' . urlencode($pesanFinal),
            'tandaiUrl' => route('admin.srk.tandai', $item->id),
            'dihubungi' => (bool) $item->terakhir_dihubungi,
            'terakhir' => $item->terakhir_dihubungi ? $item->terakhir_dihubungi->translatedFormat('d M Y, H:i') : '',
        ];
    })->values();
@endphp

<div class="flex flex-col gap-5" x-data="srkQueueManager(@js($srkQueueItems), '{{ csrf_token() }}')">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form action="{{ route('admin.srk.index') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative w-full max-w-sm">
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                        <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" class="py-2.5 ps-10 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Cari Nama / No. Telepon...">
                </div>

                <select name="status" onchange="this.form.submit()" class="py-2.5 px-3 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    <option value="">Semua Status</option>
                    <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum Dihubungi</option>
                    <option value="sudah" {{ $status === 'sudah' ? 'selected' : '' }}>Sudah Dihubungi</option>
                </select>

                <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors cursor-pointer">
                    Cari
                </button>

                @if($search !== '' || $status !== '')
                    <a href="{{ route('admin.srk.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                @endif
            </form>

            <div class="flex flex-wrap items-center gap-2">
                @if($totalKontak > 0)
                    <a href="{{ route('admin.srk.export', ['search' => $search, 'status' => $status]) }}" class="py-2.5 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0-4-4m4 4 4-4M4 20h16"/></svg>
                        Export Excel
                    </a>
                    <form action="{{ route('admin.srk.destroy-all') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus seluruh data Pasien SRK ({{ $totalKontak }} kontak)?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="py-2.5 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition-colors cursor-pointer">
                            Kosongkan Semua Data
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if($pasienSrk->count() > 0)
            <div x-show="selectedIds.length > 0" x-cloak class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="selectedIds = []" class="py-1.5 px-3 text-xs font-medium rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                        Batal Pilih
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-600">
                        <span x-text="selectedIds.length"></span> kontak dipilih
                    </span>
                    <button type="button" @click="mulaiAntrean()" class="py-2 px-4 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors cursor-pointer">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Mulai Antrean WA (<span x-text="selectedIds.length"></span>)
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="ps-5 pe-2 py-3 text-start w-10">
                            <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll($event.target.checked)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" title="Pilih semua di halaman ini">
                        </th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Telepon / WA</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-end text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($pasienSrk as $index => $item)
                        @php
                            $pesanFinal = str_replace('{nama}', $item->nama, $templatePesan);
                            $waLink = 'https://wa.me/' . $item->no_hp . '?text=' . urlencode($pesanFinal);
                        @endphp
                        <tr class="hover:bg-gray-50" :class="selectedIds.includes({{ $item->id }}) ? 'bg-blue-50/40' : ''">
                            <td class="ps-5 pe-2 py-4 whitespace-nowrap">
                                <input type="checkbox" :checked="selectedIds.includes({{ $item->id }})" @change="toggleSelect({{ $item->id }})" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $pasienSrk->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                {{ $item->nama }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">
                                {{ $item->no_hp }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <template x-if="statusMap[{{ $item->id }}]?.dihubungi">
                                    <div>
                                        <span class="inline-flex items-center gap-x-1 py-1 px-2.5 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Sudah Dihubungi
                                        </span>
                                        <div class="text-[11px] text-gray-500 mt-1" x-text="statusMap[{{ $item->id }}]?.terakhir"></div>
                                    </div>
                                </template>
                                <template x-if="!statusMap[{{ $item->id }}]?.dihubungi">
                                    <span class="inline-flex items-center gap-x-1 py-1 px-2.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-800">
                                        Belum Dihubungi
                                    </span>
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ $waLink }}" target="_blank" @click="tandaiById({{ $item->id }})" class="inline-flex items-center gap-x-1.5 py-2 px-3 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        Hubungi via WA
                                    </a>
                                    <form action="{{ route('admin.srk.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus kontak {{ addslashes($item->nama) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="py-2 px-2.5 text-xs font-semibold rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50 transition-colors cursor-pointer" title="Hapus">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">
                                Belum ada data Pasien SRK. Silakan import file Excel terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pasienSrk->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $pasienSrk->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Antrean WhatsApp -->
    <div x-show="queueModalOpen" x-cloak style="display: none; position: fixed; inset: 0; z-index: 99999; background-color: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px);" class="fixed inset-0 z-[99999] overflow-y-auto flex items-center justify-center p-4" @keydown.escape.window="queueModalOpen = false">
        <div @click.outside="queueModalOpen = false" class="bg-white border border-gray-200 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="px-5 py-4 bg-emerald-50 border-b border-emerald-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Antrean Hubungi Pasien via WA</h3>
                    <p class="text-xs text-emerald-800 mt-0.5">
                        <span x-show="!queueFinished">
                            Antrean <strong x-text="queueIndex + 1"></strong> dari <strong x-text="queueItems.length"></strong>
                            • Terkirim: <strong x-text="sentCount"></strong>
                            • Dilewati: <strong x-text="skippedCount"></strong>
                        </span>
                        <span x-show="queueFinished">
                            Seluruh antrean selesai diproses (<strong x-text="sentCount"></strong> dibuka, <strong x-text="skippedCount"></strong> dilewati).
                        </span>
                    </p>
                </div>
                <button type="button" @click="queueModalOpen = false" class="size-8 rounded-lg border border-emerald-200 bg-white text-gray-500 hover:text-gray-800 flex items-center justify-center cursor-pointer">&times;</button>
            </div>

            <div class="p-5 space-y-4">
                <!-- Progress Bar -->
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-600 h-2 transition-all duration-300" :style="'width: ' + progressPercent + '%'"></div>
                </div>

                <template x-if="!queueFinished && currentQueueItem">
                    <div class="space-y-3">
                        <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">Pasien Berikutnya</div>
                            <div class="text-base font-bold text-gray-900 mt-0.5" x-text="currentQueueItem.nama"></div>
                            <div class="text-xs font-mono text-emerald-700 font-semibold mt-0.5" x-text="currentQueueItem.no_hp"></div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-gray-600 mb-1">Preview Pesan WA:</div>
                            <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-200 text-xs text-gray-800 whitespace-pre-line max-h-36 overflow-y-auto" x-text="currentQueueItem.pesan"></div>
                        </div>
                    </div>
                </template>

                <template x-if="queueFinished">
                    <div class="py-6 text-center space-y-2">
                        <div class="size-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto font-bold text-lg">&#10003;</div>
                        <p class="text-sm font-bold text-gray-800">Antrean WA Selesai</p>
                        <p class="text-xs text-gray-500">
                            <span x-text="sentCount"></span> kontak sudah ditandai dihubungi dan <span x-text="skippedCount"></span> kontak dilewati.
                        </p>
                    </div>
                </template>
            </div>

            <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-2">
                <button type="button" @click="queueModalOpen = false" class="py-2 px-4 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 cursor-pointer">
                    Tutup
                </button>

                <div class="flex items-center gap-2" x-show="!queueFinished && currentQueueItem">
                    <button type="button" @click="lewatiAntrean()" class="py-2 px-4 text-xs font-semibold rounded-lg border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 cursor-pointer">
                        Lewati
                    </button>
                    <button type="button" @click="bukaWaDanLanjut()" class="py-2 px-4 inline-flex items-center gap-x-1.5 text-xs font-bold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm cursor-pointer">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Buka WA &amp; Lanjut
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function srkQueueManager(items, csrfToken) {
    const statusMap = {};
    items.forEach(item => {
        statusMap[item.id] = {
            dihubungi: item.dihubungi,
            terakhir: item.terakhir
        };
    });

    return {
        items,
        statusMap,
        selectedIds: [],
        queueModalOpen: false,
        queueItems: [],
        queueIndex: 0,
        sentCount: 0,
        skippedCount: 0,

        get isAllSelected() {
            return this.items.length > 0 && this.items.every(item => this.selectedIds.includes(item.id));
        },

        get queueFinished() {
            return this.queueIndex >= this.queueItems.length;
        },

        get currentQueueItem() {
            return this.queueItems[this.queueIndex] || null;
        },

        get progressPercent() {
            if (this.queueItems.length === 0) return 0;
            return Math.min(100, Math.round((this.queueIndex / this.queueItems.length) * 100));
        },

        toggleSelect(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(item => item !== id);
            } else {
                this.selectedIds.push(id);
            }
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedIds = this.items.map(item => item.id);
            } else {
                this.selectedIds = [];
            }
        },

        pilihBelumDihubungi() {
            this.selectedIds = this.items
                .filter(item => !this.statusMap[item.id]?.dihubungi)
                .map(item => item.id);
        },

        mulaiAntrean() {
            this.queueItems = this.items.filter(item => this.selectedIds.includes(item.id));
            if (this.queueItems.length === 0) return;
            this.queueIndex = 0;
            this.sentCount = 0;
            this.skippedCount = 0;
            this.queueModalOpen = true;
        },

        tandaiById(id) {
            const target = this.items.find(item => item.id === id);
            if (!target) return;

            fetch(target.tandaiUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.statusMap[id] = {
                        dihubungi: true,
                        terakhir: data.terakhir_dihubungi
                    };
                }
            })
            .catch(() => {});
        },

        bukaWaDanLanjut() {
            const current = this.currentQueueItem;
            if (!current) return;

            window.open(current.waLink, '_blank');
            this.tandaiById(current.id);
            this.sentCount++;
            this.queueIndex++;
        },

        lewatiAntrean() {
            if (!this.currentQueueItem) return;
            this.skippedCount++;
            this.queueIndex++;
        }
    };
}
</script>
@endpush
@endsection
