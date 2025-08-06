@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    showDeleteModal: false,
    deleteProductId: null,
    deleteProductName: '',
    openDeleteModal(id, name) {
        this.deleteProductId = id;
        this.deleteProductName = name;
        this.showDeleteModal = true;
    },
    closeDeleteModal() {
        this.showDeleteModal = false;
        this.deleteProductId = null;
        this.deleteProductName = '';
    }
}">
    <!-- Notifikasi -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
            <div class="flex items-center justify-between">
                <div>
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="$el.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-indigo-700">Daftar Produk</h1>
        <a href="{{ route('admin.formproduk') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-plus mr-2"></i> Tambah Produk
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Nama Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Warna</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Ukuran Tersedia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Gambar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($produks as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['nama_produk'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item['warna'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item['ukuran_tersedia'])
                                <div class="flex flex-wrap gap-2">
                                    @foreach(explode(',', $item['ukuran_tersedia']) as $ukuran)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ trim($ukuran) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($item['gambar_produk']))
                                <img src="{{ asset('storage/' . $item->gambar_produk) }}" class="w-16 h-16 object-cover rounded-md shadow-sm">
                            @else
                                <span class="text-sm text-gray-500">Tidak ada gambar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.editproduk', ['id' => $item->id_produk]) }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <button @click="openDeleteModal('{{ $item->id_produk }}', '{{ addslashes($item['nama_produk']) }}')" 
                                    class="text-red-600 hover:text-red-900 inline-flex items-center">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            Belum ada produk
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
                                    Konfirmasi Hapus Produk
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus produk <span class="font-semibold" x-text="deleteProductName"></span>? Data yang dihapus tidak dapat dikembalikan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form :action="'/admin/produk/' + deleteProductId" method="POST">
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