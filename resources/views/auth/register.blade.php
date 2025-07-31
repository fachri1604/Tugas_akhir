{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-b from-pink-100 to-white text-center flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h1 class="text-center text-3xl font-playfair font-bold text-pink-500">
            Form Pendaftaran
        </h1>
        <h2 class="mt-2 text-center text-xl font-medium text-gray-900">
            Daftar Akun WeFashion
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
            <form class="mb-0 space-y-6" action="{{ route('register') }}" method="POST">
                @csrf
                
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama lengkap</label>
                    <div class="mt-1">
                        <input id="name" name="name" type="text" required 
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
            <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
            <div class="mt-1">
                <textarea id="alamat" name="alamat" rows="3" required
            class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1 resize-none"></textarea>
                </div>
            </div>

                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Nomor Telpon</label>
                    <div class="mt-1">
                        <input id="phone" name="phone" type="tel" required 
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required 
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required 
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <div class="mt-1">
                        <input id="password_confirmation" name="password_confirmation" type="password" required 
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Daftar
                    </button>
                </div>
            </form>

            {{-- Media Sosial --}}
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Atau daftar dengan
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-3 gap-3">
                    <div>
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fab fa-facebook-f text-pink-500"></i>
                        </a>
                    </div>
                    <div>
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fab fa-google text-pink-500"></i>
                        </a>
                    </div>
                    <div>
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fab fa-twitter text-pink-500"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection