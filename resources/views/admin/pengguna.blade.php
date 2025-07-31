@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-pink-600 mb-6">Kelola Pengguna</h1>

    {{-- Tabel Pengguna --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow mb-8">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-pink-100 text-pink-800">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium uppercase">#</th>
                    <th class="px-6 py-3 text-left text-sm font-medium uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-sm font-medium uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-medium uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-sm font-medium uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $user->name }}</td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">{{ ucfirst($user->role ?? 'pengguna') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.pengguna', ['edit_id' => $user->id]) }}" class="text-blue-500 hover:underline mr-2">Edit</a>
                            <form action="{{ route('admin.pengguna.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Form Edit Jika Ada --}}
    @if ($editUser)
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Edit Pengguna: {{ $editUser->name }}</h2>
        <form method="POST" action="{{ route('admin.pengguna.update', $editUser->id) }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label class="block text-sm font-medium">Nama</label>
                <input type="text" name="name" value="{{ old('name', $editUser->name) }}" class="w-full border-gray-300 rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $editUser->email) }}" class="w-full border-gray-300 rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Role</label>
                <input type="text" name="role" value="{{ old('role', $editUser->role) }}" class="w-full border-gray-300 rounded">
            </div>

            <button type="submit" class="px-4 py-2 bg-pink-500 text-white rounded hover:bg-pink-600">Update</button>
        </form>
    </div>
    @endif
</div>
@endsection
