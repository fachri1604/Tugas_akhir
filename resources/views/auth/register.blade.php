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
                        pattern="^[A-Za-z\s]+$"
                        title="Nama hanya boleh berisi huruf dan spasi."
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
       inputmode="numeric"           
       pattern="^\d{12}$"            
       minlength="15" maxlength="15" 
       oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,12)" 
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

              {{-- Password --}}
<div x-data="{ show: false }">
  <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
  <div class="mt-1 relative">
    <input
      :type="show ? 'text' : 'password'"
      id="password" name="password" required
      autocomplete="new-password"
      minlength="8"
      pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
      title="Min. 8 karakter, ada huruf besar, huruf kecil, angka, dan simbol."
      class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1 pr-10">
    <button type="button" @click="show = !show"
            class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
            aria-label="Tampil/Sembunyikan password">
      {{-- Eye / Eye-off --}}
      <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      </svg>
      <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.236-3.742M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.307 2.507M15 12a3 3 0 00-3-3m0 0a3 3 0 013 3m-3-3L3 21"/>
      </svg>
    </button>
  </div>
  <p class="mt-1 text-xs text-gray-500">
    Syarat: min 8 karakter, ada huruf besar & kecil, angka, dan simbol. Tanpa spasi.
  </p>
  @error('password')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
  @enderror
</div>

{{-- Konfirmasi Password --}}
<div x-data="{ show: false }">
  <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
  <div class="mt-1 relative">
    <input
      :type="show ? 'text' : 'password'"
      id="password_confirmation" name="password_confirmation" required
      autocomplete="new-password"
      minlength="8"
      class="w-full border-b-2 border-gray-300 focus:border-pink-500 focus:outline-none py-2 px-1 pr-10">
    <button type="button" @click="show = !show"
            class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
            aria-label="Tampil/Sembunyikan konfirmasi password">
      <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      </svg>
      <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.236-3.742M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.307 2.507M15 12a3 3 0 00-3-3m0 0a3 3 0 013 3m-3-3L3 21"/>
      </svg>
    </button>
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
