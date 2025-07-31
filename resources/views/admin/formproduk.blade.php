@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold text-pink-500 mb-6">
        {{ isset($produk) ? 'Edit Produk' : 'Tambah Produk Baru' }}
    </h2>

    <!-- Notifikasi Sukses -->
    @if (session('success'))
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full border border-green-300">
                <div class="flex justify-between items-start">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <h3 class="text-lg font-medium text-green-800">Sukses!</h3>
                    </div>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-green-600">{{ session('success') }}</p>
                </div>
                <div class="mt-4 flex justify-end">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        OK
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Notifikasi Error -->
    @if ($errors->any())
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full border border-red-300">
                <div class="flex justify-between items-start">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-lg font-medium text-red-800">Terjadi Kesalahan</h3>
                    </div>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2">
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-4 flex justify-end">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        OK
                    </button>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ isset($produk) ? route('admin.updateproduk', $produk->id_produk) : route('admin.formproduk.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($produk))
            @method('PUT')
        @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
            <input type="text" name="nama_produk" 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" 
                   value="{{ old('nama_produk', $produk->nama_produk ?? '') }}"
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="deskripsi" rows="4" 
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" 
                      required>{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Harga</label>
            <input type="number" name="harga" 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" 
                   value="{{ old('harga', $produk->harga ?? '') }}"
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Stok</label>
            <input type="number" name="stok" 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" 
                   value="{{ old('stok', $produk->stok ?? '') }}"
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Ukuran Tersedia</label>
            <input type="text" name="ukuran_tersedia" 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" 
                   placeholder="Contoh: S,M,L,XL" 
                   value="{{ old('ukuran_tersedia', $produk->ukuran_tersedia ?? '') }}"
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Warna</label>
            <input type="text" name="warna" 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" 
                   value="{{ old('warna', $produk->warna ?? '') }}"
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Kategori</label>
            <select name="kategori_id" id="kategori_id" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500" 
                    required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" 
                            @if(old('kategori_id', isset($produk) ? $produk->kategori_id : '') == $kategori->id) selected @endif>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700">Gambar Produk</label>
            <input type="file" name="gambar_produk" 
                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100" 
                   accept="image/*"
                   @if(!isset($produk)) required @endif>
            
            @if(isset($produk) && $produk->gambar_produk)
                <div class="mt-2">
                    <img src="{{ asset('storage/gambar_produk/' . $produk->gambar_produk) }}" alt="Gambar Produk" class="h-32">
                    <p class="text-sm text-gray-500 mt-1">Gambar saat ini</p>
                </div>
            @endif
        </div>

        <div class="flex justify-between">
            <a href="{{ route('admin.produk') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                Kembali
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-pink-500 text-white rounded-md hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                {{ isset($produk) ? 'Update Produk' : 'Tambah Produk' }}
            </button>
        </div>
    </form>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi sebelum submit
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const kategoriSelect = document.getElementById('kategori_id');
        if(!kategoriSelect.value) {
            e.preventDefault();
            kategoriSelect.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            kategoriSelect.focus();
            
            // Buat pesan error jika belum ada
            if(!document.getElementById('kategori-error')) {
                const errorElement = document.createElement('p');
                errorElement.id = 'kategori-error';
                errorElement.className = 'mt-2 text-sm text-red-600';
                errorElement.textContent = 'Silakan pilih kategori terlebih dahulu';
                kategoriSelect.parentNode.appendChild(errorElement);
            }
        }
    });

    // Hapus error saat memilih kategori
    const kategoriSelect = document.getElementById('kategori_id');
    kategoriSelect.addEventListener('change', function() {
        if(this.value) {
            this.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            const errorElement = document.getElementById('kategori-error');
            if(errorElement) {
                errorElement.remove();
            }
        }
    });
});
</script>
@endsection
@endsection