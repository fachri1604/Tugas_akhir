@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8" x-data="{
    showSuccessAlert: @json(session('success') ? true : false),
    showErrorAlert: @json($errors->any() ? true : false),
    isSubmitting: false,
    isEdit: @json(isset($produk)),
    form: {
        nama_produk: '{{ old('nama_produk', $produk->nama_produk ?? '') }}',
        deskripsi: '{{ old('deskripsi', $produk->deskripsi ?? '') }}',
        harga: '{{ old('harga', $produk->harga ?? '') }}',
        gambar_produk: '',
        warna: '{{ old('warna', $produk->warna ?? '') }}',
        ukuran_tersedia: '{{ old('ukuran_tersedia', $produk->ukuran_tersedia ?? '') }}',
        alamat: '{{ old('alamat', $produk->alamat ?? '') }}',
        stok: '{{ old('stok', $produk->stok ?? 0) }}',
        kategori_id: '{{ old('kategori_id', $produk->kategori_id ?? '') }}'
    },
    init() {
        if (this.showSuccessAlert) {
            setTimeout(() => {
                this.showSuccessAlert = false;
            }, 5000);
        }
    }
}">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6" x-text="isEdit ? 'Edit Produk' : 'Tambah Produk Baru'"></h1>

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

            <!-- Form -->
            <form :action="isEdit ? '{{ route('admin.updateproduk', $produk->id_produk ?? 0) }}' : '{{ route('admin.produk.store') }}'" 
                  method="POST" 
                  enctype="multipart/form-data"
                  @submit="isSubmitting = true">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>

                <!-- Nama Produk -->
                <div class="mb-6">
                    <label for="nama_produk" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_produk" id="nama_produk" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.nama_produk"
                           required
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                    @error('nama_produk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="mb-6">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                        Deskripsi <span class="text-red-500">*</span>
                    </label>
                    <textarea name="deskripsi" id="deskripsi" rows="4"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.deskripsi"
                           required
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }"></textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga -->
                <div class="mb-6">
                    <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">
                        Harga <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="harga" id="harga" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.harga"
                           required
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                    @error('harga')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gambar Produk -->
                <div class="mb-6">
                    <label for="gambar_produk" class="block text-sm font-medium text-gray-700 mb-1">
                        Gambar Produk <span x-show="!isEdit" class="text-red-500">*</span>
                    </label>
                    <input type="file" name="gambar_produk" id="gambar_produk" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }"
                           x-bind:required="!isEdit">
                    @error('gambar_produk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if(isset($produk) && $produk->gambar_produk)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $produk->gambar_produk) }}" alt="Gambar Produk" class="w-32 h-32 object-cover rounded-md">
                            <p class="mt-1 text-sm text-gray-500">Gambar saat ini</p>
                        </div>
                    @endif
                </div>

                <!-- Warna -->
                <div class="mb-6">
                    <label for="warna" class="block text-sm font-medium text-gray-700 mb-1">
                        Warna
                    </label>
                    <input type="text" name="warna" id="warna" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.warna"
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                    @error('warna')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ukuran Tersedia -->
                <div class="mb-6">
                    <label for="ukuran_tersedia" class="block text-sm font-medium text-gray-700 mb-1">
                        Ukuran Tersedia
                    </label>
                    <input type="text" name="ukuran_tersedia" id="ukuran_tersedia" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.ukuran_tersedia"
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                    @error('ukuran_tersedia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="mb-6">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Pembelian
                    </label>
                    <input type="text" name="alamat" id="alamat" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.alamat"
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                    @error('alamat')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stok -->
                <div class="mb-6">
                    <label for="stok" class="block text-sm font-medium text-gray-700 mb-1">
                        Stok <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="stok" id="stok" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.stok"
                           required
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                    @error('stok')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div class="mb-6">
                    <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="kategori_id" id="kategori_id" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           x-model="form.kategori_id"
                           required
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" @selected(old('kategori_id', isset($produk) ? $produk->kategori_id : '') == $kategori->id)>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.produk') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                    
                    <button type="submit" 
                            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :disabled="isSubmitting"
                            :class="{ 'opacity-75 cursor-not-allowed': isSubmitting }">
                        <span x-show="!isSubmitting">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            <span x-text="isEdit ? 'Update Produk' : 'Tambah Produk'"></span>
                        </span>
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
    </div>
</div>
@endsection