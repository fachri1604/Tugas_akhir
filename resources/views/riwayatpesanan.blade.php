@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Riwayat Pesanan Saya</h1>

<table class="w-full border border-collapse">
    <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border w-16 text-center">No</th>
            <th class="p-2 border">Tanggal</th>
            <th class="p-2 border text-right">Total</th>
            <th class="p-2 border">Status</th>
            <th class="p-2 border">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pesanans as $p)
        <tr>
            {{-- nomor urut berkelanjutan antar halaman --}}
            <td class="p-2 border text-center">
                {{ ($pesanans->firstItem() ?? 1) + $loop->index }}
            </td>

            <td class="p-2 border">{{ optional($p->created_at)->format('d/m/Y H:i') }}</td>
            <td class="p-2 border text-right">Rp {{ number_format((int)$p->total_harga,0,',','.') }}</td>
            <td class="p-2 border">
                @switch($p->status)
                    @case('success') <span class="text-green-600 font-semibold">Success</span> @break
                    @case('pending') <span class="text-yellow-600 font-semibold">Pending</span> @break
                    @case('failed')  <span class="text-red-600 font-semibold">Failed</span> @break
                    @default <span>{{ ucfirst($p->status) }}</span>
                @endswitch
            </td>
            <td class="p-2 border">
                <a href="{{ route('riwayat.show', $p) }}" class="text-blue-600 underline">Detail</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="p-2 text-center">Belum ada pesanan.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $pesanans->links() }}
</div>
@endsection
