@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="{
        deleteCategoryId: null,
        deleteCategoryName: '',
        showDeleteModal: false,
        init() {
            if (this.showAlert) {
                setTimeout(() => { this.showAlert = false }, 5000);
            }
        },
        openDeleteModal(id, name) {
            this.deleteCategoryId = id;
            this.deleteCategoryName = name;
            this.showDeleteModal = true;
        },
        closeDeleteModal() { this.showDeleteModal = false; }
     }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <h1 class="text-3xl font-bold text-indigo-700">Daftar Kategori</h1>

        {{-- Search --}}
        {{-- <form method="GET" action="{{ route('admin.kategori') }}" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari kategori…"
                   class="border rounded-md px-3 py-2 w-56 focus:outline-none focus:ring focus:border-indigo-400">
            <button class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200">Cari</button> --}}
            @if(request('q'))
                <a href="{{ route('admin.kategori') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.formkategori') }}" 
           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-plus mr-2"></i> Tambah Kategori
        </a>
    </div>

    {{-- Tabel --}}
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                            Nama Kategori
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kategoris as $kategori)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Ganti ID dengan nomor urut --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $kategoris->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $kategori->nama_kategori }}
                            </td>
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
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                Belum ada kategori
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        @if($kategoris->hasPages())
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-sm text-gray-600">
                        Menampilkan
                        <span class="font-medium">{{ $kategoris->firstItem() }}</span>
                        –
                        <span class="font-medium">{{ $kategoris->lastItem() }}</span>
                        dari
                        <span class="font-medium">{{ $kategoris->total() }}</span>
                        data
                    </div>
                    <div>
                        {{ $kategoris->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <template x-teleport="body">
        <div x-show="showDeleteModal" 
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeDeleteModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Konfirmasi Hapus Kategori</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus kategori
                                        <span class="font-semibold" x-text="deleteCategoryName"></span>? Data yang dihapus tidak dapat dikembalikan.
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
