@extends('layouts.app')

@section('content')

<section class="bg-white-100 py-12">
    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10">
        <!-- Gambar Produk -->
        <div class="bg-pink-200 w-full h-96 rounded"></div>

        <!-- Detail Produk -->
        <div class="space-y-4">
            <h2 class="text-2xl font-semibold">Bruqat Tunik</h2>

            <!-- Ukuran -->
            <div>
                <p class="font-medium">Ukuran :</p>
                <div class="flex space-x-2 mt-1">
                    @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)
                        <button class="border px-3 py-1 text-sm hover:bg-gray-200 rounded">{{ $size }}</button>
                    @endforeach
                </div>
            </div>

            <!-- Warna -->
            <div>
                <p class="font-medium">Warna :</p>
                <div class="flex space-x-2 mt-1">
                    <div class="w-5 h-5 bg-red-600 border rounded-full"></div>
                    <div class="w-5 h-5 bg-yellow-400 border rounded-full"></div>
                    <div class="w-5 h-5 bg-lime-500 border rounded-full"></div>
                </div>
            </div>

            <!-- Stok dan Jumlah -->
            <div class="flex items-center space-x-4">
                <p>Stok : <span class="font-medium">2</span></p>
                <div class="flex items-center border rounded overflow-hidden">
                    <button class="px-2 py-1 bg-gray-200 hover:bg-gray-300">-</button>
                    <input type="number" value="0" class="w-12 text-center border-x outline-none">
                    <button class="px-2 py-1 bg-gray-200 hover:bg-gray-300">+</button>
                </div>
            </div>

            <!-- Tombol Beli -->
            <div class="flex items-center space-x-3 mt-4">
                <button class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
                    Beli
                </button>
                <svg xmlns="" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.2 6M17 13l1.2 6M6 19a2 2 0 100 4 2 2 0 000-4zm12 0a2 2 0 100 4 2 2 0 000-4z" />
                </svg>
            </div>
        </div>
    </div>
</section>



@endsection
