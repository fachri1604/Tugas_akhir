<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = (string) config('midtrans.server_key');
        Config::$isProduction = (bool)   config('midtrans.is_production'); // sandbox=false
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Webhook Midtrans (server -> server).
     */
    public function notificationHandler(Request $request)
    {
        try {
            $notif = new Notification();

            $orderId    = (string) ($notif->order_id ?? '');
            $trxStatus  = (string) ($notif->transaction_status ?? 'pending');
            $fraud      = (string) ($notif->fraud_status ?? '');
            $trxId      = (string) ($notif->transaction_id ?? '');
            $payType    = (string) ($notif->payment_type ?? '');
            $gross      = (string) ($notif->gross_amount ?? '');

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

            Log::info('[Midtrans][notif] masuk', compact('orderId','trxStatus','fraud','trxId','payType','gross'));

            $pesanan = Pesanan::with('detailPesanans.produk')->where('id_pesanan', $orderId)->first();
            if (!$pesanan) {
                return response()->json(['ok' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
            }

            // Simpan metadata transaksi
            if ($this->columnExists('pesanans', 'midtrans_order_id')) {
                $pesanan->midtrans_order_id = $trxId;
            }
            if ($this->columnExists('pesanans', 'payment_type') && $payType) {
                $pesanan->payment_type = $payType;
            }
            if ($this->columnExists('pesanans', 'midtrans_gross_amount') && $gross !== '') {
                $pesanan->midtrans_gross_amount = $gross;
            }

            // Map status Midtrans -> status internal
            if (
                ($trxStatus === 'capture' && $fraud === 'accept') ||
                $trxStatus === 'settlement'
            ) {
                $this->settleOrder($pesanan); // status = success
            } elseif ($trxStatus === 'pending' || $fraud === 'challenge') {
                $pesanan->status = 'pending';
                $pesanan->save();
            } else {
                $pesanan->status = 'failed';
                $pesanan->save();
            }

            return response()->json(['ok' => true, 'order_id' => $orderId, 'status' => $pesanan->status], 200);
        } catch (\Throwable $e) {
            Log::error('[Midtrans][notif] error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
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

            $order = Pesanan::with('detailPesanans.produk')->where('id_pesanan', $orderId)->firstOrFail();

            if ($this->columnExists('pesanans', 'midtrans_order_id')) {
                $order->midtrans_order_id = $trxId;
            }
            if ($this->columnExists('pesanans', 'payment_type') && $payType) {
                $order->payment_type = $payType;
            }
            if ($this->columnExists('pesanans', 'midtrans_gross_amount') && $gross !== '') {
                $order->midtrans_gross_amount = $gross;
            }

            if (in_array($trxStatus, ['capture','settlement'], true)) {
                $this->settleOrder($order); // status = success
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

    private function settleOrder(Pesanan $pesanan): void
    {
        if ($pesanan->status === 'success') {
            return; // sudah sukses sebelumnya
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->loadMissing('detailPesanans.produk');
            foreach ($pesanan->detailPesanans as $d) {
                if ($d->produk) {
                    $d->produk->stok = max(0, (int)$d->produk->stok - (int)$d->jumlah);
                    $d->produk->save();
                }
            }
            $pesanan->status = 'success'; // sesuai enum
            $pesanan->save();
        });
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function paymentSuccess($id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);
        $this->settleOrder($pesanan);
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
}
