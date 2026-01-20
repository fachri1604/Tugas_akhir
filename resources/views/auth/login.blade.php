{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-b from-pink-100 to-white text-center flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h1 class="text-center text-3xl font-playfair font-bold text-pink-500">
            Selamat Datang Kembali
        </h1>
        <h2 class="mt-2 text-center text-xl font-medium text-gray-900">
            Masuk ke Akun WeFashion Anda
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
            <form class="mb-0 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required
                               value="{{ old('email') }}"
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1"
                               placeholder="email@contoh.com" autocomplete="email">
                    </div>
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password + Eye Toggle --}}
                <div x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1 relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password" name="password" required
                            class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1 pr-10"
                            placeholder="Masukkan password" autocomplete="current-password">

                        <button type="button"
                                @click="show = !show"
                                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                            {{-- eye (show=false) --}}
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{-- eye-off (show=true) --}}
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.236-3.742M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.307 2.507M15 12a3 3 0 00-3-3m0 0a3 3 0 013 3m-3-3L3 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Masuk
                    </button>
                </div>

                {{-- (Opsional) Remember me & Lupa password --}}
                {{-- <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300">
                        Ingat saya
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-pink-500 hover:text-pink-600">Lupa password?</a>
                </div> --}}
            </form>

            {{-- Link ke Register --}}
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-medium text-pink-500 hover:text-pink-600">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@once
    {{-- Muat AlpineJS jika belum ada di layout --}}
    <script src="//unpkg.com/alpinejs" defer></script>
@endonce
@endsection
