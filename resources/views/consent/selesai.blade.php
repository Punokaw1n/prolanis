<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berhasil - Informed Consent</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-sm w-full text-center space-y-6">
        {{-- Success Icon --}}
        <div class="mx-auto w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <div>
            <h1 class="text-xl font-bold text-gray-900">Tanda Tangan Berhasil!</h1>
            <p class="text-sm text-gray-600 mt-2">
                Terima kasih, <strong>{{ $persetujuan->pasien->nama }}</strong>. Surat persetujuan Anda telah berhasil ditandatangani.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 text-left space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Dokumen</span>
                <span class="font-medium text-gray-800">{{ $persetujuan->salinan_judul }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Waktu</span>
                <span class="font-medium text-gray-800">{{ $persetujuan->ditandatangani_pada->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Status</span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Ditandatangani
                </span>
            </div>
        </div>

        <p class="text-xs text-gray-400">Anda dapat menutup halaman ini sekarang.</p>
    </div>
</body>
</html>
