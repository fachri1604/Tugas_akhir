<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna (kecuali Admin) dengan pagination (10 per halaman)
     * + pencarian opsional (?q=...),
     * + dukungan edit modal via ?edit_id=ID.
     *
     * View: resources/views/admin/pengguna.blade.php
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->where('role', '!=', 'Admin')
            ->select(
                'id_user', 'name', 'email', 'alamat',
                'provinsi_id', 'kota_id', 'kode_pos', 'phone', 'role', 'created_at'
            )
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString(); // pertahankan parameter q & edit_id di URL

        // untuk modal edit via ?edit_id
        $editUser = null;
        if ($request->filled('edit_id')) {
            $editUser = User::where('id_user', $request->edit_id)->first();
        }

        return view('admin.pengguna', compact('users', 'editUser', 'q'));
    }

    /**
     * Simpan user baru.
     * Return JSON (cocok untuk AJAX).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:191', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:6'], // kalau pakai konfirmasi: tambahkan 'confirmed'
            'alamat'      => ['nullable', 'string', 'max:255'],
            'provinsi_id' => ['nullable', 'integer'],
            'kota_id'     => ['nullable', 'integer'],
            'kode_pos'    => ['nullable', 'string', 'max:10'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'role'        => ['required', Rule::in(['Admin', 'User', 'Staff'])],
        ]);

        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'alamat'      => $validated['alamat']      ?? null,
            'provinsi_id' => $validated['provinsi_id'] ?? null,
            'kota_id'     => $validated['kota_id']     ?? null,
            'kode_pos'    => $validated['kode_pos']    ?? null,
            'phone'       => $validated['phone']       ?? null,
            'role'        => $validated['role'],
        ]);

        return response()->json($user, 201);
    }

    /**
     * Ambil detail user by id_user.
     */
    public function show($id_user)
    {
        return User::where('id_user', $id_user)
            ->select(
                'id_user', 'name', 'email', 'alamat',
                'provinsi_id', 'kota_id', 'kode_pos', 'phone', 'role', 'created_at'
            )
            ->firstOrFail();
    }

    /**
     * Update user by id_user.
     * - Email unik, abaikan email milik user saat ini.
     * - Password opsional; jika kosong, tidak diubah.
     */
    public function update(Request $request, $id_user)
    {
        try {
            $user = User::where('id_user', $id_user)->firstOrFail();

            $validated = $request->validate([
                'name'        => ['required', 'string', 'max:100'],
                // unique:users,email,<id_yang_diabaikan>,kolom_pk
                'email'       => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($id_user, 'id_user')],
                'password'    => ['nullable', 'string', 'min:6'],
                'alamat'      => ['nullable', 'string', 'max:255'],
                'provinsi_id' => ['nullable', 'integer'],
                'kota_id'     => ['nullable', 'integer'],
                'kode_pos'    => ['nullable', 'string', 'max:10'],
                'phone'       => ['nullable', 'string', 'max:20'],
                'role'        => ['required', Rule::in(['Admin', 'User', 'Staff'])],
            ]);

            $data = [
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'alamat'      => $validated['alamat']      ?? null,
                'provinsi_id' => $validated['provinsi_id'] ?? null,
                'kota_id'     => $validated['kota_id']     ?? null,
                'kode_pos'    => $validated['kode_pos']    ?? null,
                'phone'       => $validated['phone']       ?? null,
                'role'        => $validated['role'],
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            return redirect()
                ->route('admin.pengguna')
                ->with('success', 'Data pengguna berhasil diperbarui');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Hapus user by id_user.
     * Cegah menghapus diri sendiri (opsional).
     */
    public function destroy($id_user)
    {
        try {
            $user = User::where('id_user', $id_user)->firstOrFail();

            if (Auth::check() && Auth::user()->id_user === $user->id_user) {
                return redirect()->back()->with('error', 'Tidak bisa menghapus akun yang sedang dipakai.');
            }

            $user->delete();

            return redirect()
                ->route('admin.pengguna')
                ->with('success', 'Pengguna berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
        }
    }
}
