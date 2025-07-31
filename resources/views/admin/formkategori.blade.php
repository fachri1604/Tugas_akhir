@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8" x-data="{
    showSuccessAlert: @json(session('success') ? true : false),
    showErrorAlert: @json($errors->any() ? true : false),
    isSubmitting: false,
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
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                {{ isset($kategori) ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
            </h1>

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
            <form action="{{ isset($kategori) ? route('admin.updatekategori', $kategori->id) : route('admin.storekategori') }}" 
                  method="POST"
                  @submit="isSubmitting = true">
                @csrf
                @if(isset($kategori))
                    @method('PUT')
                @endif

                <div class="mb-6">
                    <label for="nama_kategori" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_kategori" id="nama_kategori" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('nama_kategori', $kategori->nama_kategori ?? '') }}"
                           required autofocus
                           :class="{ 'border-red-500': $el.nextElementSibling && $el.nextElementSibling.classList.contains('text-red-600') }">
                    @error('nama_kategori')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.kategori') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                    
                    <button type="submit" 
                            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :disabled="isSubmitting"
                            :class="{ 'opacity-75 cursor-not-allowed': isSubmitting }">
                        <span x-show="!isSubmitting">
                            <i class="fas fa-save mr-2"></i>
                            {{ isset($kategori) ? 'Update Kategori' : 'Tambah Kategori' }}
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