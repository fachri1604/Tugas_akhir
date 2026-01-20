<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check() || strcasecmp(Auth::user()->role ?? '', 'admin') !== 0) {
            abort(403);
        }

        // Validasi ringan (opsional)
        $request->validate([
            'from' => ['nullable','date_format:Y-m-d'],
            'to'   => ['nullable','date_format:Y-m-d'],
        ]);

        // Periode default 30 hari
        $from = $request->query('from') ?: now()->subDays(30)->toDateString();
        $to   = $request->query('to')   ?: now()->toDateString();

        $dateFrom = Carbon::parse($from)->startOfDay();
        $dateTo   = Carbon::parse($to)->endOfDay();
        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
            [$from, $to] = [$dateFrom->toDateString(), $dateTo->toDateString()];
        }

        $base = Pesanan::query()
            ->where('status', 'success')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        // Ringkasan umum
        $ringkasan = (clone $base)
            ->selectRaw('COUNT(*) AS jumlah_pesanan, COALESCE(SUM(total_harga),0) AS omzet')
            ->first();

        // Agregasi per hari: HANYA baris yang ADA transaksi (tanpa mengisi tanggal kosong)
        $perHariRaw = (clone $base)
            ->selectRaw('DATE(created_at) AS tgl, COUNT(*) AS jml, COALESCE(SUM(total_harga),0) AS omzet')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        // Bangun koleksi & data chart langsung dari hasil query
        $perHari     = collect();
        $chartLabels = [];
        $chartData   = [];
        $chartCount  = [];

        foreach ($perHariRaw as $row) {
            // $row->tgl: 'Y-m-d'
            $tglCarbon = Carbon::parse($row->tgl);

            $perHari->push((object)[
                'tgl'   => $tglCarbon->format('Y-m-d'),
                'jml'   => (int) $row->jml,
                'omzet' => (float) $row->omzet,
            ]);

            $chartLabels[] = $tglCarbon->format('d M');     // contoh: 01 Sep
            $chartData[]   = (float) $row->omzet;           // omzet per hari
            $chartCount[]  = (int) $row->jml;               // jumlah pesanan per hari
        }

        // Produk terlaris (tambahkan stok agar bisa ditampilkan di tabel)
        $popularProduk = DetailPesanan::query()
            ->join('pesanans', 'detail_pesanans.id_pesanan', '=', 'pesanans.id_pesanan')
            ->join('produks',   'detail_pesanans.id_produk',  '=', 'produks.id_produk')
            ->whereBetween('pesanans.created_at', [$dateFrom, $dateTo])
            ->where('pesanans.status', 'success')
            ->select(
                'produks.id_produk',
                'produks.nama_produk',
                'produks.stok',
                DB::raw('SUM(detail_pesanans.jumlah) AS qty'),
                DB::raw('COALESCE(SUM(detail_pesanans.subtotal),0) AS subtotal')
            )
            ->groupBy('produks.id_produk','produks.nama_produk','produks.stok')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Kartu-kartu umum
        $totalProduk        = Produk::count();
        $totalPengguna      = User::count();
        $totalPesanan       = Pesanan::count();
        $totalDikirim       = Pesanan::where('status', 'success')->count();
        $totalBelumDikirim  = Pesanan::where('status', 'pending')->count();

        // Metrik stok
        $totalStokBarang    = (int) Produk::sum('stok');
        $jumlahProdukHabis  = (int) Produk::where('stok', 0)->count();

        // Daftar stok menipis
        $threshold = 5;
        $stokMenipis = Produk::select('id_produk','nama_produk','stok','harga')
            ->where('stok', '>=', 0)
            ->where('stok', '<=', $threshold)
            ->orderBy('stok', 'asc')
            ->orderBy('nama_produk')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'from'               => $from,
            'to'                 => $to,
            'ringkasan'          => $ringkasan,
            'perHari'            => $perHari,
            'popularProduk'      => $popularProduk,
            'totalProduk'        => $totalProduk,
            'totalPengguna'      => $totalPengguna,
            'totalPesanan'       => $totalPesanan,
            'totalDikirim'       => $totalDikirim,
            'totalBelumDikirim'  => $totalBelumDikirim,

            'chartLabels'        => $chartLabels,
            'chartData'          => $chartData,
            'chartCount'         => $chartCount,

            'totalStokBarang'    => $totalStokBarang,
            'jumlahProdukHabis'  => $jumlahProdukHabis,
            'stokMenipis'        => $stokMenipis,
            'threshold'          => $threshold,
        ]);
    }
}
