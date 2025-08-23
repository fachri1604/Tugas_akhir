@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-12">

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
        @foreach($produks as $produk)
        <div class="border rounded-lg shadow p-4 flex flex-col">

            {{-- Gambar produk --}}
            <div class="w-full h-56 flex items-center justify-center bg-white rounded mb-3">
                <img src="{{ asset('storage/' . $produk->gambar_produk) }}" 
                     alt="{{ $produk->nama_produk }}"                     
                    class="w-full h-56 object-contain rounded mb-3 bg-white">
            </div>

            {{-- Nama produk dengan ellipsis --}}
            <h3 class="font-semibold text-lg truncate w-full block" 
                title="{{ $produk->nama_produk }}">
                {{ $produk->nama_produk }}
            </h3>

            <p class="text-gray-600 mb-3">
                Rp {{ number_format($produk->harga, 0, ',', '.') }}
            </p>

            <a href="{{ route('produk.beli', $produk->id_produk) }}" 
               class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 w-full text-center block">
               Beli
            </a>
        </div>
        @endforeach
    </div>

    <div class="mt-6 flex justify-center">
        {{ $produks->links() }}
    </div>
</div>
@endsection
