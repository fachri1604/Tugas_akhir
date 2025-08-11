<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Berhasil</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; text-align:center; padding:40px;">
    <h1 style="color:green;">✅ Pembayaran Berhasil</h1>
    <p>Terima kasih! Pesanan Anda telah berhasil diproses.</p>

    <div style="margin-top:20px;">
        <p><strong>ID Pesanan:</strong> {{ $pesanan->id ?? '-' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($pesanan->total_harga ?? 0, 0, ',', '.') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($pesanan->status ?? 'success') }}</p>
    </div>

    <a href="{{ url('/') }}" style="display:inline-block; margin-top:20px; padding:10px 20px; background:green; color:white; text-decoration:none; border-radius:5px;">Kembali ke Beranda</a>
</body>
</html>
