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
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // (opsional) kunci hanya admin
        if (!Auth::check() || strcasecmp(Auth::user()->role ?? '', 'admin') !== 0) {
            abort(403);
        }

        // Periode: default 30 hari terakhir
        $from = $request->query('from') ?: now()->subDays(30)->toDateString();
        $to   = $request->query('to')   ?: now()->toDateString();

        $dateFrom = Carbon::parse($from)->startOfDay();
        $dateTo   = Carbon::parse($to)->endOfDay();

        // Basis query pesanan sukses pada periode
        $base = Pesanan::query()
            ->where('status', 'success')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        // Ringkasan
        $ringkasan = (clone $base)
            ->selectRaw('COUNT(*) AS jumlah_pesanan, COALESCE(SUM(total_harga),0) AS omzet')
            ->first();

        // Agregasi per hari (raw)
        $perHariRaw = (clone $base)
            ->selectRaw('DATE(created_at) AS tgl, COUNT(*) AS jml, COALESCE(SUM(total_harga),0) AS omzet')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        // Lengkapi tanggal yang kosong supaya grafik mulus
        $period = CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay());
        $map    = $perHariRaw->keyBy('tgl');

        $perHari = collect();
        $chartLabels = [];
        $chartData   = []; // omzet
        $chartCount  = []; // jumlah pesanan

        foreach ($period as $day) {
            $key = $day->toDateString();
            $row = $map->get($key);

            $tgl  = $day->format('Y-m-d');
            $jml  = (int) ($row->jml   ?? 0);
            $omzt = (int) ($row->omzet ?? 0);

            $perHari->push((object)[
                'tgl'   => $tgl,
                'jml'   => $jml,
                'omzet' => $omzt,
            ]);

            $chartLabels[] = $day->format('d M');
            $chartData[]   = (float) $omzt;
            $chartCount[]  = (int) $jml;
        }

        // TOP 5 produk terlaris (periode & hanya pesanan sukses)
        $popularProduk = DetailPesanan::query()
            ->join('pesanans', 'detail_pesanans.id_pesanan', '=', 'pesanans.id_pesanan')
            ->join('produks',   'detail_pesanans.id_produk',  '=', 'produks.id_produk')
            ->whereBetween('pesanans.created_at', [$dateFrom, $dateTo])
            ->where('pesanans.status', 'success')
            ->select(
                'produks.nama_produk',
                DB::raw('SUM(detail_pesanans.jumlah) AS qty'),
                DB::raw('COALESCE(SUM(detail_pesanans.subtotal),0) AS subtotal')
            )
            ->groupBy('produks.nama_produk')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Kartu-kartu umum
        $totalProduk        = Produk::count();
        $totalPengguna      = User::count();
        $totalPesanan       = Pesanan::count();
        $totalDikirim       = Pesanan::where('status', 'success')->count(); // contoh
        $totalBelumDikirim  = Pesanan::where('status', 'pending')->count(); // contoh

        return view('admin.dashboard', [
            'from'               => $from,
            'to'                 => $to,
            'ringkasan'          => $ringkasan,
            'perHari'            => $perHari,       // sudah berisi semua tanggal
            'popularProduk'      => $popularProduk,
            'totalProduk'        => $totalProduk,
            'totalPengguna'      => $totalPengguna,
            'totalPesanan'       => $totalPesanan,
            'totalDikirim'       => $totalDikirim,
            'totalBelumDikirim'  => $totalBelumDikirim,

            // untuk Chart.js
            'chartLabels'        => $chartLabels,
            'chartData'          => $chartData,
            'chartCount'         => $chartCount,
        ]);
    }
}
