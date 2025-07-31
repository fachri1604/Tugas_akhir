@extends('layouts.admin') {{-- Sesuaikan dengan layout utama kamu --}}

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Daftar Kategori</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.formkategori') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
        + Tambah Kategori
    </a>

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-200">
                <th class="border p-2">ID</th>
                <th class="border p-2">Nama Kategori</th>
                <th class="border p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kategoris as $kategori)
            <tr>
                <td class="border p-2">{{ $kategori->id }}</td>
                <td class="border p-2">{{ $kategori->nama_kategori }}</td>
                <td class="border p-2 space-x-2">
                    <a href="{{ route('admin.editkategori', $kategori->id) }}" class="bg-yellow-400 text-white px-2 py-1 rounded">
                        Edit
                    </a>

                  <form action="{{ route('admin.deletekategori', $kategori->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah kamu yakin ingin menghapus kategori ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="bg-red-500 text-white px-2 py-1 rounded">Hapus</button>
                </form>

                </td>
            </tr>
            @endforeach

            @if($kategoris->isEmpty())
                <tr>
                    <td colspan="3" class="text-center p-4 text-gray-500">Belum ada kategori</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
