@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Daftar Pesanan</h1>

<table class="w-full border border-collapse">
    <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border">ID Pesanan</th>
            <th class="p-2 border">User</th>
            <th class="p-2 border">Total Harga</th>
            <th class="p-2 border">Status</th>
            <th class="p-2 border">Tanggal</th>
            <th class="p-2 border">Detail</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pesanans as $pesanan)
        <tr>
            <td class="p-2 border">{{ $pesanan->id_pesanan }}</td>
            <td class="p-2 border">{{ $pesanan->user->name ?? '-' }}</td>
            <td class="p-2 border">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
            <td class="p-2 border">
                @if($pesanan->status == 'success')
                    <span class="text-green-600 font-semibold">Success</span>
                @elseif($pesanan->status == 'pending')
                    <span class="text-yellow-600 font-semibold">Pending</span>
                @elseif($pesanan->status == 'failed')
                    <span class="text-red-600 font-semibold">Failed</span>
                @else
                    <span>{{ $pesanan->status }}</span>
                @endif
            </td>
            <td class="p-2 border">{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
            <td class="p-2 border">
                <ul>
                    @foreach($pesanan->detailPesanans as $detail)
                        <li>{{ $detail->produk->nama_produk }} - Jumlah: {{ $detail->jumlah }}</li>
                    @endforeach
                </ul>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="p-2 text-center">Belum ada pesanan</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $pesanans->links() }}
</div>
@endsection
