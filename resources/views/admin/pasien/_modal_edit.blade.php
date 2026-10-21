{{-- Modal Edit Data Pasien (Reusable Component) --}}
<template x-teleport="body">
    <div x-show="editPasienModalOpen" 
         x-cloak
         class="fixed inset-0 overflow-y-auto" 
         aria-labelledby="modal-edit-pasien-title" 
         role="dialog" 
         aria-modal="true" 
         style="display: none; z-index: 9999;">
        
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 transition-opacity" 
             style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);" 
             @click="editPasienModalOpen = false"></div>

        <!-- Modal Dialog Box -->
        <div class="min-h-full flex items-center justify-center p-4 sm:p-6" style="position: relative; z-index: 10000;">
            <div x-show="editPasienModalOpen" 
                 x-transition:enter="ease-out duration-250" 
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden text-left"
                 @click.outside="editPasienModalOpen = false">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50/40 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900" id="modal-edit-pasien-title">
                                Edit Data Pasien
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Perbarui identitas, kontak telepon, atau NIK pasien <span class="font-semibold text-gray-700">{{ $pasien->nama }}</span>.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="editPasienModalOpen = false" class="size-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-white flex items-center justify-center transition-colors">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Content -->
                <form action="{{ route('admin.pasien.update', $pasien->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        @if ($errors->any())
                            <div class="p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 space-y-1">
                                <p class="font-bold flex items-center gap-1.5">
                                    <svg class="size-4 text-red-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Terjadi kesalahan saat menyimpan data:
                                </p>
                                <ul class="list-disc list-inside ps-1 space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nama Lengkap Pasien -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Nama Lengkap Pasien <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama" value="{{ old('nama', $pasien->nama) }}" required 
                                       placeholder="Nama lengkap sesuai KTP..." 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                            </div>

                            <!-- NIK Pasien -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    NIK (Nomor Induk Kependudukan) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nik" value="{{ old('nik', $pasien->nik) }}" required maxlength="20"
                                       placeholder="16 digit NIK..." 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm font-mono text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                            </div>

                            <!-- No. Handphone / WhatsApp -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Nomor Handphone / WhatsApp
                                </label>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $pasien->no_hp) }}" maxlength="20"
                                       placeholder="Contoh: 08123456789..." 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm font-mono text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                            </div>

                            <!-- No. BPJS -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    No. BPJS / KIS
                                </label>
                                <input type="text" name="no_bpjs" value="{{ old('no_bpjs', $pasien->no_bpjs) }}" maxlength="30"
                                       placeholder="Nomor kartu BPJS..." 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm font-mono text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                            </div>

                            <!-- No. Rekam Medis (RM) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    No. Rekam Medis (No RM)
                                </label>
                                <input type="text" name="no_rm" value="{{ old('no_rm', $pasien->no_rm) }}" maxlength="30"
                                       placeholder="Nomor RM faskes..." 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm font-mono text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                            </div>

                            <!-- Tanggal Lahir -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Tanggal Lahir
                                </label>
                                <input type="date" name="tanggal_lahir" 
                                       value="{{ old('tanggal_lahir', $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('Y-m-d') : '') }}" 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Jenis Kelamin
                                </label>
                                <select name="jenis_kelamin" class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs bg-white">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="L" {{ old('jenis_kelamin', $pasien->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('jenis_kelamin', $pasien->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                            </div>

                            <!-- Pekerjaan -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Pekerjaan
                                </label>
                                <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $pasien->pekerjaan) }}" maxlength="100"
                                       placeholder="Pekerjaan pasien saat ini..." 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                            </div>

                            @if($pasien->pasienPrb)
                            <!-- Kode Diagnosa PRB -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Kode Diagnosa PRB
                                </label>
                                <input type="text" name="diagnosa" value="{{ old('diagnosa', $pasien->diagnosa) }}" maxlength="50"
                                       placeholder="Contoh: I11.9, E11.8..." 
                                       class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg text-sm font-mono text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">
                                <p class="text-[11px] text-gray-400 mt-1">Kode diagnosa penyakit kronis PRB (ICD-10)</p>
                            </div>
                            @endif

                            <!-- Alamat Domisili -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Alamat Domisili / Tempat Tinggal
                                </label>
                                <textarea name="alamat" rows="2" 
                                          placeholder="Alamat lengkap tempat tinggal pasien..." 
                                          class="py-2 px-3 block w-full border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-2xs">{{ old('alamat', $pasien->alamat) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" @click="editPasienModalOpen = false" 
                                class="py-2.5 px-4 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-100 shadow-2xs transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="py-2.5 px-5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex items-center gap-1.5 transition-colors">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
