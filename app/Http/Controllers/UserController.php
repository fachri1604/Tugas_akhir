<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'alamat'   => 'nullable|string',
            'phone'    => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'alamat'   => $request->alamat,
            'phone'    => $request->phone,
        ]);

        return response()->json($user, 201);
    }

    public function show($id_user)
    {
        return User::where('id_user', $id_user)->firstOrFail();
    }

    public function update(Request $request, $id_user)
    {
        $user = User::where('id_user', $id_user)->firstOrFail();

        $user->update([
            'name'   => $request->name,
            'email'  => $request->email,
            'alamat' => $request->alamat,
            'phone'  => $request->phone,
        ]);

        return response()->json($user);
    }

    public function destroy($id_user)
    {
        User::where('id_user', $id_user)->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
