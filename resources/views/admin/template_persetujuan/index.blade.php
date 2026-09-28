@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Template Informed Consent</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola format isi surat persetujuan umum untuk pasien.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Kolom -->
    <div class="lg:col-span-2">
        <form action="{{ route('admin.template.store') }}" method="POST" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Editor Surat Persetujuan</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Judul -->
                <div>
                    <label for="judul" class="block text-sm font-medium mb-2">Judul Surat <span class="text-red-500">*</span></label>
                    <input type="text" id="judul" name="judul" value="{{ old('judul', $template->judul ?? 'INFORMED CONSENT') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>
                </div>

                <!-- Teks Pembuka -->
                <div>
                    <label for="teks_pembuka" class="block text-sm font-medium mb-2">Teks Pembuka <span class="text-red-500">*</span></label>
                    <textarea id="teks_pembuka" name="teks_pembuka" rows="4" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>{{ old('teks_pembuka', $template->teks_pembuka ?? "Yang bertanda tangan di bawah ini:\n\nNama: {nama_pasien}\nNIK: {nik}\nNo. RM: {no_rm}\nTanggal Lahir: {tanggal_lahir}\nAlamat: {alamat}\n\nDengan ini menyatakan dengan sesungguhnya bahwa saya telah menerima informasi dan memberikan persetujuan terhadap pelayanan kesehatan di Puskesmas Kalijudan, dengan ketentuan sebagai berikut:") }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Isi data yang menggunakan kurawal (seperti <code>{nama_pasien}</code>) akan diganti otomatis oleh sistem.</p>
                </div>

                <!-- Poin-poin Persetujuan -->
                <div x-data="poinManager()" class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-sm font-medium">Poin-poin Persetujuan</label>
                        <button type="button" @click="tambahPoin()" class="py-1.5 px-3 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg border border-transparent bg-blue-100 text-blue-800 hover:bg-blue-200">
                            <svg class="flex-shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Tambah Poin
                        </button>
                    </div>

                    <div class="space-y-3" id="poin-container">
                        <template x-for="(poin, index) in daftarPoin" :key="poin.id">
                            <div class="flex items-start gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div class="pt-2 cursor-move text-gray-400 hover:text-gray-600">
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                </div>
                                <span class="pt-2 text-sm font-semibold text-gray-700 w-5 text-center" x-text="(index + 1) + '.'"></span>
                                <textarea :name="'poin[' + index + ']'" x-model="poin.teks" rows="2" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required></textarea>
                                <button type="button" @click="hapusPoin(index)" class="pt-2 text-gray-400 hover:text-red-600 transition-colors" title="Hapus poin">
                                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        
                        <div x-show="daftarPoin.length === 0" class="text-center py-6 bg-gray-50 border border-dashed border-gray-300 rounded-lg">
                            <p class="text-sm text-gray-500">Belum ada poin persetujuan.</p>
                        </div>
                    </div>
                </div>

                <!-- Teks Penutup -->
                <div class="pt-4 border-t border-gray-200">
                    <label for="teks_penutup" class="block text-sm font-medium mb-2">Teks Penutup <span class="text-red-500">*</span></label>
                    <textarea id="teks_penutup" name="teks_penutup" rows="3" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm border" required>{{ old('teks_penutup', $template->teks_penutup ?? "Demikian surat persetujuan ini saya buat dengan sadar dan tanpa paksaan dari pihak mana pun.\n\nSurabaya, {tanggal}") }}</textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                <button type="submit" class="py-2 px-5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm">
                    Simpan Template
                </button>
            </div>
        </form>
    </div>

    <!-- Informasi Kolom -->
    <div class="space-y-6">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <h3 class="text-sm font-bold text-blue-800 flex items-center gap-2 mb-3">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                Variabel Otomatis (Placeholder)
            </h3>
            <p class="text-xs text-blue-700 mb-3">Gunakan kode di bawah ini pada teks pembuka, teks poin, atau teks penutup. Sistem akan mengubahnya dengan data pasien saat surat diterbitkan.</p>
            
            <ul class="space-y-2 text-xs">
                <li class="flex items-center justify-between border-b border-blue-200/50 pb-2">
                    <code class="text-blue-900 bg-blue-200/50 px-1.5 py-0.5 rounded font-bold">{nama_pasien}</code>
                    <span class="text-blue-700">Nama lengkap</span>
                </li>
                <li class="flex items-center justify-between border-b border-blue-200/50 pb-2">
                    <code class="text-blue-900 bg-blue-200/50 px-1.5 py-0.5 rounded font-bold">{nik}</code>
                    <span class="text-blue-700">Nomor Induk Kependudukan</span>
                </li>
                <li class="flex items-center justify-between border-b border-blue-200/50 pb-2">
                    <code class="text-blue-900 bg-blue-200/50 px-1.5 py-0.5 rounded font-bold">{no_rm}</code>
                    <span class="text-blue-700">Nomor Rekam Medis</span>
                </li>
                <li class="flex items-center justify-between border-b border-blue-200/50 pb-2">
                    <code class="text-blue-900 bg-blue-200/50 px-1.5 py-0.5 rounded font-bold">{tanggal_lahir}</code>
                    <span class="text-blue-700">Tanggal Lahir (DD MMMM YYYY)</span>
                </li>
                <li class="flex items-center justify-between border-b border-blue-200/50 pb-2">
                    <code class="text-blue-900 bg-blue-200/50 px-1.5 py-0.5 rounded font-bold">{alamat}</code>
                    <span class="text-blue-700">Alamat lengkap</span>
                </li>
                <li class="flex items-center justify-between">
                    <code class="text-blue-900 bg-blue-200/50 px-1.5 py-0.5 rounded font-bold">{tanggal}</code>
                    <span class="text-blue-700">Tanggal terbit dokumen</span>
                </li>
            </ul>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 mb-2">Informasi Tambahan</h3>
            <p class="text-xs text-gray-600">Perubahan pada template ini <strong>tidak akan memengaruhi</strong> surat persetujuan yang sudah ditandatangani atau diterbitkan sebelumnya. Perubahan hanya berlaku untuk surat yang diterbitkan setelah template ini disimpan.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $defaultPoin = [
        ['id' => 'p1', 'teks' => 'Saya memahami bahwa data kesehatan saya akan dicatat dan dikelola oleh fasilitas kesehatan.'],
        ['id' => 'p2', 'teks' => 'Saya bersedia mengikuti anjuran medis dan program pemeriksaan rutin yang diberikan.'],
        ['id' => 'p3', 'teks' => 'Saya menyetujui bahwa dokter/petugas berhak mengambil tindakan darurat jika diperlukan demi keselamatan jiwa saya.']
    ];
    
    $poinData = $template && $template->poinTemplates->count() > 0 
        ? $template->poinTemplates->map(function($p) { 
            return ['id' => uniqid(), 'teks' => $p->teks_poin]; 
        })->values()->toArray() 
        : $defaultPoin;
@endphp
<script>
    // Ambil data poin dari PHP jika ada, jika tidak ada siapkan beberapa poin default
    const existingPoin = @json($poinData);

    document.addEventListener('alpine:init', () => {
        Alpine.data('poinManager', () => ({
            daftarPoin: existingPoin,
            
            tambahPoin() {
                this.daftarPoin.push({
                    id: Date.now().toString(36) + Math.random().toString(36).substr(2),
                    teks: ''
                });
            },
            
            hapusPoin(index) {
                if(confirm('Hapus poin ini?')) {
                    this.daftarPoin.splice(index, 1);
                }
            }
            
            // Drag and Drop bisa ditambahkan nanti jika diperlukan
        }));
    });
</script>
@endpush
