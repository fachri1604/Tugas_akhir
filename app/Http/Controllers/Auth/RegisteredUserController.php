<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan form register
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Simpan data user baru
     */
    public function store(Request $request)
    {
        

        $request->validate([
            'name' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        // Simpan user ke database
        User::create([
            'name' => $request->name,
            'alamat' => $request->alamat,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'User', // Default role
        ]);

        // Redirect ke halaman login
        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan login.');
    }
}
