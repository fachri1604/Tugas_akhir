<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    // =================== ADMIN ===================

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

    /**
     * Hitung ulang total_harga dari detail_pesanan (jumlah * harga_satuan/subtotal).
     * Mengembalikan JSON.
     */
    public function updateTotal($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $total   = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');

        $pesanan->total_harga = (int) $total;
        $pesanan->save();

        return response()->json($pesanan);
    }

    /**
     * Ubah status. Otomatis:
     * - pending/failed -> success  => KURANGI stok
     * - success -> pending/failed  => KEMBALIKAN stok
     * - perubahan lain             => hanya ganti status
     *
     * Enum di DB kamu: pending|failed|success
     */
    public function updateStatus(Request $request, $id)
    {
        $pesanan = Pesanan::with(['detailPesanans.produk'])->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,failed,success',
        ]);

        $old = $pesanan->status;
        $new = $validated['status'];

        DB::transaction(function () use ($pesanan, $old, $new) {

            // pending/failed -> success  => KURANGI stok
            if ($old !== 'success' && $new === 'success') {
                foreach ($pesanan->detailPesanans as $d) {
                    $produk = $d->produk()->lockForUpdate()->first();
                    if ($produk->stok < $d->jumlah) {
                        throw new \RuntimeException(
                            "Stok produk '{$produk->nama_produk}' tidak cukup. Sisa: {$produk->stok}, butuh: {$d->jumlah}"
                        );
                    }
                    $produk->stok = $produk->stok - $d->jumlah;
                    $produk->save();
                }
                $pesanan->status = 'success';
                $pesanan->save();
                return;
            }

            // success -> (pending/failed)  => KEMBALIKAN stok
            if ($old === 'success' && $new !== 'success') {
                foreach ($pesanan->detailPesanans as $d) {
                    $produk = $d->produk()->lockForUpdate()->first();
                    $produk->stok = $produk->stok + $d->jumlah;
                    $produk->save();
                }
                $pesanan->status = $new;
                $pesanan->save();
                return;
            }

            // Perubahan lain: hanya ubah status
            $pesanan->status = $new;
            $pesanan->save();
        });

        return response()->json($pesanan->fresh('detailPesanans.produk'));
    }

    public function destroy($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->delete();

        return response()->json(['message' => 'Pesanan berhasil dihapus']);
    }

    // =================== USER (RIWAYAT) ===================

    public function riwayat(Request $request)
    {
        $userId = Auth::user()->id_user;

        $pesanans = Pesanan::withCount('detailPesanans')
            ->where('id_user', $userId)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('riwayatpesanan', compact('pesanans'));
    }

    /**
     * Route model binding by id_pesanan: /riwayat-pesanan/{pesanan:id_pesanan}
     */
    public function riwayatShow(Pesanan $pesanan)
    {
        if ((int) $pesanan->id_user !== (int) Auth::user()->id_user) {
            abort(403);
        }
        $pesanan->loadMissing(['detailPesanans.produk', 'user']);
        return view('riwayatpesanan-detail', compact('pesanan'));
    }
 public function bayar(Request $request, $id)
{
    $pesanan = Pesanan::findOrFail($id);

    if ($request->uang_diterima < $pesanan->total_harga) {
        return response()->json([
            'success' => false,
            'message' => 'Uang kurang'
        ]);
    }

    $pesanan->update([
        'status' => 'success',
        'uang_diterima' => $request->uang_diterima,
        'kembalian' => $request->uang_diterima - $pesanan->total_harga
    ]);

    return response()->json(['success' => true]);
}

}


