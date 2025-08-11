<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Pending</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; text-align:center; padding:40px;">
    <h1 style="color:orange;">⚠️ Pembayaran Belum Selesai</h1>
    <p>Pembayaran Anda masih menunggu. Silakan selesaikan pembayaran untuk memproses pesanan.</p>

    <div style="margin-top:20px;">
        <p><strong>ID Pesanan:</strong> {{ $pesanan->id ?? '-' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($pesanan->total_harga ?? 0, 0, ',', '.') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($pesanan->status ?? 'pending') }}</p>
    </div>

    <a href="{{ url('/') }}" style="display:inline-block; margin-top:20px; padding:10px 20px; background:orange; color:white; text-decoration:none; border-radius:5px;">Kembali ke Beranda</a>
</body>
</html>
