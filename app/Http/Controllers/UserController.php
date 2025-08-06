<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua field pengguna kecuali Admin
        $users = User::where('role', '!=', 'Admin')
                   ->select('id_user', 'name', 'email', 'alamat', 'phone', 'role', 'created_at')
                   ->get();

        $editUser = null;
        if ($request->has('edit_id')) {
            $editUser = User::find($request->edit_id);
        }

        return view('admin.pengguna', compact('users', 'editUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'alamat'   => 'nullable|string',
            'phone'    => 'nullable|string',
            'role'     => 'required|string'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'alamat'   => $request->alamat,
            'phone'    => $request->phone,
            'role'     => $request->role
        ]);

        return response()->json($user, 201);
    }

    public function show($id_user)
    {
        return User::where('id_user', $id_user)
                 ->select('id_user', 'name', 'email', 'alamat', 'phone', 'role', 'created_at')
                 ->firstOrFail();
    }

    public function update(Request $request, $id_user)
{
    try {
        $user = User::where('id_user', $id_user)->firstOrFail();

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id_user.',id_user',
            'alamat' => 'nullable',
            'phone' => 'nullable',
            'role' => 'required'
        ]);

        $user->update($request->all());

        return redirect()->route('admin.pengguna')
                       ->with('success', 'Data pengguna berhasil diperbarui');

    } catch (\Exception $e) {
        return redirect()->back()
                       ->with('error', 'Gagal memperbarui data pengguna: '.$e->getMessage());
    }
}

public function destroy($id_user)
{
    try {
        $user = User::where('id_user', $id_user)->firstOrFail();
        $user->delete();

        return redirect()->route('admin.pengguna')
                       ->with('success', 'Pengguna berhasil dihapus');

    } catch (\Exception $e) {
        return redirect()->back()
                       ->with('error', 'Gagal menghapus pengguna: '.$e->getMessage());
    }
}
}