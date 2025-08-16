<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Pesanan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProduk       = Produk::count();
        $totalPengguna     = User::count();
        $totalPesanan      = Pesanan::count();
        $totalDikirim      = Pesanan::where('status', 'dikirim')->count();
        $totalBelumDikirim = Pesanan::whereIn('status', ['pending','diproses','siap_kirim'])->count();

        return view('admin.dashboard', compact(
            'totalProduk',
            'totalPengguna',
            'totalPesanan',
            'totalDikirim',
            'totalBelumDikirim'
        ));
    }
}
