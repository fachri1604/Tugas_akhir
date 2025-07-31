<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        return Pembayaran::with('pesanan')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pesanan' => 'required|exists:pesanans,id_pesanan',
            'metode_pembayaran' => 'required|string',
            'jumlah_bayar' => 'required|integer',
            'bukti_pembayaran' => 'nullable|string',
            'status' => 'in:pending,terverifikasi,gagal'
        ]);

        $pembayaran = Pembayaran::create($request->all());
        return response()->json($pembayaran, 201);
    }

    public function show($id)
    {
        return Pembayaran::with('pesanan')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->update($request->all());

        return response()->json($pembayaran);
    }

    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->delete();

        return response()->json(['message' => 'Pembayaran deleted']);
    }
}
