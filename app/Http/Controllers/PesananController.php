<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index()
    {
        // $pesanans = Pesanan::with('user', 'detailPesanans.produk')->get();
        // return response()->json($pesanans);
         $pesanans = Pesanan::with('user', 'detailPesanans.produk')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.pesanan', compact('pesanans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id_user',
        ]);

        // Buat pesanan baru
        $pesanan = Pesanan::create([
            'id_user'     => $validated['id_user'],
            'total_harga' => 0,
            'status'      => 'pending'
        ]);

        return response()->json($pesanan, 201);
    }

    public function show($id)
    {
        $pesanan = Pesanan::with('user', 'detailPesanans.produk')->findOrFail($id);
        return response()->json($pesanan);
    }

    public function updateTotal($id)
    {
        $pesanan = Pesanan::findOrFail($id);

        // Hitung total harga dari semua detail
        $total = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');

        $pesanan->total_harga = $total;
        $pesanan->save();

        return response()->json($pesanan);
    }

    public function updateStatus(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,diproses,selesai,dibatalkan',
        ]);

        $pesanan->status = $validated['status'];
        $pesanan->save();

        return response()->json($pesanan);
    }

    public function destroy($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->delete();

        return response()->json(['message' => 'Pesanan berhasil dihapus']);
    }
}
