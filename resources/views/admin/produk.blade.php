@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold text-pink-500 mb-6">Daftar Produk</h2>

    @if (session('success'))
        <div class="p-4 bg-green-100 text-green-800 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.formproduk') }}" class="mb-4 inline-block px-4 py-2 bg-pink-500 text-white rounded hover:bg-pink-600">Tambah Produk</a>

    <table class="w-full table-auto border-collapse border border-gray-300">
        <thead class="bg-pink-100">
            <tr>
                <th class="border px-4 py-2">#</th>
                <th class="border px-4 py-2">Nama</th>
                <th class="border px-4 py-2">Harga</th>
                <th class="border px-4 py-2">Warna</th>
                <th class="border px-4 py-2">Stok</th>
                <th class="border px-4 py-2">Gambar</th>
                <th class="border px-4 py-2">Aksi</th> {{-- Kolom aksi --}}
            </tr>
        </thead>
        <tbody>
            @forelse ($produks as $item)
                <tr>
                    <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                    <td class="border px-4 py-2">{{ $item['nama_produk'] }}</td>
                    <td class="border px-4 py-2">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                    <td class="border px-4 py-2">{{ $item['warna'] }}</td>
                    <td class="border px-4 py-2">{{ $item['ukuran_tersedia'] }}</td>
                    <td class="border px-4 py-2">
                        @if(isset($item['gambar_produk']))
                            <img src="{{ asset('storage/' . $item->gambar_produk) }}" class="w-20 h-20 object-cover">
                        @else
                            Tidak ada gambar
                        @endif
                    </td>
                    <td class="border px-4 py-2 text-center">
                            <a href="{{ route('admin.editproduk', ['id' => $item->id_produk]) }}" 
       class="text-blue-500 hover:underline mr-2">Edit</a>
                        
                     <form action="{{ route('admin.deleteproduk', ['id' => $item->id_produk]) }}" method="POST" 
          style="display: inline;" 
          onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Belum ada produk</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
