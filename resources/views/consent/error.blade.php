<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }} - Informed Consent</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-sm w-full text-center space-y-6">
        <div class="mx-auto w-20 h-20 rounded-full bg-red-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" x2="12" y1="8" y2="12"/>
                <line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
        </div>

        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $judul }}</h1>
            <p class="text-sm text-gray-600 mt-2">{{ $pesan }}</p>
        </div>

        <p class="text-xs text-gray-400">Silakan hubungi petugas di loket pendaftaran untuk bantuan lebih lanjut.</p>
    </div>
</body>
</html>
