<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudah Ditandatangani - Informed Consent</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-sm w-full text-center space-y-6">
        <div class="mx-auto w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div>
            <h1 class="text-xl font-bold text-gray-900">Sudah Ditandatangani</h1>
            <p class="text-sm text-gray-600 mt-2">
                Dokumen ini sudah ditandatangani oleh <strong>{{ $persetujuan->pasien->nama }}</strong> pada {{ $persetujuan->ditandatangani_pada->translatedFormat('d F Y, H:i') }}.
            </p>
        </div>

        <p class="text-xs text-gray-400">Anda dapat menutup halaman ini.</p>
    </div>
</body>
</html>
