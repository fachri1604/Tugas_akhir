@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-12">
    <h2 class="text-2xl font-semibold mb-6">Keranjang Belanja</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($pesanan && $pesanan->detailPesanans->count() > 0)
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-left">Produk</th>
                    <th class="p-2 text-center">Jumlah</th>
                    <th class="p-2 text-right">Subtotal</th>
                    <th class="p-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pesanan->detailPesanans as $detail)
                <tr class="border-t">
                    <td class="p-2">{{ $detail->produk->nama_produk }}</td>
                    <td class="p-2 text-center">
                        <form action="{{ route('cart.update', $detail->id_detail) }}" method="POST" class="flex items-center justify-center space-x-2">
                            @csrf
                            @method('PUT')
                            <input type="number" name="jumlah" value="{{ $detail->jumlah }}" min="1" class="w-16 border rounded text-center">
                            <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Update</button>
                        </form>
                    </td>
                    <td class="p-2 text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    <td class="p-2 text-center">
                        <form action="{{ route('cart.remove', $detail->id_detail) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-between items-center mt-6">
            <h3 class="text-lg font-semibold">Total: Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</h3>
            
            {{-- Pastikan id_pesanan ada sebagai parameter --}}
            <a href="{{ route('payment.show', ['order_id' => $pesanan->id_pesanan]) }}" 
               class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
               Checkout
            </a>
        </div>
    @else
        <p>Keranjang belanja Anda kosong.</p>
    @endif
</div>
@endsection
