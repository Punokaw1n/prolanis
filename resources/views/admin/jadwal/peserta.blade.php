@extends('layouts.app')

@section('content')
<div class="flex flex-col">
    <!-- Header Title -->
    <div class="mb-4 grid gap-3 md:flex md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Peserta Terjadwal
            </h2>
            <p class="text-sm text-gray-600">
                Pantau daftar pasien yang sudah dijadwalkan per tanggal layanan.
            </p>
        </div>
    </div>
    
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- Search & Filter Bar -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <form action="{{ route('admin.jadwal.peserta') }}" method="GET" class="grid sm:flex gap-3">
                        
                        <!-- Search Box -->
                        <div class="relative sm:max-w-xs w-full">
                            <label class="sr-only">Cari</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="py-2.5 px-3 ps-10 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500" placeholder="Cari nama, NIK, atau No RM...">
                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5">
                                <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </div>
                        </div>
                        
                        <!-- Date Filter -->
                        <div class="sm:w-48 relative">
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="py-2.5 px-3 block w-full border-gray-200 shadow-sm rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 text-gray-600" onchange="this.form.submit()" title="Filter berdasarkan tanggal jadwal">
                        </div>
                        
                        <div class="flex gap-2">
                            <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
                                Terapkan
                            </button>
                            
                            @if(request('search') || request('tanggal'))
                            <a href="{{ route('admin.jadwal.peserta') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent text-red-600 hover:bg-red-50 hover:text-red-800 focus:outline-none focus:bg-red-50">
                                Hapus Filter
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
                <!-- End Search & Filter -->

                <!-- Desktop & Tablet Table View -->
                <div class="hidden sm:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Nama Pasien</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Kontak</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Tanggal Terjadwal</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Ket. Jadwal</th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($peserta as $p)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-800">{{ $p->nama }}</span>
                                        <span class="text-xs text-gray-500 mt-0.5">NIK: {{ $p->nik ?? '-' }}</span>
                                        <span class="text-xs text-gray-500 line-clamp-1" title="{{ $p->alamat }}">{{ $p->alamat ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    {{ $p->no_hp ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-[200px] truncate">
                                    {{ $p->keterangan ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    <a class="text-blue-600 hover:text-blue-800" href="{{ route('admin.jadwal.edit', $p->jadwal_id) }}" title="Buka form edit jadwal ini">
                                        Edit Jadwal
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                    @if(request('search') || request('tanggal'))
                                        Tidak ada pasien terjadwal yang cocok dengan filter pencarian.
                                    @else
                                        Belum ada satupun pasien yang di-ploting ke dalam jadwal.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden flex flex-col divide-y divide-gray-200">
                    @forelse($peserta as $p)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-800">{{ $p->nama }}</span>
                                <span class="text-xs text-gray-500 mt-0.5">NIK: {{ $p->nik ?? '-' }}</span>
                                <span class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $p->alamat ?? '-' }}</span>
                            </div>
                            <a href="{{ route('admin.jadwal.edit', $p->jadwal_id) }}" class="inline-flex items-center gap-1.5 py-1.5 px-2.5 rounded-md text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 whitespace-nowrap">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                Edit
                            </a>
                        </div>
                        <div class="grid grid-cols-2 gap-2 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                            <div>
                                <span class="block text-[10px] uppercase text-gray-500 font-semibold mb-0.5">Tanggal</span>
                                <span class="text-xs font-medium text-blue-600">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase text-gray-500 font-semibold mb-0.5">Kontak</span>
                                <span class="text-xs font-medium text-gray-800">{{ $p->no_hp ?? '-' }}</span>
                            </div>
                            @if($p->keterangan)
                            <div class="col-span-2 mt-1 pt-2 border-t border-gray-200">
                                <span class="block text-[10px] uppercase text-gray-500 font-semibold mb-0.5">Keterangan</span>
                                <span class="text-xs text-gray-600">{{ $p->keterangan }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-sm text-gray-500">
                        @if(request('search') || request('tanggal'))
                            Tidak ada pasien terjadwal yang cocok dengan filter pencarian.
                        @else
                            Belum ada satupun pasien yang di-ploting ke dalam jadwal.
                        @endif
                    </div>
                    @endforelse
                </div>

                <!-- Footer / Pagination -->
                @if($peserta->hasPages())
                <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-t border-gray-200">
                    {{ $peserta->links() }}
                </div>
                @endif
                <!-- End Footer -->
            </div>
        </div>
    </div>
</div>
@endsection
