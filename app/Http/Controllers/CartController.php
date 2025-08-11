<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class CartController extends Controller
{
    // Tampilkan isi keranjang
    public function index()
    {
        $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('id_user', Auth::user()->id_user)
            ->where('status', 'pending')
            ->first();

        return view('cart', compact('pesanan'));
    }

     public function checkout()
{
    $pesanan = Pesanan::with('detailPesanans.produk')
        ->where('id_user', Auth::user()->id_user)
        ->where('status', 'pending')
        ->firstOrFail();

    // Validasi stok setiap produk
    foreach ($pesanan->detailPesanans as $detail) {
        if ($detail->produk->stok < $detail->jumlah) {
            return redirect()->route('cart.index')->with('error', 
                "Stok produk '{$detail->produk->nama_produk}' tidak cukup. Stok tersedia: {$detail->produk->stok}");
        }
    }

    // Jika semua stok cukup, lanjut buat transaksi Midtrans
    Config::$serverKey = config('midtrans.server_key');
    Config::$isProduction = config('midtrans.is_production');
    Config::$isSanitized = true;
    Config::$is3ds = true;

    $params = [
        'transaction_details' => [
            'order_id' => $pesanan->id_pesanan,
            'gross_amount' => $pesanan->total_harga,
        ],
        'customer_details' => [
            'first_name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ],
        'item_details' => $pesanan->detailPesanans->map(function ($item) {
            return [
                'id' => $item->id_produk,
                'price' => $item->produk->harga,
                'quantity' => $item->jumlah,
                'name' => $item->produk->nama_produk,
            ];
        })->toArray()
    ];

    $snapToken = Snap::getSnapToken($params);

    return view('payment', compact('pesanan', 'snapToken'));
}


    // Tambah produk ke keranjang
    public function add(Request $request, $id_produk)
    {
        $produk = Produk::findOrFail($id_produk);

        // Cari pesanan pending milik user
        $pesanan = Pesanan::firstOrCreate(
            [
                'id_user' => Auth::user()->id_user,
                'status' => 'pending'
            ],
            [
                'total_harga' => 0
            ]
        );

        // Cek apakah produk sudah ada di keranjang
        $detail = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)
            ->where('id_produk', $produk->id_produk)
            ->first();

        if ($detail) {
            // Update jumlah
            $detail->jumlah += $request->input('jumlah', 1);
            $detail->subtotal = $detail->jumlah * $produk->harga;
            $detail->save();
        } else {
            // Tambahkan produk baru
            DetailPesanan::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'id_produk'  => $produk->id_produk,
                'jumlah'     => $request->input('jumlah', 1),
                'subtotal'   => $request->input('jumlah', 1) * $produk->harga
            ]);
        }

        // Update total harga pesanan
        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    // Update jumlah produk di keranjang
    public function update(Request $request, $id_detail)
    {
        $detail = DetailPesanan::findOrFail($id_detail);
        $detail->jumlah = $request->jumlah;
        $detail->subtotal = $detail->jumlah * $detail->produk->harga;
        $detail->save();

        // Update total pesanan
        $pesanan = $detail->pesanan;
        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui');
    }

    // Hapus produk dari keranjang
    public function remove($id_detail)
    {
        $detail = DetailPesanan::findOrFail($id_detail);
        $pesanan = $detail->pesanan;
        $detail->delete();

        // Update total harga pesanan
        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang');
    }
}
