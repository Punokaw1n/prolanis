@extends('layouts.guest')

@section('content')
<div class="bg-white border border-gray-200 rounded-xl shadow-sm">
    <div class="p-4 sm:p-7">
        <div class="text-center">
            <h1 class="block text-2xl font-bold text-gray-800">Login Admin/Petugas</h1>
            <p class="mt-2 text-sm text-gray-600">
                Sistem Informasi Pasien Prolanis
            </p>
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
                        <label for="email" class="block text-sm mb-2">Email</label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none border" required aria-describedby="email-error">
                        </div>
                    </div>
                    <!-- End Form Group -->

                    <!-- Form Group -->
                    <div>
                        <div class="flex justify-between items-center">
                            <label for="password" class="block text-sm mb-2">Password</label>
                        </div>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none border" required aria-describedby="password-error">
                        </div>
                    </div>
                    <!-- End Form Group -->

                    <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
