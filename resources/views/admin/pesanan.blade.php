@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Daftar Pesanan</h1>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">
        {{ session('success') }}
    </div>
@endif

<table class="w-full border border-collapse">
    <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border w-12 text-center">No</th>
            <th class="p-2 border">ID Pesanan</th>
            <th class="p-2 border">User</th>
            <th class="p-2 border">Total Harga</th>
            <th class="p-2 border">Status</th>
            <th class="p-2 border">Tanggal</th>
            <th class="p-2 border">Detail</th>
            <th class="p-2 border">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pesanans as $pesanan)
        <tr>
            {{-- Kolom Nomor --}}
            <td class="p-2 border text-center">
                {{ $loop->iteration + ($pesanans->firstItem() - 1) }}
            </td>

            <td class="p-2 border">
                {{ $pesanan->id_pesanan }}
                @php
                    $isCOD = isset($pesanan->midtrans_order_id) && strpos($pesanan->midtrans_order_id, 'COD-') === 0;
                @endphp
                @if($isCOD)
                    <span class="ml-2 text-xs px-2 py-0.5 rounded bg-gray-200">COD</span>
                @endif
            </td>
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
                <ul class="list-disc list-inside text-sm">
                    @foreach($pesanan->detailPesanans as $detail)
                        <li>{{ $detail->produk->nama_produk }} - Jumlah: {{ $detail->jumlah }}</li>
                    @endforeach
                </ul>
            </td>
            <td class="p-2 border">
                <div class="flex flex-wrap gap-2">

                    {{-- Recalc Total --}}
                    <form action="{{ route('admin.pesanan.update_total', $pesanan->id_pesanan) }}"
                          method="POST"
                          onsubmit="return confirm('Hitung ulang total pesanan ini?');">
                        @csrf @method('PUT')
                        <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">
                            Recalc Total
                        </button>
                    </form>

                    {{-- Set Pending --}}
                    <form id="pendingForm-{{ $pesanan->id_pesanan }}"
                    action="{{ route('admin.pesanan.update_status', $pesanan->id_pesanan) }}"
                    method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="pending">

                    <button type="button"
                        onclick="openPendingModal('{{ $pesanan->id_pesanan }}')"
                        class="px-3 py-1 rounded bg-yellow-500 hover:bg-yellow-600 text-white text-sm">
                        Set Pending
                    </button>
                </form>

                    {{-- Set Success --}}
                    {{-- <form action="{{ route('admin.pesanan.update_status', $pesanan->id_pesanan) }}"
                          method="POST"
                          onsubmit="return confirm('Tandai SUCCESS dan kurangi stok?');">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="success">
                        <button class="px-3 py-1 rounded bg-green-600 hover:bg-green-700 text-white text-sm">
                            Set Success
                        </button>
                    </form> --}}

                    <!-- Tombol Set Success yang menampilkan modal pembayaran -->
<button type="button"
    onclick="openPaymentModal('{{ $pesanan->id_pesanan }}', '{{ $pesanan->total_harga }}')"
    class="px-3 py-1 rounded bg-green-600 hover:bg-green-700 text-white text-sm">
    Pembayaran Kasir
</button>

<!-- Modal Form Pembayaran -->
<div id="paymentModal" class="fixed inset-0 hidden bg-gray-900 bg-opacity-50  items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-lg font-semibold mb-4">Form Pembayaran Kasir (COD)</h2>

        <form id="paymentForm">
            @csrf
            <input type="hidden" id="id_pesanan" name="id_pesanan">
            <input type="hidden" id="total_harga" name="total_harga">

            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Total Harga</label>
                <input type="text" id="total_display" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Jumlah Uang Diterima (Rp)</label>
                <input type="number" id="uang_diterima" name="uang_diterima" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Kembalian</label>
                <input type="text" id="kembalian" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
            </div>

            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 bg-gray-400 text-white rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded">Selesaikan Pembayaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal(idPesanan, totalHarga) {
        document.getElementById('id_pesanan').value = idPesanan;
        document.getElementById('total_harga').value = totalHarga;
        document.getElementById('total_display').value = 'Rp ' + parseInt(totalHarga).toLocaleString('id-ID');
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    // Hitung kembalian otomatis
    document.getElementById('uang_diterima').addEventListener('input', function() {
        const total = parseInt(document.getElementById('total_harga').value);
        const uang = parseInt(this.value);
        const kembalian = uang - total;
        document.getElementById('kembalian').value = (kembalian >= 0)
            ? 'Rp ' + kembalian.toLocaleString('id-ID')
            : 'Uang kurang Rp ' + Math.abs(kembalian).toLocaleString('id-ID');
    });

    // Kirim data ke server
    document.getElementById('paymentForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const idPesanan = document.getElementById('id_pesanan').value;
        const uang = document.getElementById('uang_diterima').value;

        const response = await fetch(`/admin/pesanan/${idPesanan}/bayar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ uang_diterima: uang }),
        });

        const data = await response.json();
        if (data.success) {
            alert('Pembayaran berhasil! Pesanan sudah diset ke Success.');
            location.reload();
        } else {
            alert('Terjadi kesalahan: ' + data.message);
        }
    });
     let pendingFormId = null;

    function openPendingModal(idPesanan) {
        pendingFormId = 'pendingForm-' + idPesanan;
        const modal = document.getElementById('pendingModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePendingModal() {
        const modal = document.getElementById('pendingModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingFormId = null;
    }

    function submitPendingForm() {
        if (pendingFormId) {
            document.getElementById(pendingFormId).submit();
        }
    }
</script>


                    {{-- Set Failed --}}
                    <form action="{{ route('admin.pesanan.update_status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="failed">
                        <button class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-sm">
                            Set Failed
                        </button>
                    </form>

                    {{-- Hapus --}}
                    <form action="{{ route('admin.pesanan.destroy', $pesanan->id_pesanan) }}"
                          method="POST"
                          onsubmit="return confirm('Hapus pesanan ini?');">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1 rounded bg-red-700 hover:bg-red-800 text-white text-sm">
                            Hapus
                        </button>
                    </form>
                </div>
            </td>
            <!-- Modal Konfirmasi Set Pending -->
<div id="pendingModal"
     class="fixed inset-0 hidden bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 text-center">
        <h2 class="text-lg font-semibold mb-3">Konfirmasi</h2>
        <p class="mb-5">Apakah Anda ingin mengatur pesanan ini menjadi <b>Pending</b>?</p>

        <div class="flex justify-center gap-3">
            <button onclick="closePendingModal()"
                class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded">
                Batal
            </button>

            <button onclick="submitPendingForm()"
                class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded">
                Ya, Set Pending
            </button>
        </div>
    </div>
</div>

        </tr>
        @empty
        <tr>
            <td colspan="8" class="p-2 text-center">Belum ada pesanan</td>
        </tr>
        @endforelse
    </tbody>
    
</table>

<div class="mt-4">
    {{ $pesanans->links() }}
</div>
@endsection
