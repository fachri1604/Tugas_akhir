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
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1"
                               placeholder="email@contoh.com">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required 
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1"
                               placeholder="Masukkan password">
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-pink-500 focus:ring-pink-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Ingat saya
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-pink-500 hover:text-pink-600">
                            Lupa password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Masuk
                    </button>
                </div>
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
@endsection