@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Stok</h1>
        <a href="{{ route('admin.stok.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">
            + Tambah Stok
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-100 uppercase text-xs font-semibold text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-center">Ukuran</th>
                    <th class="px-4 py-3 text-center">Warna</th>
                    <th class="px-4 py-3 text-center">Jumlah</th>
                    <th class="px-4 py-3 text-center">Tipe</th>
                    <th class="px-4 py-3 text-left">Catatan</th>
                    <th class="px-4 py-3 text-center">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($stoks as $stok)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold">{{ $stok->produk->nama_produk ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $ukuranList = $stok->produk->ukuran_tersedia ?? '-';
                            @endphp
                            {{ $ukuranList }}
                        </td>
                        <td class="px-4 py-3 text-center">{{ $stok->warna->nama_warna ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $stok->jumlah }}</td>
                        <td class="px-4 py-3 text-center">
                            @if(strtolower($stok->tipe) === 'masuk')
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Masuk</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">Keluar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $stok->catatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $stok->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada data stok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $stoks->links() }}</div>
</div>
@endsection
