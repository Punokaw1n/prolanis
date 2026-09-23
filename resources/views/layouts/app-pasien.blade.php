<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Pasien - PROLANIS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.1.0/dist/chartjs-plugin-annotation.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-x-2">
                        <div class="flex items-center justify-center w-8 h-8 bg-emerald-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <span class="font-bold text-xl tracking-tight text-gray-900">Portal Pasien</span>
                    </div>

                    @auth('pasien')
                    <div class="flex items-center gap-x-4">
                        <span class="text-sm font-medium text-gray-600 hidden sm:block">Halo, {{ explode(' ', Auth::guard('pasien')->user()->nama)[0] }}</span>
                        <form action="{{ route('pasien.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Logout</button>
                        </form>
                    </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-6 text-center">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} UPTD Puskesmas. All rights reserved.</p>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
