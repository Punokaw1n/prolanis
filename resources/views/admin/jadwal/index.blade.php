@extends('layouts.app')

@section('content')
<div class="flex flex-col">
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">
                            Jadwal Layanan Prolanis
                        </h2>
                        <p class="text-sm text-gray-600">
                            Daftar tanggal layanan Prolanis yang dibuka.
                        </p>
                    </div>

                    <div>
                        <div class="inline-flex gap-x-2">
                            <a class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" href="{{ route('admin.jadwal.create') }}">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Tambah Jadwal
                            </a>
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Table -->
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Dibuat Oleh</th>
                            <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($jadwal as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($item->is_active)
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Aktif Digunakan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                {{ $item->pembuat->nama ?? 'Sistem' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                <a class="text-blue-600 hover:text-blue-800 mr-3" href="{{ route('admin.jadwal.edit', $item->id) }}">Edit</a>
                                <div x-data="{ open: false }" class="inline-block">
                                    <button @click="open = true" type="button" class="text-red-600 hover:text-red-800">Hapus</button>

                                <!-- AlpineJS Delete Modal -->
                                    <template x-teleport="body">
                                        <div x-show="open" style="display: none;" class="fixed inset-0 z-[80] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                            <!-- Backdrop -->
                                            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

                                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                                <!-- Modal Panel -->
                                                <div x-show="open" 
                                                    x-transition:enter="ease-out duration-300" 
                                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                                    x-transition:leave="ease-in duration-200" 
                                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                                    @click.away="open = false"
                                                    class="relative inline-block w-full max-w-lg p-4 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl sm:p-7 rounded-xl sm:my-8 sm:align-middle">
                                                    
                                                    <div class="text-center">
                                                        <svg class="size-14 mx-auto text-red-500 mb-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                        <h3 class="text-lg font-bold text-gray-800" id="modal-title">Hapus Jadwal?</h3>
                                                        <p class="text-sm text-gray-500 mt-2">Apakah Anda yakin ingin menghapus jadwal <strong>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</strong>? Data absensi terkait jadwal ini juga mungkin akan terdampak.</p>
                                                    </div>

                                                    <div class="mt-6 flex justify-center gap-x-3">
                                                        <button @click="open = false" type="button" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                                                            Batal
                                                        </button>
                                                        <form action="{{ route('admin.jadwal.destroy', $item->id) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-red-600 text-white hover:bg-red-700">
                                                                Ya, Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                Belum ada jadwal layanan yang ditambahkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <!-- End Table -->

                <!-- Footer -->
                @if($jadwal->total() > 0)
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $jadwal->links() }}
                </div>
                @endif
                <!-- End Footer -->
            </div>
        </div>
    </div>
</div>
@endsection
