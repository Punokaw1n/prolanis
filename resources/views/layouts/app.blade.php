<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Prolanis') }} - Admin</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="h-screen overflow-hidden">
        
        <!-- Mobile Overlay Backdrop -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden" style="display: none;"></div>

        <!-- ========== SIDEBAR ========== -->
        <aside id="sidebar" :class="sidebarOpen ? 'flex' : 'hidden'" class="lg:flex flex-col fixed inset-y-0 start-0 z-50 w-56 bg-white border-e border-gray-200 h-full">
            <!-- Logo -->
            <div class="flex items-center gap-x-3 px-6 h-16 border-b border-gray-200">
                <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-lg">
                    <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <span class="text-lg font-bold text-gray-800">Prolanis</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 hs-accordion-group" data-hs-accordion-always-open>
                <ul class="space-y-1">
                    <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Menu Utama</p>

                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            Dashboard
                        </a>
                    </li>



                    <p class="px-3 mt-6 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Data</p>

                    <li>
                        <a href="{{ route('admin.pasien.index') }}" class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.pasien.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Pasien
                        </a>
                    </li>

                    @php
                        $isProlanisActive = request()->routeIs('admin.evaluasi.*') || request()->routeIs('admin.import.poliklinik') || request()->routeIs('admin.import.apotek') || request()->routeIs('admin.jadwal.*');
                    @endphp
                    
                    <li x-data="{ open: {{ $isProlanisActive ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" class="w-full text-start flex items-center gap-x-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors" :class="open ? 'text-blue-700 bg-blue-50' : 'text-gray-700 hover:bg-gray-100'">
                            <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                            Prolanis

                            <!-- Arrow Up (Open) -->
                            <svg x-show="open" class="ms-auto size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="m18 15-6-6-6 6"/></svg>
                            <!-- Arrow Down (Closed) -->
                            <svg x-show="!open" class="ms-auto size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="w-full overflow-hidden" style="display: {{ $isProlanisActive ? 'block' : 'none' }};">
                            <ul class="pt-2 ps-8 space-y-1">
                                <li>
                                    <a href="{{ route('admin.jadwal.index') }}" class="flex items-center gap-x-3 py-2 px-2.5 text-sm rounded-lg {{ request()->routeIs('admin.jadwal.index') || request()->routeIs('admin.jadwal.create') || request()->routeIs('admin.jadwal.edit') ? 'text-blue-600 font-medium' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        Jadwal Layanan
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.jadwal.peserta') }}" class="flex items-center gap-x-3 py-2 px-2.5 text-sm rounded-lg {{ request()->routeIs('admin.jadwal.peserta') ? 'text-blue-600 font-medium' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        Peserta Terjadwal
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.evaluasi.index') }}" class="flex items-center gap-x-3 py-2 px-2.5 text-sm rounded-lg {{ request()->routeIs('admin.evaluasi.*') ? 'text-blue-600 font-medium' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                                        Evaluasi Kehadiran
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.import.poliklinik') }}" class="flex items-center gap-x-3 py-2 px-2.5 text-sm rounded-lg {{ request()->routeIs('admin.import.poliklinik') ? 'text-blue-600 font-medium' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                        Import Poliklinik
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.import.apotek') }}" class="flex items-center gap-x-3 py-2 px-2.5 text-sm rounded-lg {{ request()->routeIs('admin.import.apotek') ? 'text-blue-600 font-medium' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-4.24 4.24a3 3 0 0 0 0 4.24l.39.39"/></svg>
                                        Import Apotek
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- User Info at Bottom -->
            <div class="border-t border-gray-200 px-4 py-4">
                <div class="flex items-center gap-x-3">
                    <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 bg-blue-600 text-white font-bold rounded-full text-sm">
                        {{ strtoupper(substr(auth()->user()->nama ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->nama ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500 truncate capitalize">{{ auth()->user()->peran ?? 'admin' }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-gray-100" title="keluar">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        <!-- ========== END SIDEBAR ========== -->

        <!-- ========== MAIN AREA ========== -->
        <div class="h-full flex flex-col lg:ms-56">
            
            <!-- Top Bar (mobile brand + hamburger, desktop: just a subtle bar) -->
            <header class="sticky top-0 z-40 flex items-center h-16 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8">
                <!-- Mobile toggle -->
                <button type="button" class="lg:hidden p-2 -ms-2 rounded-lg text-gray-700 hover:bg-gray-100" @click="sidebarOpen = true">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                </button>
                <span class="lg:hidden ms-3 text-lg font-bold text-gray-800">Prolanis</span>

                <div class="flex-1"></div>

                <!-- Right side: compact user info on desktop -->
                <div class="hidden lg:flex items-center gap-x-3 text-sm text-gray-600">
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                <div class="bg-teal-50 border-t-2 border-teal-500 rounded-lg p-4 mb-6" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <span class="inline-flex justify-center items-center size-8 rounded-full border-4 border-teal-100 bg-teal-200 text-teal-800">
                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            </span>
                        </div>
                        <div class="ms-3">
                            <h3 class="text-gray-800 font-semibold">Sukses!</h3>
                            <p class="text-sm text-gray-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
        <!-- ========== END MAIN AREA ========== -->

    </div>

    @stack('scripts')
</body>
</html>
