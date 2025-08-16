@extends('layouts.admin')

@section('content')
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
        Selamat Datang di Dashboard Admin
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Total Produk</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ number_format($totalProduk ?? 0) }}
            </p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Pengguna</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ number_format($totalPengguna ?? 0) }}
            </p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Pesanan</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ number_format($totalPesanan ?? 0) }}
            </p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Dikirim</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ number_format($totalDikirim ?? 0) }}
            </p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Belum Dikirim</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ number_format($totalBelumDikirim ?? 0) }}
            </p>
        </div>
    </div>
@endsection
