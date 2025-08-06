@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">Data Stok</h2>
    <a href="{{ route('admin.stok.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Tambah Stok</a>
    <table class="w-full table-auto border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Produk</th>
                <th class="border px-4 py-2">Jumlah</th>
                <th class="border px-4 py-2">Ukuran</th>
                <th class="border px-4 py-2">Warna</th>
                <th class="border px-4 py-2">Alamat</th>
                <th class="border px-4 py-2">Catatan</th>
                <th class="border px-4 py-2">Tipe</th>
                <th class="border px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stoks as $stok)
            <tr>
                <td class="border px-4 py-2">{{ $stok->produk->nama_produk ?? 'Tidak Ada' }}</td>
                <td class="border px-4 py-2">{{ $stok->jumlah }}</td>
                <td class="border px-4 py-2">{{ $stok->ukuran }}</td>
                <td class="border px-4 py-2">{{ $stok->warna }}</td>
                <td class="border px-4 py-2">{{ $stok->alamat }}</td>
                <td class="border px-4 py-2">{{ $stok->catatan }}</td>
                <td class="border px-4 py-2">{{ $stok->tipe }}</td>
                <td class="border px-4 py-2">
                    <a href="{{ route('admin.stok.edit', $stok->id_log) }}" class="text-blue-600 hover:underline">Edit</a> |
                    <form action="{{ route('admin.stok.destroy', $stok->id_log) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Yakin hapus stok ini?')" class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
