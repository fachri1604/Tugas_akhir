<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use Illuminate\Http\Request;

class DetailPesananController extends Controller
{
    public function index()
    {
        $details = DetailPesanan::with('pesanan', 'produk')->get();
        return response()->json($details);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pesanan' => 'required|exists:pesanans,id_pesanan',
            'id_produk' => 'required|exists:produks,id_produk',
            'jumlah' => 'required|integer|min:1',
            'subtotal' => 'required|integer|min:0',
        ]);

        $detail = DetailPesanan::create($validated);

        return response()->json($detail, 201);
    }

    public function show($id)
    {
        $detail = DetailPesanan::with('pesanan', 'produk')->findOrFail($id);
        return response()->json($detail);
    }

    public function update(Request $request, $id)
    {
        $detail = DetailPesanan::findOrFail($id);

        $validated = $request->validate([
            'jumlah' => 'sometimes|integer|min:1',
            'subtotal' => 'sometimes|integer|min:0',
        ]);

        $detail->update($validated);

        return response()->json($detail);
    }

    public function destroy($id)
    {
        $detail = DetailPesanan::findOrFail($id);
        $detail->delete();

        return response()->json(['message' => 'Detail pesanan berhasil dihapus']);
    }
}
