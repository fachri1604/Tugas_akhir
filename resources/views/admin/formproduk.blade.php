@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-xl" x-data="{
    showSuccessAlert: @json(session('success') ? true : false),
    showErrorAlert: @json($errors->any() ? true : false),
    init() {
        if (this.showSuccessAlert) {
            setTimeout(() => {
                this.showSuccessAlert = false;
            }, 5000);
        }
    }
}">
    <h2 class="text-2xl font-bold text-indigo-700 mb-6">
        {{ isset($produk) ? 'Edit Produk' : 'Tambah Produk Baru' }}
    </h2>

    <!-- Success Alert -->
    <div x-show="showSuccessAlert" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4" 
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 translate-y-4"
         class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="showSuccessAlert = false" class="text-green-700 hover:text-green-900">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Error Alert -->
    <div x-show="showErrorAlert" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4" 
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="font-medium">Terjadi kesalahan!</span>
            </div>
            <button @click="showErrorAlert = false" class="text-red-700 hover:text-red-900">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <ul class="mt-2 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

    <form action="{{ isset($produk) ? route('admin.updateproduk', $produk->id_produk) : route('admin.formproduk.store') }}" method="POST" enctype="multipart/form-data" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
        @csrf
        @if(isset($produk))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Nama Produk -->
            <div>
                <label for="nama_produk" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                <input type="text" id="nama_produk" name="nama_produk" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                       value="{{ old('nama_produk', $produk->nama_produk ?? '') }}"
                       required>
            </div>

            <!-- Harga -->
            <div>
                <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">Rp</span>
                    </div>
                    <input type="number" id="harga" name="harga" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                           value="{{ old('harga', $produk->harga ?? '') }}"
                           required>
                </div>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="mb-6">
            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                      required>{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Stok -->
            <div>
                <label for="stok" class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                <input type="number" id="stok" name="stok" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                       value="{{ old('stok', $produk->stok ?? '') }}"
                       required>
            </div>

            <!-- Ukuran Tersedia -->
            <div>
                <label for="ukuran_tersedia" class="block text-sm font-medium text-gray-700 mb-1">Ukuran Tersedia</label>
                <input type="text" id="ukuran_tersedia" name="ukuran_tersedia" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                       placeholder="Contoh: S,M,L,XL" 
                       value="{{ old('ukuran_tersedia', $produk->ukuran_tersedia ?? '') }}"
                       required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Warna -->
            <div>
                <label for="warna" class="block text-sm font-medium text-gray-700 mb-1">Warna</label>
                <input type="text" id="warna" name="warna" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                       value="{{ old('warna', $produk->warna ?? '') }}"
                       required>
            </div>

            <!-- Kategori -->
            <div>
                <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select id="kategori_id" name="kategori_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
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
        </div>

        <!-- Gambar Produk -->
        <div class="mb-6">
            <label for="gambar_produk" class="block text-sm font-medium text-gray-700 mb-1">Gambar Produk</label>
            <div class="mt-1 flex items-center">
                <label for="gambar_produk" class="cursor-pointer">
                    <span class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-image mr-2"></i>
                        Pilih Gambar
                    </span>
                    <input type="file" id="gambar_produk" name="gambar_produk" 
                           class="sr-only" 
                           accept="image/*"
                           @if(!isset($produk)) required @endif>
                </label>
                <span id="file-name" class="ml-2 text-sm text-gray-500">Tidak ada file dipilih</span>
            </div>
            
            @if(isset($produk) && $produk->gambar_produk)
                <div class="mt-4">
                    <p class="text-sm text-gray-500 mb-1">Gambar saat ini:</p>
                    <img src="{{ asset('storage/gambar_produk/' . $produk->gambar_produk) }}" alt="Gambar Produk" class="h-32 rounded-md shadow-sm border border-gray-200">
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between border-t border-gray-200 pt-6">
            <a href="{{ route('admin.produk') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :disabled="isSubmitting"
                    :class="{ 'opacity-75 cursor-not-allowed': isSubmitting }">
                <i class="fas fa-save mr-2"></i>
                <span x-show="!isSubmitting">{{ isset($produk) ? 'Update Produk' : 'Tambah Produk' }}</span>
                <span x-show="isSubmitting" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </form>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show selected file name
    document.getElementById('gambar_produk').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'Tidak ada file dipilih';
        document.getElementById('file-name').textContent = fileName;
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const kategoriSelect = document.getElementById('kategori_id');
        if(!kategoriSelect.value) {
            e.preventDefault();
            kategoriSelect.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            kategoriSelect.focus();
            
            // Create error message if not exists
            if(!document.getElementById('kategori-error')) {
                const errorElement = document.createElement('p');
                errorElement.id = 'kategori-error';
                errorElement.className = 'mt-1 text-sm text-red-600';
                errorElement.textContent = 'Silakan pilih kategori terlebih dahulu';
                kategoriSelect.parentNode.appendChild(errorElement);
            }
        }
    });

    // Remove error when category is selected
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