@extends('layouts.admin')

@section('content')
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
        Selamat Datang di Dashboard Admin
    </h2>

    {{-- FILTER TANGGAL --}}
    <form action="{{ route('admin.dashboard') }}" method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from ?? '' }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to ?? '' }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="flex items-end">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Tampilkan
            </button>
        </div>
        <div class="flex items-end">
            <a href="{{ route('admin.dashboard') }}"
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 inline-block">
               Reset
            </a>
        </div>
    </form>

    {{-- KARTU-KARTU --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Total Produk</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalProduk ?? 0) }}</p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Pengguna</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalPengguna ?? 0) }}</p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Pesanan</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalPesanan ?? 0) }}</p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Dikirim (success)</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalDikirim ?? 0) }}</p>
        </div>

        <div class="bg-white p-4 rounded shadow text-center">
            <h3 class="text-lg font-medium text-gray-600">Belum Dikirim (pending)</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalBelumDikirim ?? 0) }}</p>
        </div>
    </div>

    {{-- RINGKASAN PENJUALAN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-800">Ringkasan Penjualan</h3>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="border rounded p-3 text-center">
                    <div class="text-sm text-gray-500">Jumlah Pesanan</div>
                    <div class="text-2xl font-bold">{{ number_format($ringkasan->jumlah_pesanan ?? 0) }}</div>
                </div>
                <div class="border rounded p-3 text-center">
                    <div class="text-sm text-gray-500">Omzet (Rp)</div>
                    <div class="text-2xl font-bold">{{ number_format($ringkasan->omzet ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- TOP PRODUK --}}
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-800">Produk Terlaris</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left bg-gray-50">
                            <th class="px-3 py-2">Produk</th>
                            <th class="px-3 py-2">Qty</th>
                            <th class="px-3 py-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($popularProduk as $row)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $row->nama_produk }}</td>
                                <td class="px-3 py-2">{{ number_format($row->qty) }}</td>
                                <td class="px-3 py-2">Rp {{ number_format($row->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TABEL PER HARI + GRAFIK --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Penjualan per Hari</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left bg-gray-50">
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Pesanan</th>
                            <th class="px-3 py-2">Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perHari as $d)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($d->tgl)->format('d M Y') }}</td>
                                <td class="px-3 py-2">{{ number_format($d->jml) }}</td>
                                <td class="px-3 py-2">Rp {{ number_format($d->omzet, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Grafik Omzet per Hari</h3>
            <canvas id="chartOmzet" height="140"></canvas>

            {{-- CDN Chart.js; aman dibiarkan di sini agar tak bergantung layout --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
            (function () {
                const el = document.getElementById('chartOmzet');
                if (!el) return;

                // Data dari server
                const labels = @json(
                    $perHari->pluck('tgl')->map(fn($d)=> \Carbon\Carbon::parse($d)->format('d M'))->values()
                );
                const data   = @json(
                    $perHari->pluck('omzet')->map(fn($v)=>(float)$v)->values()
                );

                if (typeof Chart === 'undefined') {
                    console.warn('Chart.js tidak termuat.');
                    return;
                }

                const ctx = el.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Omzet (Rp)',
                            data,
                            tension: 0.25,
                            borderWidth: 2,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: true } },
                        scales: {
                            x: { ticks: { autoSkip: true, maxTicksLimit: 10 } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => 'Rp ' + Number(v||0).toLocaleString('id-ID')
                                }
                            }
                        }
                    }
                });
            })();
            </script>
        </div>
    </div>
@endsection
