<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;

class StokController extends Controller
{
    /**
     * Menampilkan semua data stok
     */
    public function index()
    {
        $stoks = Stok::with(['produk', 'user'])->get();
        return response()->json($stoks);
    }

    /**
     * Menyimpan data stok baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required|exists:produks,id_produk',
            'id_user' => 'required|exists:users,id_user',
            'tipe' => 'required|in:tambah,kurang',
            'jumlah' => 'required|integer',
            'catatan' => 'nullable|string',
            'total' => 'required|integer',
        ]);

        $stok = Stok::create($validated);

        return response()->json(['message' => 'Stok berhasil ditambahkan', 'data' => $stok]);
    }

    /**
     * Menampilkan detail stok
     */
    public function show($id)
    {
        $stok = Stok::with(['produk', 'user'])->findOrFail($id);
        return response()->json($stok);
    }

    /**
     * Mengupdate stok
     */
    public function update(Request $request, $id)
    {
        $stok = Stok::findOrFail($id);

        $validated = $request->validate([
            'id_produk' => 'required|exists:produks,id_produk',
            'id_user' => 'required|exists:users,id_user',
            'tipe' => 'required|in:tambah,kurang',
            'jumlah' => 'required|integer',
            'catatan' => 'nullable|string',
            'total' => 'required|integer',
        ]);

        $stok->update($validated);

        return response()->json(['message' => 'Stok berhasil diupdate', 'data' => $stok]);
    }

    /**
     * Menghapus stok
     */
    public function destroy($id)
    {
        $stok = Stok::findOrFail($id);
        $stok->delete();

        return response()->json(['message' => 'Stok berhasil dihapus']);
    }
}
