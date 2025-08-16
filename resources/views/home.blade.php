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
    <button onclick="window.location.href='{{ route('katalog') }}'"
        class="bg-pink-300 hover:bg-pink-400 text-white py-2 px-6 rounded-full text-sm shadow-lg transition duration-200">
    Belanja Sekarang
</button>

</section>

    
   <!-- Rekomendasi Hari Ini -->
<section class="py-16 text-center">
    <h2 class="text-xl font-semibold mb-8">Rekomendasi Hari Ini :</h2>

    @if(isset($products) && $products->count())
        <div class="flex justify-center flex-wrap gap-6">
            @foreach($products as $p)
                <a href="{{ route('produk.detail', $p->id_produk) }}" class="group block">
                    <div class="w-32 h-48 bg-gray-100 rounded overflow-hidden shadow-sm">
                        <img
                            src="{{ asset('storage/'.$p->gambar_produk) }}"
                            alt="{{ $p->nama_produk }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy">
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-gray-500">Belum ada produk untuk ditampilkan.</div>
    @endif
</section>




@endsection
