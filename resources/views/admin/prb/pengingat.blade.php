@extends('layouts.app')

@push('styles')
<style>
/* Memaksa scrollbar bawah selalu terlihat agar user tahu tabel bisa digeser */
.custom-scrollbar-x::-webkit-scrollbar {
    height: 8px;
    display: block !important;
}
.custom-scrollbar-x::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.custom-scrollbar-x::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar-x::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.custom-scrollbar-x {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}
</style>
@endpush

@section('content')
<header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Dashboard Pengingat WA</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar pasien PRB yang sudah atau lewat jatuh tempo pengambilan obat.</p>
    </div>
</header>

<div class="flex flex-col gap-6">
    <!-- Search Box -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form action="{{ route('admin.prb.pengingat') }}" method="GET" class="flex items-center gap-3">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                    <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="py-2.5 ps-10 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Cari Nama / NIK Pasien...">
            </div>
            <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.prb.pengingat') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto pb-2 custom-scrollbar-x">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterlambatan</th>
                        <th scope="col" class="px-6 py-3 text-end text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($riwayatPengingat as $riwayat)
                    @php
                        $pasien = $riwayat->pasienPrb->pasien;
                        $pesan = "Halo Bpk/Ibu {$pasien->nama},\n\nMengingatkan bahwa jadwal pengambilan obat Program Rujuk Balik (PRB) Anda sudah jatuh tempo pada tanggal " . \Carbon\Carbon::parse($riwayat->tanggal_jatuh_tempo)->translatedFormat('d M Y') . ".\n\nMohon segera datang ke faskes untuk mengambil obat. Terima kasih.";
                        $encodedPesan = urlencode($pesan);
                        $noHp = $pasien->no_hp;
                        $waLink = $noHp ? "https://wa.me/{$noHp}?text={$encodedPesan}" : null;
                    @endphp
                    <tr class="hover:bg-gray-50" x-data="{ 
                        diingatkan: {{ $riwayat->terakhir_diingatkan ? 'true' : 'false' }}, 
                        terakhir: '{{ $riwayat->terakhir_diingatkan ? $riwayat->terakhir_diingatkan->translatedFormat('d M Y, H:i') : '' }}',
                        loading: false,
                        tandai() {
                            if(!this.diingatkan) {
                                this.loading = true;
                                fetch('{{ route('admin.prb.pengingat.tandai', $riwayat->id) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if(data.success) {
                                        this.diingatkan = true;
                                        this.terakhir = data.terakhir_diingatkan;
                                    }
                                    this.loading = false;
                                })
                                .catch(() => { this.loading = false; });
                            }
                        }
                    }">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-800">{{ $pasien->nama }}</div>
                            <div class="text-xs text-gray-500">NIK: {{ $pasien->nik }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">HP: {{ $pasien->no_hp ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">
                            {{ \Carbon\Carbon::parse($riwayat->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($riwayat->selisih_hari > 0)
                                <span class="inline-flex items-center gap-x-1.5 py-1 px-2 rounded-md text-xs font-semibold bg-red-100 text-red-800">
                                    Telat {{ $riwayat->selisih_hari }} Hari
                                </span>
                            @elseif($riwayat->selisih_hari === 0)
                                <span class="inline-flex items-center gap-x-1.5 py-1 px-2 rounded-md text-xs font-semibold bg-amber-100 text-amber-800">
                                    Hari Ini
                                </span>
                            @else
                                <span class="inline-flex items-center gap-x-1.5 py-1 px-2 rounded-md text-xs font-semibold bg-blue-100 text-blue-800">
                                    H{{ $riwayat->selisih_hari }}
                                </span>
                            @endif
                            
                            <template x-if="diingatkan">
                                <div class="mt-2 flex items-center gap-1 text-[11px] text-green-600 font-medium">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    Diingatkan: <span x-text="terakhir"></span>
                                </div>
                            </template>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                            @if($waLink)
                            <a href="{{ $waLink }}" target="_blank" @click="tandai()" class="inline-flex items-center gap-x-2 py-2 px-3 text-sm font-semibold rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                Hubungi via WA
                            </a>
                            @else
                            <button disabled class="inline-flex items-center gap-x-2 py-2 px-3 text-sm font-medium rounded-lg border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                No HP Kosong
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">
                            Tidak ada pasien PRB yang perlu dihubungi (Semua sudah mengambil obat atau belum jatuh tempo).
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($riwayatPengingat->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $riwayatPengingat->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
