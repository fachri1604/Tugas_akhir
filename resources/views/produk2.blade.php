@extends('layouts.app')

@section('content')

<section class="bg-white py-12">
    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10">
        
        <!-- Gambar Produk -->
        <div>
            <img src="{{ asset('storage/' . $produk->gambar_produk) }}" 
                 alt="{{ $produk->nama_produk }}" 
                 class="w-full h-96 object-cover rounded">
        </div>

        <!-- Detail Produk -->
        <div class="space-y-4">
            <!-- Nama Produk -->
            <h2 class="text-2xl font-semibold">{{ $produk->nama_produk }}</h2>

            <!-- Harga -->
            <p class="text-xl font-bold text-pink-600">
                Rp {{ number_format($produk->harga, 0, ',', '.') }}
            </p>

            <!-- Deskripsi -->
            <p class="text-gray-600">
                {{ $produk->deskripsi }}
            </p>

            <!-- Ukuran -->
            <div>
                <p class="font-medium">Ukuran :</p>
                <div class="flex space-x-2 mt-1">
                    @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)
                        <button class="border px-3 py-1 text-sm hover:bg-gray-200 rounded">
                            {{ $size }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Warna dari Database -->
            <div>
                <p class="font-medium">Warna :</p>
                <div class="flex space-x-2 mt-1">
                    @php
                        $warnaArray = !empty($produk->warna) ? explode(',', $produk->warna) : [];
                    @endphp

                    @forelse($warnaArray as $warna)
                        <div class="px-3 py-1 border rounded bg-gray-100 text-sm">
                            {{ trim($warna) }}
                        </div>
                    @empty
                        <p class="text-gray-500">Tidak ada warna tersedia</p>
                    @endforelse
                </div>
            </div>

            <!-- Stok dan Jumlah -->
            <div class="flex items-center space-x-4">
                <p>Stok : <span class="font-medium">{{ $produk->stok }}</span></p>
                <div class="flex items-center border rounded overflow-hidden">
                    <button type="button" class="px-2 py-1 bg-gray-200 hover:bg-gray-300">-</button>
                    <input type="number" name="jumlah" value="1" min="1" max="{{ $produk->stok }}" 
                           class="w-12 text-center border-x outline-none">
                    <button type="button" class="px-2 py-1 bg-gray-200 hover:bg-gray-300">+</button>
                </div>
            </div>

            <!-- Tombol Beli -->
            <div class="flex items-center space-x-3 mt-4">
                <form action="{{ route('cart.add', $produk->id_produk) }}" method="POST">
                    @csrf
                    <input type="hidden" name="jumlah" value="1">
                    <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
                        Beli
                    </button>
                </form>
                
                <!-- Icon Keranjang -->
                <a href="{{ route('cart.index') }}" class="p-2 border rounded hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" 
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.2 6M17 13l1.2 6M6 19a2 2 0 100 4 2 2 0 000-4zm12 0a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
