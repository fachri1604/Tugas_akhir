@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    showDeleteModal: false,
    deleteUserId: null,
    deleteUserName: '',
    openDeleteModal(id, name) {
        this.deleteUserId = id;
        this.deleteUserName = name;
        this.showDeleteModal = true;
    },
    closeDeleteModal() {
        this.showDeleteModal = false;
        this.deleteUserId = null;
        this.deleteUserName = '';
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

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            <div class="flex items-center justify-between">
                <div>
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="$el.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-indigo-700">Daftar Pengguna</h1>       
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Telepon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Tanggal Daftar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->id_user }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->alamat ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $user->role === 'Admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.pengguna', ['edit_id' => $user->id_user]) }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <button @click="openDeleteModal('{{ $user->id_user }}', '{{ addslashes($user->name) }}')" 
                                    class="text-red-600 hover:text-red-900 inline-flex items-center">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            Belum ada pengguna
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
                                    Konfirmasi Hapus Pengguna
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus pengguna <span class="font-semibold" x-text="deleteUserName"></span>? Data yang dihapus tidak dapat dikembalikan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form :action="'/admin/pengguna/' + deleteUserId" method="POST">
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

    @if($editUser)
    <!-- Edit Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.pengguna.update', $editUser->id_user) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Pengguna</h3>
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                            <input type="text" name="name" id="name" value="{{ $editUser->name }}" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ $editUser->email }}" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="mb-4">
                            <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea name="alamat" id="alamat" rows="3" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ $editUser->alamat }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                            <input type="text" name="phone" id="phone" value="{{ $editUser->phone }}" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" id="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Admin" {{ $editUser->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="User" {{ $editUser->role == 'User' ? 'selected' : '' }}>User</option>
                                <option value="Staff" {{ $editUser->role == 'Staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <a href="{{ route('admin.pengguna') }}" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection