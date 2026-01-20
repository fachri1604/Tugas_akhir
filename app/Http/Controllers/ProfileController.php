<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Normalisasi: hapus semua non-digit (spasi, tanda, dll)
        if ($request->filled('phone')) {
            $request->merge([
                'phone' => preg_replace('/\D+/', '', (string) $request->input('phone'))
            ]);
        }

        $request->validate(
            [
                'name'   => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
                'email'  => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id_user.',id_user'],
                // nullable + digits:12 => jika diisi wajib 12 digit
                'phone'  => ['nullable', 'digits:12'],
                'alamat' => ['nullable', 'string', 'max:500'],
            ],
            [
                'name.regex'   => 'Nama lengkap hanya boleh berisi huruf dan spasi.',
                'phone.digits' => 'Nomor telepon harus tepat 12 digit angka.',
            ]
        );

        $user->update($request->only(['name', 'email', 'phone', 'alamat']));

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Password berhasil diubah');
    }
}
