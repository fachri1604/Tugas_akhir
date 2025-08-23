<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    // =================== ADMIN (tetap) ===================

    public function index()
    {
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
        $total   = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');

        $pesanan->total_harga = (int) $total;
        $pesanan->save();

        return response()->json($pesanan);
    }

    public function updateStatus(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,diproses,selesai,dibatalkan,success,failed',
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

    // =================== USER (riwayat) ===================

    /**
     * List riwayat milik user yang sedang login.
     */
    public function riwayat(Request $request)
    {
        // gunakan kolom id_user (bukan id)
        $userId = Auth::user()->id_user;

        $pesanans = Pesanan::withCount('detailPesanans')
            ->where('id_user', $userId)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // view kamu bernama 'riwayatpesanan.blade.php'
        return view('riwayatpesanan', compact('pesanans'));
    }

    /**
     * Detail satu pesanan milik user (route model binding by id_pesanan).
     * Route: /riwayat-pesanan/{pesanan:id_pesanan}
     */
    public function riwayatShow(Pesanan $pesanan)
    {
        // pastikan yang akses adalah pemiliknya
        if ((int) $pesanan->id_user !== (int) Auth::user()->id_user) {
            abort(403);
        }

        $pesanan->loadMissing(['detailPesanans.produk', 'user']);

        // view kamu bernama 'riwayatpesanan-detail.blade.php'
        return view('riwayatpesanan-detail', compact('pesanan'));
    }
}
