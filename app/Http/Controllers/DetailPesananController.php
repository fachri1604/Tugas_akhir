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
        // Validasi dasar
        $validated = $request->validate([
            'id_pesanan' => 'required|exists:pesanans,id_pesanan',
            'id_produk'  => 'required|exists:produks,id_produk',
            'jumlah'     => 'required|integer|min:1',
            'ukuran'     => 'nullable|string|max:10', // ← ukuran ikut divalidasi
        ]);

        // Ambil produk untuk harga & validasi ukuran
        $produk = Produk::findOrFail($validated['id_produk']);

        // Normalisasi ukuran ke UPPERCASE
        $ukuran = $request->filled('ukuran')
            ? strtoupper(trim((string) $request->input('ukuran')))
            : null;

        // Jika produk punya daftar ukuran_tersedia, validasi terhadap daftar itu
        if (!empty($produk->ukuran_tersedia)) {
            $allowed = array_values(array_filter(array_map(function ($v) {
                return strtoupper(trim($v));
            }, explode(',', $produk->ukuran_tersedia))));

            if (!is_null($ukuran) && !in_array($ukuran, $allowed, true)) {
                return response()->json([
                    'message' => 'Ukuran tidak valid untuk produk ini.',
                    'allowed' => $allowed,
                ], 422);
            }
        }

        // Hitung subtotal
        $hargaSatuan = $produk->harga;
        $subtotal    = $hargaSatuan * (int) $validated['jumlah'];

        // Simpan detail (dengan ukuran)
        $detail = DetailPesanan::create([
            'id_pesanan'   => $validated['id_pesanan'],
            'id_produk'    => $validated['id_produk'],
            'jumlah'       => (int) $validated['jumlah'],
            'ukuran'       => $ukuran,          // ← simpan ukuran
            'harga_satuan' => $hargaSatuan,
            'subtotal'     => $subtotal,
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
        $detail = DetailPesanan::with('produk')->findOrFail($id);

        // Validasi input
        $validated = $request->validate([
            'jumlah' => 'sometimes|integer|min:1',
            'ukuran' => 'sometimes|nullable|string|max:10', // ← bisa update ukuran juga
        ]);

        // Jika minta update ukuran, validasi terhadap ukuran_tersedia produk (jika ada)
        if ($request->has('ukuran')) {
            $ukuranBaru = $request->filled('ukuran')
                ? strtoupper(trim((string) $request->input('ukuran')))
                : null;

            if (!empty($detail->produk->ukuran_tersedia)) {
                $allowed = array_values(array_filter(array_map(function ($v) {
                    return strtoupper(trim($v));
                }, explode(',', $detail->produk->ukuran_tersedia))));

                if (!is_null($ukuranBaru) && !in_array($ukuranBaru, $allowed, true)) {
                    return response()->json([
                        'message' => 'Ukuran tidak valid untuk produk ini.',
                        'allowed' => $allowed,
                    ], 422);
                }
            }

            $detail->ukuran = $ukuranBaru;
        }

        // Jika jumlah diubah, perbarui subtotal
        if (array_key_exists('jumlah', $validated)) {
            $detail->jumlah   = (int) $validated['jumlah'];
            $detail->subtotal = $detail->harga_satuan * $detail->jumlah;
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
