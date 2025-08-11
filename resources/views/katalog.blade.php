@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-12">

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($produks as $produk)
        <div class="border rounded-lg shadow p-4 flex flex-col">
            <img src="{{ asset('storage/' . $produk->gambar_produk) }}" 
                 alt="{{ $produk->nama_produk }}"
                 class="h-48 w-full object-cover rounded mb-3">

            <h3 class="font-semibold text-lg">{{ $produk->nama_produk }}</h3>
            <p class="text-gray-600 mb-2">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500 flex-grow">{{ Str::limit($produk->deskripsi, 80) }}</p>

            <a href="{{ route('produk.beli', $produk->id_produk) }}" 
   class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 w-full text-center block">
   Beli
</a>

        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $produks->links() }}
    </div>
</div>
@endsection
