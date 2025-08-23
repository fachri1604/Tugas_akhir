@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Detail Pesanan #{{ $pesanan->id_pesanan }}</h1>

<div class="mb-4 space-y-1">
  <div><b>Tanggal:</b> {{ optional($pesanan->created_at)->format('d/m/Y H:i') }}</div>
  <div><b>Status:</b> {{ ucfirst($pesanan->status) }}</div>
  <div><b>Kurir:</b> {{ strtoupper($pesanan->kurir) }} {{ $pesanan->service_code }}</div>
  <div><b>Ongkir:</b> Rp {{ number_format((int)$pesanan->ongkir,0,',','.') }}</div>
  <div><b>Total:</b> Rp {{ number_format((int)$pesanan->total_harga,0,',','.') }}</div>
</div>

<table class="w-full border border-collapse">
  <thead class="bg-gray-100">
    <tr>
      <th class="p-2 border text-left">Produk</th>
      <th class="p-2 border">Qty</th>
      <th class="p-2 border text-right">Harga</th>
      <th class="p-2 border text-right">Subtotal</th>
    </tr>
  </thead>
  <tbody>
    @foreach($pesanan->detailPesanans as $d)
      <tr>
        <td class="p-2 border">
          {{ $d->produk->nama_produk ?? 'Produk' }}
          @if($d->ukuran) <span class="text-gray-500">(Uk: {{ $d->ukuran }})</span>@endif
          @if($d->warna)  <span class="text-gray-500">(Warna: {{ $d->warna }})</span>@endif
        </td>
        <td class="p-2 border text-center">{{ (int)$d->jumlah }}</td>
        <td class="p-2 border text-right">Rp {{ number_format((int)($d->produk->harga ?? 0),0,',','.') }}</td>
        <td class="p-2 border text-right">Rp {{ number_format((int)$d->subtotal,0,',','.') }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="mt-4">
  <a href="{{ route('riwayat.index') }}" class="underline text-blue-600">← Kembali ke Riwayat</a>
</div>
@endsection
