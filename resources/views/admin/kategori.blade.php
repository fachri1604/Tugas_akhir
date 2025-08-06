@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    deleteCategoryId: null,
    deleteCategoryName: '',
    showDeleteModal: false,
    init() {
        if (this.showAlert) {
            setTimeout(() => {
                this.showAlert = false;
            }, 5000);
        }
    },
    openDeleteModal(id, name) {
        this.deleteCategoryId = id;
        this.deleteCategoryName = name;
        this.showDeleteModal = true;
    },
    closeDeleteModal() {
        this.showDeleteModal = false;
    }
}">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-indigo-700">Daftar Kategori</h1>
        <a href="{{ route('admin.formkategori') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-plus mr-2"></i> Tambah Kategori
        </a>
    </div>

    <!-- Alert Notification -->
    <div x-show="showAlert" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         :class="{
            'bg-green-100 border-l-4 border-green-500 text-green-700': alertType === 'success',
            'bg-red-100 border-l-4 border-red-500 text-red-700': alertType === 'error'
         }"
         class="mb-6 p-4 rounded">
        <div class="flex items-center justify-between">
            <div>
                <i :class="{
                    'fas fa-check-circle mr-2': alertType === 'success',
                    'fas fa-exclamation-circle mr-2': alertType === 'error'
                }"></i>
                <span x-text="alertMessage"></span>
            </div>
            <button @click="showAlert = false" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Nama Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kategoris as $kategori)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $kategori->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $kategori->nama_kategori }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.editkategori', $kategori->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <button @click="openDeleteModal('{{ $kategori->id }}', '{{ addslashes($kategori->nama_kategori) }}')" 
                                    class="text-red-600 hover:text-red-900 inline-flex items-center">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                            Belum ada kategori
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     aria-hidden="true" 
                     @click="closeDeleteModal()"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Konfirmasi Hapus Kategori
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus kategori <span class="font-semibold" x-text="deleteCategoryName"></span>? Data yang dihapus tidak dapat dikembalikan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form :action="'/admin/kategori/' + deleteCategoryId" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Ya, Hapus
                            </button>
                        </form>
                        <button @click="closeDeleteModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection