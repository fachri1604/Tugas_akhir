<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;
use Midtrans\Notification;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans sekali saja
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production'); // false kalau sandbox
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // =========================
    // 1. HANDLE NOTIFIKASI MIDTRANS
    // =========================
    public function notificationHandler(Request $request)
    {
        $notification = new Notification();

        $midtransOrderId = $notification->order_id; // order_id unik dari midtrans
        $transaction_status = $notification->transaction_status;
        $fraud_status = $notification->fraud_status;

        // Cari pesanan berdasarkan midtrans_order_id
        $pesanan = Pesanan::with('detailPesanans.produk')->where('midtrans_order_id', $midtransOrderId)->first();

        if ($pesanan) {
            if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
                if ($fraud_status == 'challenge') {
                    $pesanan->status = 'pending';
                } else {
                    // Cek apakah sebelumnya status belum success untuk mencegah pengurangan stok berkali-kali
                    if ($pesanan->status !== 'success') {
                        $pesanan->status = 'success';

                        // Kurangi stok produk hanya sekali saat sukses
                        foreach ($pesanan->detailPesanans as $detail) {
                            $produk = $detail->produk;
                            $produk->stok = max(0, $produk->stok - $detail->jumlah);
                            $produk->save();
                        }
                    }
                }
            } elseif ($transaction_status == 'pending') {
                $pesanan->status = 'pending';
            } elseif (in_array($transaction_status, ['deny', 'expire', 'cancel'])) {
                $pesanan->status = 'failed';
            }

            $pesanan->save();
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================
    // 2. HALAMAN SUCCESS / UNFINISH / ERROR
    // =========================
    public function paymentSuccess($id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);
        $pesanan->status = 'success';
        $pesanan->save();

        return redirect()->route('payment.show', $id_pesanan);
    }

    public function paymentUnfinish($id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);
        $pesanan->status = 'pending';
        $pesanan->save();

        return redirect()->route('payment.show', $id_pesanan);
    }

    public function paymentError($id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);
        $pesanan->status = 'failed';
        $pesanan->save();

        return redirect()->route('payment.show', $id_pesanan);
    }

    // =========================
    // 3. HALAMAN PAYMENT
    // =========================
    public function show($id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);

        // Jika belum punya midtrans_order_id, buat dan simpan
        if (!$pesanan->midtrans_order_id) {
            $pesanan->midtrans_order_id = $pesanan->id_pesanan . '-' . time();
            $pesanan->save();
        }

        // Buat Snap Token Midtrans pakai midtrans_order_id
        $snapToken = $this->createMidtransTransaction($pesanan);

        return view('payment', compact('pesanan', 'snapToken'));
    }

    // =========================
    // 4. FUNGSI BUAT SNAP TOKEN
    // =========================
    private function createMidtransTransaction($pesanan)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $pesanan->midtrans_order_id,
                'gross_amount' => $pesanan->total_harga,
            ],
            'customer_details' => [
                'first_name' => $pesanan->nama_pelanggan ?? 'Customer',
                'email'      => $pesanan->email ?? 'customer@example.com',
                'phone'      => $pesanan->no_hp ?? '08123456789',
            ],
            'callbacks' => [
                'finish' => url("/payment/success/{$pesanan->id_pesanan}"),
                'unfinish' => url("/payment/unfinish/{$pesanan->id_pesanan}"),
                'error' => url("/payment/error/{$pesanan->id_pesanan}")
            ]
        ];

        return Snap::getSnapToken($params);
    }
}
