<?php

namespace App\Http\Controllers;

use App\Models\UlasanProduk;
use Illuminate\Http\Request;

class UlasanProdukController extends Controller
{
    public function index()
    {
        $ulasans = UlasanProduk::with('produk', 'user')->get();
        return response()->json($ulasans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|exists:produks,id_produk',
            'id_user' => 'required|exists:users,id_user',
            'ulasan' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $ulasan = UlasanProduk::create($validated);

        return response()->json($ulasan, 201);
    }

    public function show($id)
    {
        $ulasan = UlasanProduk::with('produk', 'user')->findOrFail($id);
        return response()->json($ulasan);
    }

    public function update(Request $request, $id)
    {
        $ulasan = UlasanProduk::findOrFail($id);

        $validated = $request->validate([
            'ulasan' => 'sometimes|string|max:1000',
            'rating' => 'sometimes|integer|min:1|max:5',
        ]);

        $ulasan->update($validated);

        return response()->json($ulasan);
    }

    public function destroy($id)
    {
        $ulasan = UlasanProduk::findOrFail($id);
        $ulasan->delete();

        return response()->json(['message' => 'Ulasan berhasil dihapus']);
    }
}
