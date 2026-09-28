@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                Edit Jadwal Layanan
            </h2>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.jadwal.update', $jadwal->id) }}" method="POST" x-data="jadwalForm()">
                @csrf
                @method('PUT')
                
                <div class="space-y-5">
                    <!-- Form Group -->
                    <div>
                        <label for="tanggal" class="block text-sm font-medium mb-2">Tanggal Layanan</label>
                        <div class="relative">
                            <input type="text" id="tanggal" name="tanggal" value="{{ old('tanggal', $jadwal->tanggal) }}" class="py-3 ps-10 pe-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 border" placeholder="Pilih tanggal">
                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5">
                                <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            </div>
                        </div>
                        @error('tanggal')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- End Form Group -->

                    <!-- Form Group -->
                    <div>
                        <label for="keterangan" class="block text-sm font-medium mb-2">Keterangan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <textarea id="keterangan" name="keterangan" rows="3" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 border">{{ old('keterangan', $jadwal->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- End Form Group -->

                    <!-- Checkbox -->
                    <div class="flex items-center mt-4">
                        <div class="flex">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="shrink-0 mt-0.5 border-gray-200 rounded text-blue-600 focus:ring-blue-500" {{ old('is_active', $jadwal->is_active) ? 'checked' : '' }}>
                        </div>
                        <div class="ms-3">
                            <label for="is_active" class="text-sm text-gray-800 font-medium">Jadikan sebagai jadwal aktif</label>
                            <p class="text-xs text-gray-500">Hanya boleh ada 1 jadwal aktif. Jadwal aktif akan digunakan di pesan WhatsApp.</p>
                        </div>
                    </div>
                    <!-- End Checkbox -->

                    <!-- Pasien Selection -->
                    <div class="mt-8 border-t border-gray-200 pt-8">
                        <div class="mb-5 flex flex-col">
                            <label class="text-base font-semibold text-gray-800">Pilih Pasien</label>
                            <span class="text-sm text-gray-500">Pasien yang dicentang akan dijadwalkan hadir pada tanggal tersebut.</span>
                        </div>
                        
                                                <div class="flex gap-2 mb-4 overflow-x-auto custom-scrollbar-x pb-2" x-show="selectedMonth" style="display: none;">
                            <button type="button" @click="searchStatus = ''; performSearch()" 
                                :class="searchStatus === '' ? 'bg-gray-800 text-white border-transparent shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 shadow-sm'" 
                                class="px-4 py-1.5 border rounded-full text-sm font-medium transition-colors whitespace-nowrap">Semua</button>
                            
                            <button type="button" @click="searchStatus = 'Rutin'; performSearch()" 
                                :class="searchStatus === 'Rutin' ? 'bg-emerald-100 text-emerald-800 border-emerald-200 shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 shadow-sm'" 
                                class="px-4 py-1.5 border rounded-full text-sm font-medium transition-colors whitespace-nowrap">Rutin</button>
                            
                            <button type="button" @click="searchStatus = 'Jarang'; performSearch()" 
                                :class="searchStatus === 'Jarang' ? 'bg-yellow-100 text-yellow-800 border-yellow-200 shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 shadow-sm'" 
                                class="px-4 py-1.5 border rounded-full text-sm font-medium transition-colors whitespace-nowrap">Jarang Hadir</button>
                            
                            <button type="button" @click="searchStatus = 'Pasif'; performSearch()" 
                                :class="searchStatus === 'Pasif' ? 'bg-red-100 text-red-800 border-red-200 shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 shadow-sm'" 
                                class="px-4 py-1.5 border rounded-full text-sm font-medium transition-colors whitespace-nowrap">Pasif</button>
                        </div>
                        
                        <div class="relative mb-4" x-show="selectedMonth" style="display: none;">
                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5">
                                <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </div>
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch" class="py-3 ps-10 pe-4 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 border" placeholder="Ketik nama atau NIK/RM pasien untuk mencari...">
                            
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none" x-show="isSearching">
                                <span class="flex h-5 items-center justify-center gap-0.5" aria-hidden="true">
                                    <span class="h-3 w-1 rounded-sm bg-gray-300 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0s;"></span>
                                    <span class="h-3 w-1 rounded-sm bg-gray-400 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.12s;"></span>
                                    <span class="h-3 w-1 rounded-sm bg-gray-500 origin-bottom" style="animation: spinner-bars 1s ease-in-out infinite 0.24s;"></span>
                                </span>
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto p-1 -mx-1" x-show="selectedMonth && displayPasiens.length > 0" style="display: none;">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="pasien in displayPasiens" :key="pasien.id">
                                    <label :for="'pasien_' + pasien.id" 
                                           class="flex p-3 w-full border border-gray-200 rounded-lg text-sm focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition-colors"
                                           :class="isPatientDisabled(pasien.id) ? 'bg-gray-100 opacity-70' : (isSelected(pasien.id) ? 'bg-blue-50 border-blue-200 ring-1 ring-blue-500' : 'bg-white hover:bg-gray-50 cursor-pointer')">
                                        <input type="checkbox" name="pasien_ids[]" :value="pasien.id" :id="'pasien_' + pasien.id"
                                            class="shrink-0 mt-0.5 size-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                            :disabled="isPatientDisabled(pasien.id)"
                                            :checked="isSelected(pasien.id)"
                                            @change="togglePasien(pasien, $event)">
                                        <div class="ms-3 flex flex-col w-full">
                                            <span class="text-sm font-semibold" :class="isPatientDisabled(pasien.id) ? 'text-gray-500' : 'text-gray-800'" x-text="pasien.nama"></span>
                                            <div class="flex flex-col mt-0.5 gap-1" :class="isPatientDisabled(pasien.id) ? 'text-gray-400' : 'text-gray-500'">
                                                <div class="text-xs flex flex-wrap items-center gap-2">
                                                    <span x-text="'NIK: ' + (pasien.nik ? pasien.nik : '-')"></span>
                                                    <template x-if="pasien.kunjungan_terakhir">
                                                        <span class="inline-flex items-center py-0.5 px-1.5 rounded bg-blue-50 text-blue-700 font-medium border border-blue-100" style="font-size: 10px;">
                                                            <span x-text="'Terakhir: ' + pasien.kunjungan_terakhir"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!pasien.kunjungan_terakhir">
                                                        <span class="inline-flex items-center py-0.5 px-1.5 rounded bg-gray-50 text-gray-500 font-medium border border-gray-200" style="font-size: 10px;">
                                                            <span>Belum ada kunjungan</span>
                                                        </span>
                                                    </template>
                                                </div>
                                                <span class="text-xs line-clamp-1" :title="pasien.alamat" x-text="pasien.alamat ? pasien.alamat : '-'"></span>
                                            </div>
                                            
                                            <span x-show="isPatientDisabled(pasien.id)" class="text-[10px] text-orange-600 mt-1.5 font-bold uppercase tracking-wider bg-orange-100 px-1.5 py-0.5 rounded-md inline-block w-max" style="display: none;" x-text="'Sudah dijadwalkan: ' + getScheduledDate(pasien.id)"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                        
                        <div x-show="selectedMonth && displayPasiens.length === 0 && searchQuery.length >= 2 && !isSearching" class="text-center py-6" style="display: none;">
                            <span class="text-sm text-gray-500">Tidak ditemukan pasien dengan kata kunci tersebut.</span>
                        </div>
                        
                        <!-- Empty State -->
                        <div x-show="!selectedMonth" class="flex flex-col items-center justify-center p-8 border border-gray-200 border-dashed rounded-xl bg-gray-50 text-center">
                            <svg class="size-10 text-gray-400 mb-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            <p class="text-sm text-gray-600 font-medium">Tanggal belum dipilih</p>
                            <p class="text-xs text-gray-500 mt-1">Silakan pilih tanggal layanan terlebih dahulu untuk melihat dan mencentang daftar pasien.</p>
                        </div>
                        
                        <p x-show="selectedMonth" class="text-xs text-gray-500 mt-3" style="display: none;">*Pasien dengan keterangan abu-abu berarti sudah dijadwalkan pada bulan yang sama di jadwal lain.</p>
                    </div>
                    <!-- End Pasien Selection -->
                </div>

                <div class="mt-6 flex justify-end gap-x-3">
                    <a href="{{ route('admin.jadwal.index') }}" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                        Perbarui Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- End Card -->
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
@keyframes spinner-bars {
  0% { transform: scaleY(0.5); opacity: 0.5; }
  20% { transform: scaleY(1); opacity: 1; }
  40% { transform: scaleY(0.5); opacity: 0.5; }
  100% { transform: scaleY(0.5); opacity: 0.5; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
    document.addEventListener("alpine:init", () => {
        Alpine.data('jadwalForm', () => ({
            selectedMonth: null,
            patientSchedules: @json($patientSchedules),
            searchQuery: '',
            searchStatus: '',
            isSearching: false,
            searchResults: [],
            selectedPasiens: @json($initialPasiens ?? []),
            
            get displayPasiens() {
                const map = new Map();
                this.selectedPasiens.forEach(p => map.set(p.id, p));
                this.searchResults.forEach(p => map.set(p.id, p));
                return Array.from(map.values());
            },
            
            isSelected(id) {
                return this.selectedPasiens.some(p => p.id === id);
            },
            
            togglePasien(pasien, event) {
                if (event.target.checked) {
                    if (!this.isSelected(pasien.id)) {
                        this.selectedPasiens.push(pasien);
                    }
                } else {
                    this.selectedPasiens = this.selectedPasiens.filter(p => p.id !== pasien.id);
                }
            },
            
            async performSearch() {
                if (!this.searchStatus && this.searchQuery.trim().length < 2) {
                    this.searchResults = [];
                    return;
                }
                
                this.isSearching = true;
                try {
                    const tanggalVal = document.getElementById('tanggal').value;
                    let url = `/admin/jadwal/search-pasien?q=${encodeURIComponent(this.searchQuery.trim())}`;
                    if (this.searchStatus) url += `&status=${encodeURIComponent(this.searchStatus)}`;
                    if (tanggalVal) url += `&tanggal=${encodeURIComponent(tanggalVal)}`;
                    
                    const response = await fetch(url);
                    if (response.ok) {
                        this.searchResults = await response.json();
                    }
                } catch (e) {
                    console.error("Gagal mengambil data pasien", e);
                } finally {
                    this.isSearching = false;
                }
            },
            
            init() {
                const initialDate = document.getElementById('tanggal').value;
                if (initialDate) {
                    this.selectedMonth = initialDate.substring(0, 7);
                }

                flatpickr("#tanggal", {
                    locale: "id",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d F Y",
                    onChange: (selectedDates, dateStr) => {
                        this.selectedMonth = dateStr ? dateStr.substring(0, 7) : null;
                        
                        this.$nextTick(() => {
                            this.selectedPasiens = this.selectedPasiens.filter(p => !this.isPatientDisabled(p.id));
                        });
                    }
                });
            },
            
            isPatientDisabled(patientId) {
                if (!this.selectedMonth) return false;
                const schedules = this.patientSchedules[patientId] || [];
                return schedules.some(s => s.month === this.selectedMonth && s.jadwal_id !== {{ $jadwal->id }});
            },
            
            getScheduledDate(patientId) {
                if (!this.selectedMonth) return null;
                const schedules = this.patientSchedules[patientId] || [];
                const schedule = schedules.find(s => s.month === this.selectedMonth && s.jadwal_id !== {{ $jadwal->id }});
                return schedule ? schedule.formatted_date : null;
            }
        }));
    });
</script>
@endpush
