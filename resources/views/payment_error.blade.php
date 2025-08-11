<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Gagal</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; text-align:center; padding:40px;">
    <h1 style="color:red;">❌ Pembayaran Gagal</h1>
    <p>Maaf, terjadi kesalahan saat memproses pembayaran Anda.</p>

    <div style="margin-top:20px;">
        <p><strong>ID Pesanan:</strong> {{ $pesanan->id ?? '-' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($pesanan->total_harga ?? 0, 0, ',', '.') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($pesanan->status ?? 'failed') }}</p>
    </div>

    <a href="{{ url('/') }}" style="display:inline-block; margin-top:20px; padding:10px 20px; background:red; color:white; text-decoration:none; border-radius:5px;">Kembali ke Beranda</a>
</body>
</html>
