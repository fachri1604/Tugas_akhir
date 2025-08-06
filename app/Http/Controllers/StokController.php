<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
use App\Models\Produk;

class StokController extends Controller
{
    // Menampilkan form tambah stok
    public function create()
    {
        $produks = Produk::all();
        $warnaList = Produk::pluck('warna')->toArray();
        $ukuranList = Produk::pluck('ukuran_tersedia')->toArray();

        // Gabungkan semua string jadi satu, lalu explode, lalu unique dan sortir
        $warnas = collect($warnaList)
            ->flatMap(function ($item) {
                return array_map('trim', explode(',', $item));
            })
            ->unique()
            ->sort()
            ->values()
            ->all();

        $ukurans = collect($ukuranList)
            ->flatMap(function ($item) {
                return array_map('trim', explode(',', $item));
            })
            ->unique()
            ->sort()
            ->values()
            ->all();



        return view('admin.formstok', compact('produks', 'warnas', 'ukurans'));
    }
    public function getKombinasi($id)
{
    $produk = Produk::findOrFail($id);

    // Misalnya warnas dan ukurans disimpan dalam JSON di kolom produk
    $warnas = json_decode($produk->warna, true);    // Contoh: ["Merah", "Biru"]
    $ukurans = json_decode($produk->ukuran_tersedia, true);  // Contoh: ["S", "M", "L"]

    return response()->json([
        'warnas' => $warnas,
        'ukurans' => $ukurans,
    ]);
}



    // Menampilkan daftar stok
    public function index()
    {
        $stoks = Stok::with('produk')->latest()->paginate(10);
        return view('admin.stok', compact('stoks'));
    }

    // Mendapatkan data produk (ukuran dan warna) via AJAX
    public function getProdukData($id)
    {
        $produk = Produk::findOrFail($id);

        return response()->json([
            'ukuran' => $produk->ukuran ?? '',
            'warna' => $produk->warna ?? ''
        ]);
    }

    // Menyimpan stok baru
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'kombinasi' => 'required|array|min:1',
            'kombinasi.*.warna' => 'required|string',
            'kombinasi.*.ukuran' => 'required|string',
            'kombinasi.*.jumlah' => 'required|integer|min:1',
            'tipe' => 'required|in:tambah,kurang',
            'catatan' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
        ]);


        foreach ($request->kombinasi as $item) {
            Stok::create([
                'produk_id' => $request->produk_id,
                'warna' => $item['warna'],
                'ukuran' => $item['ukuran'],
                'jumlah' => $item['jumlah'],
                'tipe' => $request->tipe,
                'catatan' => $request->catatan,
                'alamat' => $request->alamat,
            ]);
        }

        return redirect()->back()->with('success', 'Stok berhasil ditambahkan!');
    }
}
