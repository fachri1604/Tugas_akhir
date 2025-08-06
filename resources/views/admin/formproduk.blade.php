@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Tambah Produk</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label for="nama_produk" class="block font-semibold">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" class="w-full border border-gray-300 rounded p-2" value="{{ old('nama_produk') }}" required>
        </div>

        <div class="mb-4">
            <label for="deskripsi" class="block font-semibold">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="w-full border border-gray-300 rounded p-2" required>{{ old('deskripsi') }}</textarea>
        </div>

        <div class="mb-4">
            <label for="harga" class="block font-semibold">Harga</label>
            <input type="number" name="harga" id="harga" class="w-full border border-gray-300 rounded p-2" value="{{ old('harga') }}" required>
        </div>

        <div class="mb-4">
            <label for="gambar_produk" class="block font-semibold">Gambar Produk</label>
            <input type="file" name="gambar_produk" id="gambar_produk" class="w-full">
        </div>

        <div class="mb-4">
            <label for="warna" class="block font-semibold">Warna (pisahkan dengan koma)</label>
            <input type="text" name="warna" id="warna" class="w-full border border-gray-300 rounded p-2" value="{{ old('warna') }}" placeholder="Misal: Merah,Biru,Kuning" required>
        </div>

        <div class="mb-4">
            <label for="ukuran_tersedia" class="block font-semibold">Ukuran Tersedia (pisahkan dengan koma)</label>
            <input type="text" name="ukuran_tersedia" id="ukuran_tersedia" class="w-full border border-gray-300 rounded p-2" value="{{ old('ukuran_tersedia') }}" placeholder="Misal: S,M,L,XL" required>
        </div>

        <div class="mb-4">
            <label for="kategori_id" class="block font-semibold">Kategori</label>
            <select name="kategori_id" id="kategori_id" class="w-full border border-gray-300 rounded p-2" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Produk</button>
    </form>
</div>
@endsection
