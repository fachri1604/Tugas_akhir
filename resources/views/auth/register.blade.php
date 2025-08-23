@extends('layouts.app')

@section('content')

{{-- MODAL NOTIFIKASI (berhasil/gagal) --}}
@if (session('success') || $errors->any())
<div x-data="{ open: true }" x-show="open"
     class="fixed inset-0 z-50 flex items-center justify-center">
  <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

  <div class="relative bg-white w-full max-w-md mx-4 rounded-lg shadow-lg p-6 text-center">
      @if (session('success'))
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Berhasil</h3>
        <p class="mt-1 text-gray-600">{{ session('success') }}</p>
        <button @click="open=false" class="mt-4 px-4 py-2 rounded bg-pink-500 text-white hover:bg-pink-600">Tutup</button>
      @else
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Gagal Mendaftar</h3>
        <ul class="mt-2 text-left text-sm text-red-600 list-disc list-inside space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button @click="open=false" class="mt-4 px-4 py-2 rounded bg-gray-700 text-white hover:bg-gray-800">Tutup</button>
      @endif
  </div>
</div>
@endif

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
                               value="{{ old('name') }}"
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <div class="mt-1">
                        <textarea id="alamat" name="alamat" rows="3" required
                                  class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1 resize-none">{{ old('alamat') }}</textarea>
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                    <div class="mt-1">
                        <input id="phone" name="phone" type="tel" required
                               value="{{ old('phone') }}"
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required
                               value="{{ old('email') }}"
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required
                               minlength="8"  {{-- client-side min 8 --}}
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1"
                               placeholder="Minimal 8 karakter">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <div class="mt-1">
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               minlength="8"
                               class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1">
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Daftar
                    </button>
                </div>
            </form>

            {{-- Media Sosial --}}            
        </div>
    </div>
</div>

{{-- AlpineJS untuk modal --}}
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
