@extends('layouts.app')

@section('content')

{{-- Header --}}
<header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="block text-2xl font-bold text-gray-800 sm:text-3xl">Terbitkan Informed Consent</h1>
        <p class="mt-2 text-lg text-gray-600">Cari pasien, pratinjau surat, lalu terbitkan dan hasilkan QR Code.</p>
    </div>
    <a href="{{ route('admin.persetujuan.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
        &larr; Kembali ke Arsip
    </a>
</header>

<div class="flex flex-col gap-6">

    {{-- STEP 1: CARI PASIEN --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form action="{{ route('admin.persetujuan.create') }}" method="GET" class="flex items-center gap-3">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                    <svg class="flex-shrink-0 size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="py-2.5 ps-10 pe-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                    placeholder="Ketik nama atau NIK pasien...">
            </div>
            <button type="submit" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 min-w-[80px] justify-center">
                Cari
            </button>
            @if(request('search'))
            <a href="{{ route('admin.persetujuan.create') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- HASIL PENCARIAN --}}
    @if(request()->has('search'))
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Hasil Pencarian &mdash;
                @if(count($pasiens) > 0)
                    <span class="text-blue-600">{{ count($pasiens) }} pasien ditemukan</span>
                @else
                    <span class="text-red-500">Tidak ditemukan</span>
                @endif
            </p>
        </div>

        @if(count($pasiens) > 0)
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach($pasiens as $p)
                    @php $isSelected = request('pasien_id') == $p->id; @endphp
                    <a href="{{ route('admin.persetujuan.create', ['pasien_id' => $p->id, 'search' => request('search')]) }}"
                        class="flex items-center gap-3 p-3 border rounded-lg transition-all
                            {{ $isSelected
                                ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-200 shadow-sm'
                                : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50/30' }}">
                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $isSelected ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                            {{ strtoupper(substr($p->nama, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $p->nama }}</p>
                            <p class="text-xs text-gray-500">NIK: {{ $p->nik ?? '-' }}</p>
                        </div>
                        @if($isSelected)
                        <svg class="flex-shrink-0 size-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        @else
        <div class="p-6 text-center">
            <svg class="mx-auto size-10 text-gray-300 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p class="text-sm text-gray-500">Tidak ada pasien yang cocok dengan &ldquo;<strong>{{ request('search') }}</strong>&rdquo;.</p>
        </div>
        @endif
    </div>
    @endif

    {{-- STEP 2: PRATINJAU SURAT --}}
    @if(!$template)
    <div class="bg-amber-50 border border-amber-200 rounded-xl shadow-sm p-6 text-center">
        <p class="text-amber-800 text-sm font-medium">Template Informed Consent belum diatur oleh Admin.</p>
        @if(auth()->user()->peran === 'admin')
        <a href="{{ route('admin.template.index') }}" class="mt-2 inline-block text-sm text-blue-600 hover:underline">Buat Template &rarr;</a>
        @endif
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800">Pratinjau Surat</h2>
            @if($pasien)
                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Pasien: {{ $pasien->nama }}
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                    Pilih pasien terlebih dahulu
                </span>
            @endif
        </div>

        @php
            $replaceText = function($text) use ($pasien) {
                if (!$text) return '';
                if (!$pasien) return nl2br(htmlspecialchars($text));
                $text = htmlspecialchars($text);
                $text = str_replace('{nama_pasien}', $pasien->nama, $text);
                $text = str_replace('{nik}', $pasien->nik ?? '-', $text);
                $text = str_replace('{no_rm}', $pasien->no_rm ?? '-', $text);
                $text = str_replace('{tanggal_lahir}', $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y') : '-', $text);
                $text = str_replace('{alamat}', $pasien->alamat ?? '-', $text);
                $text = str_replace('{tanggal}', now()->translatedFormat('d F Y'), $text);
                return nl2br($text);
            };
        @endphp

        <div class="bg-gray-50 p-5 sm:p-8">
            {{-- Dokumen Surat --}}
            <div class="bg-white border border-gray-300 shadow mx-auto max-w-2xl p-6 sm:p-10">
                <div class="text-center text-base font-bold uppercase tracking-wide border-b-2 border-gray-900 pb-3 mb-6">
                    {!! $replaceText($template->judul) !!}
                </div>

                <div class="text-sm text-gray-800 leading-relaxed mb-5">
                    {!! $replaceText($template->teks_pembuka) !!}
                </div>

                @if($template->poinTemplates->count() > 0)
                <div class="text-sm text-gray-800 space-y-2 mb-5">
                    @foreach($template->poinTemplates as $i => $poin)
                    <div class="flex gap-2">
                        <span class="font-semibold min-w-[20px] text-gray-700">{{ $i + 1 }}.</span>
                        <div class="leading-relaxed">{!! $replaceText($poin->teks_poin) !!}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="text-sm text-gray-800 leading-relaxed">
                    {!! $replaceText($template->teks_penutup) !!}
                </div>

                <div class="mt-10 flex justify-end">
                    <div class="text-center w-44">
                        <div class="h-14 mb-2 flex items-center justify-center bg-gray-50 border border-dashed border-gray-300 rounded text-xs text-gray-400 italic">
                            Tanda Tangan
                        </div>
                        <div class="border-b border-gray-700 mb-1"></div>
                        <p class="text-xs font-semibold text-gray-700">{{ $pasien ? $pasien->nama : '(Nama Pasien)' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($pasien)
        <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-xs text-gray-500">
                QR Code berlaku <strong class="text-gray-700">1 jam</strong> setelah diterbitkan.
            </p>
            <form action="{{ route('admin.persetujuan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pasien_id" value="{{ $pasien->id }}">
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                <button type="submit" class="w-full sm:w-auto py-2.5 px-5 inline-flex items-center justify-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-green-600 text-white hover:bg-green-700 shadow-sm">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Terbitkan & Buat QR Code
                </button>
            </form>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
