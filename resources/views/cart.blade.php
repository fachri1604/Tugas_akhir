@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-12">
    <h2 class="text-2xl font-semibold mb-6">Keranjang Belanja</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded mb-4 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    @if($pesanan && $pesanan->detailPesanans->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border">
                <thead>
                    <tr class="bg-gray-100 text-sm">
                        <th class="p-2 text-left">Produk</th>
                        <th class="p-2 text-center">Ukuran</th>
                        <th class="p-2 text-center">Warna</th>
                        <th class="p-2 text-center">Jumlah</th>
                        <th class="p-2 text-right">Subtotal</th>
                        <th class="p-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesanan->detailPesanans as $detail)
                    <tr class="border-t">
                        <td class="p-2 align-top">
                            <div class="font-medium">{{ $detail->produk->nama_produk }}</div>
                        </td>

                        {{-- TAMPILKAN UKURAN --}}
                        <td class="p-2 text-center align-top">
                            <span class="inline-block px-2 py-1 text-xs rounded border">
                                {{ $detail->ukuran ?? '-' }}
                            </span>
                        </td>

                        {{-- TAMPILKAN WARNA --}}
                        <td class="p-2 text-center align-top">
                            <span class="inline-block px-2 py-1 text-xs rounded border">
                                {{ $detail->warna ?? '-' }}
                            </span>
                        </td>

                        {{-- UPDATE JUMLAH SAJA (seperti sebelumnya) --}}
                        <td class="p-2 text-center align-top">
                            <form action="{{ route('cart.update', $detail->id_detail) }}" method="POST" class="inline-flex items-center space-x-2">
                                @csrf
                                @method('PUT')
                                <input type="number" name="jumlah" value="{{ $detail->jumlah }}" min="1"
                                       class="w-16 border rounded text-center">
                                <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">Update</button>
                            </form>
                        </td>

                        <td class="p-2 text-right align-top whitespace-nowrap">
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </td>

                        <td class="p-2 text-center align-top">
                            <form action="{{ route('cart.remove', $detail->id_detail) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-6">
            <h3 class="text-lg font-semibold">
                Total: Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
            </h3>

            {{-- Tombol menuju form checkout --}}
            <a href="{{ route('checkout.form', $pesanan->id_pesanan) }}"
               class="inline-block bg-pink-600 text-white px-4 py-2 rounded hover:bg-pink-700 text-sm">
                Lanjut ke Checkout
            </a>
        </div>
    @else
        <p>Keranjang belanja Anda kosong.</p>
    @endif
</div>
@endsection
