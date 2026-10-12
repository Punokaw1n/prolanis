@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-x-4">
            <a href="{{ route('admin.prb.show', $prb->id) }}" class="inline-flex justify-center items-center size-10 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 transition-colors" title="Kembali ke Detail Pasien">
                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Edit Pengambilan Obat</h1>
                <p class="mt-1 text-sm text-gray-600">Pasien: <strong class="text-gray-800">{{ $prb->pasien->nama }}</strong> (NIK: {{ $prb->pasien->nik }})</p>
            </div>
        </div>
    </header>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 mb-6" role="alert">
            <span class="font-bold">Error:</span>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.prb.riwayat.update', $riwayat->id) }}" method="POST" x-data="riwayatForm()">
        @csrf
        @method('PUT')
        
        <!-- Informasi Tanggal -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">Informasi Waktu</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_jatuh_tempo" x-model="tanggalJatuhTempo" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-2">Kapan pasien seharusnya mengambil obat di siklus ini.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal Diambil</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="tanggal_diambil" x-model="tanggalDiambil" class="py-3 px-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Kapan pasien benar-benar mengambil. Kosongkan jika memang belum ambil.</p>
                </div>
            </div>
        </div>

        <!-- Daftar Obat (Dinamis) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-lg font-semibold text-gray-800">Rincian Obat (Opsional)</h2>
                <button type="button" @click="tambahObat" class="py-1.5 px-3 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg border border-transparent bg-blue-100 text-blue-600 hover:bg-blue-200">
                    <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Obat
                </button>
            </div>

            <template x-if="obatList.length === 0">
                <div class="text-center py-6 text-gray-500 text-sm border-2 border-dashed border-gray-200 rounded-lg">
                    Belum ada obat yang dicatat.<br>Klik "Tambah Obat" jika ingin mencatat rinciannya.
                </div>
            </template>

            <div class="space-y-4">
                <template x-for="(obat, index) in obatList" :key="obat.id">
                    <div class="flex items-start gap-4 p-4 border border-gray-100 bg-gray-50 rounded-lg relative">
                        <div class="flex-grow">
                            <label class="block text-xs font-medium mb-1 text-gray-500">Nama Obat</label>
                            <input type="text" :name="`obat[${index}][nama]`" x-model="obat.nama" class="py-2 px-3 block w-full border border-gray-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ketik nama obat..." required>
                        </div>
                        <div class="w-1/3">
                            <label class="block text-xs font-medium mb-1 text-gray-500">Kategori</label>
                            <select :name="`obat[${index}][kategori]`" x-model="obat.kategori" class="py-2 px-3 block w-full border border-gray-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="prb">PRB</option>
                                <option value="non_prb">Non-PRB</option>
                            </select>
                        </div>
                        <div class="pt-5">
                            <button type="button" @click="hapusObat(index)" class="p-2 inline-flex items-center text-sm font-semibold rounded-md border border-transparent text-red-500 hover:bg-red-50">
                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function riwayatForm() {
    return {
        tanggalDiambil: '{{ $riwayat->tanggal_diambil ? $riwayat->tanggal_diambil->format('Y-m-d') : '' }}',
        tanggalJatuhTempo: '{{ $riwayat->tanggal_jatuh_tempo ? $riwayat->tanggal_jatuh_tempo->format('Y-m-d') : '' }}',
        obatList: {!! $riwayat->obatPrb->map(function($o) { return ['id' => $o->id, 'nama' => $o->nama_obat, 'kategori' => $o->kategori]; })->toJson() !!},
        
        tambahObat() {
            this.obatList.push({
                id: Date.now(),
                nama: '',
                kategori: 'prb'
            });
        },
        
        hapusObat(index) {
            this.obatList.splice(index, 1);
        }
    }
}
</script>
@endpush
@endsection
