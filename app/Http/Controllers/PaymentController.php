<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Transaction;
use App\Support\SizeStock; // helper stok per-ukuran

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = (string) config('midtrans.server_key');
        Config::$isProduction = (bool)   config('midtrans.is_production'); 
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function notificationHandler(Request $request)
    {
        try {
            $notif = new Notification();

            $orderId   = (string) ($notif->order_id ?? '');
            $trxStatus = (string) ($notif->transaction_status ?? 'pending');
            $fraud     = (string) ($notif->fraud_status ?? '');
            $trxId     = (string) ($notif->transaction_id ?? '');
            $payType   = (string) ($notif->payment_type ?? '');
            $gross     = (string) ($notif->gross_amount ?? '');

            if ($orderId === '') {
                $payload   = $request->all();
                $orderId   = (string) ($payload['order_id'] ?? '');
                $trxStatus = (string) ($payload['transaction_status'] ?? $trxStatus);
                $fraud     = (string) ($payload['fraud_status'] ?? $fraud);
                $trxId     = (string) ($payload['transaction_id'] ?? $trxId);
                $payType   = (string) ($payload['payment_type'] ?? $payType);
                $gross     = (string) ($payload['gross_amount'] ?? $gross);
            }
            if ($orderId === '') {
                return response()->json(['ok' => false, 'message' => 'order_id kosong'], 400);
            }

            Log::info('[Midtrans][notif]', compact('orderId','trxStatus','fraud','trxId','payType','gross'));

            $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('midtrans_order_id', $orderId)
            ->orWhere('id_pesanan', $orderId)
            ->orWhere('order_id', $orderId)
            ->first();

            if (!$pesanan) {
                return response()->json(['ok' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
            }

            if ($this->columnExists('pesanans', 'midtrans_trx_id')) {
                $pesanan->midtrans_trx_id = $trxId;
            }
            if ($this->columnExists('pesanans', 'payment_type') && $payType) {
                $pesanan->payment_type = $payType;
            }
            if ($this->columnExists('pesanans', 'midtrans_gross_amount') && $gross !== '') {
                $pesanan->midtrans_gross_amount = $gross;
            }

            if (
                ($trxStatus === 'capture' && $fraud === 'accept') ||
                $trxStatus === 'settlement'
            ) {
                $this->settleOrder($pesanan);
            } elseif ($trxStatus === 'pending' || $fraud === 'challenge') {
                $pesanan->status = 'pending';
                $pesanan->save();
            } else {
                $pesanan->status = 'failed';
                $pesanan->save();
            }

            return response()->json(['ok' => true, 'order_id' => $orderId, 'status' => $pesanan->status], 200);
        } catch (\Throwable $e) {
            Log::error('[Midtrans][notif] error: '.$e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Server error'], 500);
        }
    }

    public function show($id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);

        $params = [
            'transaction_details' => [
                'order_id'     => (string) $pesanan->id_pesanan,
                'gross_amount' => (int)    $pesanan->total_harga,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);
        return view('payment', compact('pesanan', 'snapToken'));
    }

    public function confirmFromClient(Request $request)
    {
        $orderId = (string) $request->input('order_id');
        if ($orderId === '') {
            return response()->json(['ok' => false, 'message' => 'order_id kosong'], 422);
        }

        try {
            $status = Transaction::status($orderId);
            $st = is_array($status) ? $status : json_decode(json_encode($status), true);

            $trxStatus = (string) ($st['transaction_status'] ?? 'pending');
            $trxId     = (string) ($st['transaction_id']    ?? '');
            $payType   = (string) ($st['payment_type']      ?? '');
            $gross     = (string) ($st['gross_amount']      ?? '');

            $order = Pesanan::with('detailPesanans.produk')
                ->where('midtrans_order_id', $orderId)->firstOrFail();

            if ($this->columnExists('pesanans', 'midtrans_trx_id')) {
                $order->midtrans_trx_id = $trxId;
            }
            if ($this->columnExists('pesanans', 'payment_type') && $payType) {
                $order->payment_type = $payType;
            }
            if ($this->columnExists('pesanans', 'midtrans_gross_amount') && $gross !== '') {
                $order->midtrans_gross_amount = $gross;
            }

            if (in_array($trxStatus, ['capture','settlement'], true)) {
                $this->settleOrder($order);
            } elseif ($trxStatus === 'pending') {
                $order->status = 'pending';
                $order->save();
            } else {
                $order->status = 'failed';
                $order->save();
            }

            return response()->json(['ok' => true, 'status' => $trxStatus, 'transaction_id' => $trxId]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Gagal konfirmasi: '.$e->getMessage()], 500);
        }
    }

    /**
     * Settle order (success): kurangi stok global + stok per-ukuran.
     */
    private function settleOrder(Pesanan $pesanan): void
    {
        if (in_array($pesanan->status, ['success','paid'], true)) {
            return; // sudah settle sebelumnya
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->loadMissing('detailPesanans.produk');

            foreach ($pesanan->detailPesanans as $d) {
                if (!$d->produk) continue;

                $produk = Produk::where('id_produk', $d->id_produk)->lockForUpdate()->first();
                if (!$produk) continue;

                // 1) Kurangi stok global
                $produk->stok = max(0, (int)$produk->stok - (int)$d->jumlah);

                // 2) Kurangi stok per-ukuran jika ada
                $map = SizeStock::parse((string)$produk->ukuran_tersedia);
                $key = mb_strtolower((string)($d->ukuran ?? ''));
                if ($key !== '' && isset($map[$key]) && $map[$key]['stock'] !== null) {
                    $map[$key]['stock'] = max(0, (int)$map[$key]['stock'] - (int)$d->jumlah);
                    $produk->ukuran_tersedia = SizeStock::build($map);
                }

                $produk->save();
            }

            $pesanan->status = 'success';
            $pesanan->save();
        });
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
