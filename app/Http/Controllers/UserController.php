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
           'name'        => $request->name,
    'email'       => $request->email,
    'password'    => Hash::make($request->password),
    'alamat'      => $request->alamat,
    'provinsi_id' => $request->provinsi_id,
    'kota_id'     => $request->kota_id,
    'kode_pos'    => $request->kode_pos,
    'phone'       => $request->phone,
    'role'        => $request->role
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
             'name'        => 'required',
    'email'       => 'required|email|unique:users,email' . (isset($id_user) ? ',' . $id_user . ',id_user' : ''),
    'password'    => isset($id_user) ? 'nullable|min:6' : 'required|min:6',
    'alamat'      => 'nullable|string|max:255',
    'provinsi_id' => 'nullable|integer',
    'kota_id'     => 'nullable|integer',
    'kode_pos'    => 'nullable|string|max:10',
    'phone'       => 'nullable|string|max:20',
    'role'        => 'required|string'
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