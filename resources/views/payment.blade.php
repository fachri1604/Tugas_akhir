<!DOCTYPE html>
<html>
<head>
    <title>Payment Page</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Halaman Pembayaran</h1>

    @if ($pesanan->status === 'pending')
        <button id="pay-button">Bayar Sekarang</button>
    @elseif ($pesanan->status === 'success')
        <h2 style="color: green;">Pembayaran Berhasil ✅</h2>
    @elseif ($pesanan->status === 'failed')
        <h2 style="color: red;">Pembayaran Gagal ❌</h2>
    @endif

    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
    document.getElementById('pay-button')?.addEventListener('click', function () {
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function(result){
                window.location.href = "{{ route('payment.success', $pesanan->id_pesanan) }}";
            },
            onPending: function(result){
                window.location.href = "{{ route('payment.unfinish', $pesanan->id_pesanan) }}";
            },
            onError: function(result){
                window.location.href = "{{ route('payment.error', $pesanan->id_pesanan) }}";
            }
        });
    });
    </script>
</body>
</html>
