@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Informed Consent</h1>
        <p class="text-sm text-gray-500 mt-1">Status dan QR Code persetujuan untuk pasien <span class="font-semibold text-gray-800">{{ $persetujuan->pasien->nama }}</span>.</p>
    </div>
    <div>
        <a href="{{ route('admin.persetujuan.index') }}" class="py-2 px-3 inline-flex items-center gap-x-1.5 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
            Kembali ke Arsip
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- QR Code Section -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center h-full flex flex-col justify-center items-center">
            @if($persetujuan->status === 'menunggu_tanda_tangan')
                <div class="mb-4">
                    <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                        <span class="size-1.5 inline-block bg-yellow-500 rounded-full"></span>
                        Menunggu Tanda Tangan Pasien
                    </span>
                </div>
                
                <p class="text-sm text-gray-600 mb-6">Minta pasien untuk memindai QR Code di bawah ini menggunakan kamera HP mereka.</p>
                
                <div class="bg-white p-4 border-2 border-gray-200 rounded-xl inline-block shadow-sm">
                    <div id="qrcode" class="mx-auto flex justify-center"></div>
                </div>
                
                <p class="text-xs text-red-500 mt-4 font-medium flex items-center justify-center gap-1">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Berlaku hingga: {{ $persetujuan->kedaluwarsa_pada->translatedFormat('H:i, d M Y') }}
                </p>
                
                <div class="mt-6 w-full pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">Atau bagikan link langsung:</p>
                    <div class="flex">
                        <input type="text" id="consent-url" readonly value="{{ url('/portal/consent/' . $persetujuan->token_qr) }}" class="py-2 px-3 block w-full border-gray-200 rounded-l-lg text-xs bg-gray-50 text-gray-500 focus:border-blue-500 focus:ring-blue-500">
                        <button type="button" onclick="copyUrl()" class="py-2 px-3 inline-flex justify-center items-center gap-x-2 text-xs font-semibold rounded-r-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                            Copy
                        </button>
                    </div>
                </div>

            @elseif($persetujuan->status === 'ditandatangani')
                <div class="mb-4">
                    <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        Sudah Ditandatangani
                    </span>
                </div>
                
                <div class="w-full mt-4">
                    <p class="text-xs text-gray-500 mb-2 font-medium uppercase tracking-wider text-left">Bukti Tanda Tangan</p>
                    <div class="border border-gray-200 rounded-lg p-2 bg-gray-50">
                        @if($persetujuan->tanda_tangan_path)
                            <!-- Placeholder image for now, later we map this to Storage -->
                            <img src="{{ Storage::url($persetujuan->tanda_tangan_path) }}" alt="Tanda Tangan" class="max-w-full h-auto mx-auto mix-blend-multiply">
                        @else
                            <div class="py-10 text-gray-400 text-xs italic">Gambar tidak tersedia</div>
                        @endif
                    </div>
                    
                    <div class="mt-4 text-left space-y-2 text-xs text-gray-600">
                        <p><span class="font-medium text-gray-800">Waktu:</span> {{ $persetujuan->ditandatangani_pada ? $persetujuan->ditandatangani_pada->translatedFormat('d F Y, H:i') : '-' }}</p>
                        <p><span class="font-medium text-gray-800">IP Address:</span> {{ $persetujuan->ip_address ?? '-' }}</p>
                    </div>
                </div>
                
                <a href="{{ route('admin.persetujuan.pdf', $persetujuan->id) }}" class="mt-6 w-full py-2.5 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                    <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Cetak PDF
                </a>

            @elseif($persetujuan->status === 'kedaluwarsa')
                <div class="mb-4">
                    <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        Kedaluwarsa
                    </span>
                </div>
                <p class="text-sm text-gray-600">Batas waktu untuk penandatanganan telah habis. Silakan terbitkan ulang consent ini jika pasien masih berada di tempat.</p>
                
                <a href="{{ route('admin.persetujuan.create', ['pasien_id' => $persetujuan->pasien_id]) }}" class="mt-6 w-full py-2.5 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-sm">
                    Terbitkan Ulang
                </a>
            @endif
        </div>
    </div>

    <!-- Salinan Surat Section -->
    <div class="lg:col-span-2">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 sm:p-10">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-6 pb-2 border-b border-gray-100">Salinan Dokumen (Final)</h3>
            
            <div class="text-center font-bold text-lg mb-8 uppercase border-b-2 border-black pb-4">
                {!! $persetujuan->salinan_judul !!}
            </div>
            
            <div class="text-sm text-gray-800 leading-relaxed mb-6">{!! nl2br(e($persetujuan->salinan_teks_pembuka)) !!}</div>
            
            <div class="text-sm text-gray-800 space-y-3 mb-6">
                @foreach($persetujuan->salinan_poin as $index => $poin)
                    <div class="flex gap-3">
                        <span class="font-medium min-w-[20px]">{{ $index + 1 }}.</span>
                        <div>{!! nl2br(e($poin)) !!}</div>
                    </div>
                @endforeach
            </div>
            
            <div class="text-sm text-gray-800 leading-relaxed">{!! nl2br(e($persetujuan->salinan_teks_penutup)) !!}</div>
            
            <div class="mt-12 flex justify-end">
                <div class="text-center w-48 pt-10">
                    <div class="border-b border-gray-800 w-full mb-1"></div>
                    <p class="text-xs font-semibold">{{ $persetujuan->pasien->nama }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($persetujuan->status === 'menunggu_tanda_tangan')
<!-- Pustaka QRCode.js via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var url = document.getElementById("consent-url").value;
        new QRCode(document.getElementById("qrcode"), {
            text: url,
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Polling setiap 3 detik untuk auto-refresh jika sudah ditandatangani
        setInterval(function() {
            fetch("{{ route('admin.persetujuan.status', $persetujuan->id) }}")
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'ditandatangani') {
                        window.location.reload();
                    }
                })
                .catch(err => console.error('Gagal mengecek status', err));
        }, 3000);
    });
    
    function copyUrl() {
        var copyText = document.getElementById("consent-url");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(copyText.value).then(function() {
            showToast("URL berhasil disalin ke clipboard!");
        });
    }

    function showToast(message) {
        // Cek apakah toast sudah ada, hapus jika ada
        let existing = document.getElementById('custom-toast');
        if (existing) existing.remove();

        let toast = document.createElement('div');
        toast.id = 'custom-toast';
        toast.className = 'fixed bottom-5 right-5 z-[90] flex items-center w-full max-w-xs p-4 space-x-3 text-gray-800 bg-white border border-gray-200 rounded-xl shadow-lg transition-all duration-300 translate-y-10 opacity-0';
        toast.innerHTML = `
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-teal-600 bg-teal-100 rounded-lg">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="ml-3 text-sm font-semibold">${message}</div>
        `;
        document.body.appendChild(toast);

        // Animasi masuk
        setTimeout(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);

        // Animasi keluar dan hapus
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endif
@endpush
