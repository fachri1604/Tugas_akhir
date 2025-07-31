{{-- Extend layout utama --}}
@extends('layouts.app')

{{-- Isi konten utama --}}
@section('content')

    <!-- Hero Section -->
   <section class="bg-gradient-to-b from-pink-100 to-white text-center py-32 relative">
    <h1 class="text-4xl font-playfair text-pink-500">
    Selamat Datang di WeFashion
</h1>
    <p class="text-gray-600 text-sm md:text-base mb-8">
        Temukan gaya terbaik untukmu hari ini
    </p>
    <button class="bg-pink-300 hover:bg-pink-400 text-white py-2 px-6 rounded-full text-sm shadow-lg transition duration-200">
        Belanja Sekarang
    </button>
</section>

    <!-- Rekomendasi Hari Ini -->
    <section class="py-16 text-center">
        <h2 class="text-xl font-semibold mb-8">Rekomendasi Hari Ini :</h2>
        <div class="flex justify-center gap-6">
            @for ($i = 0; $i < 6; $i++)
                <div class="w-32 h-48 bg-gray-300 rounded"></div>
            @endfor
        </div>
    </section>

@endsection
