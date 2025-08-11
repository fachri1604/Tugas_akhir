<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Produk;
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
            'id_produk'  => 'required|exists:produks,id_produk',
            'jumlah'     => 'required|integer|min:1',
        ]);

        // Ambil data produk untuk harga satuan
        $produk = Produk::findOrFail($validated['id_produk']);

        // Hitung subtotal
        $hargaSatuan = $produk->harga;
        $subtotal    = $hargaSatuan * $validated['jumlah'];

        $detail = DetailPesanan::create([
            'id_pesanan'   => $validated['id_pesanan'],
            'id_produk'    => $validated['id_produk'],
            'jumlah'       => $validated['jumlah'],
            'harga_satuan' => $hargaSatuan,
            'subtotal'     => $subtotal
        ]);

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
        ]);

        // Kalau jumlah diubah, update subtotal
        if (isset($validated['jumlah'])) {
            $detail->jumlah = $validated['jumlah'];
            $detail->subtotal = $detail->harga_satuan * $validated['jumlah'];
        }

        $detail->save();

        return response()->json($detail);
    }

    public function destroy($id)
    {
        $detail = DetailPesanan::findOrFail($id);
        $detail->delete();

        return response()->json(['message' => 'Detail pesanan berhasil dihapus']);
    }
}
