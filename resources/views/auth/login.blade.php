@extends('layouts.guest')

@section('content')
<div class="bg-white border border-gray-200 rounded-xl shadow-sm">
    <div class="p-4 sm:p-7">
        <div class="text-center">
            <h1 class="block text-2xl font-bold text-gray-800">Login Petugas</h1>
           
        </div>

        <div class="mt-5">
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="grid gap-y-4">
                    
                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-sm text-red-600 rounded-lg p-4" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Form Group -->
                    <div>
                        <label for="username" class="block text-sm mb-2 font-medium text-gray-700">NIK / NIP / Email</label>
                        <div class="relative">
                            <input type="text" id="username" name="username" value="{{ old('username') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none border" required aria-describedby="username-error" placeholder="Masukkan NIK atau NIP Anda" autocomplete="username">
                        </div>
                    </div>
                    <!-- End Form Group -->

                    <!-- Form Group -->
                    <div>
                        <div class="flex justify-between items-center">
                            <label for="password" class="block text-sm mb-2">Password</label>
                        </div>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" id="password" name="password" class="py-3 ps-4 pe-10 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" required autocomplete="current-password">
                            <button type="button" @click="show = !show" style="right: 0;" class="absolute inset-y-0 right-0 flex items-center z-20 px-3 cursor-pointer text-gray-400 rounded-e-md focus:outline-none focus:text-blue-600 hover:text-gray-600">
                              <svg class="shrink-0 size-4" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <!-- Eye close icon (show = false) -->
                                <path x-show="!show" d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                <path x-show="!show" d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                <path x-show="!show" d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                <line x-show="!show" x1="2" x2="22" y1="2" y2="22"/>
                                <!-- Eye open icon (show = true) -->
                                <path x-show="show" style="display: none;" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle x-show="show" style="display: none;" cx="12" cy="12" r="3"/>
                              </svg>
                            </button>
                        </div>
                    </div>
                    <!-- End Form Group -->

                    <!-- Checkbox Remember Me -->
                    <div class="flex items-center">
                        <div class="flex">
                            <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }} class="shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                        </div>
                        <div class="ms-3">
                            <label for="remember" class="text-sm text-gray-600 select-none cursor-pointer">Ingat saya di perangkat ini</label>
                        </div>
                    </div>
                    <!-- End Checkbox -->

                    <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
