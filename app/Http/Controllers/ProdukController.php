<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Stok;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function edit($id)
    {
        $produk = Produk::with('stoks')->findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.formproduk', compact('produk', 'kategoris'));
    }
    public function getDetail($id)
    {
        $produk = Produk::findOrFail($id);
        return response()->json([
            'warna' => explode(',', $produk->warna),
            'ukuran' => explode(',', $produk->ukuran),
        ]);
    }
    public function getAttributes($id)
{
    $produk = Produk::with(['warnas', 'ukurans'])->findOrFail($id);

    return response()->json([
        'warnas' => $produk->warnas->pluck('nama'),    // Asumsi: relasi `warnas`
        'ukurans' => $produk->ukurans->pluck('nama'),  // Asumsi: relasi `ukurans`
    ]);
}


    public function index()
    {
        $produks = Produk::with(['kategori', 'stoks'])->get();
        return view('admin.produk', compact('produks'));
    }

    public function create()
    {
        $kategoris = Kategori::all();
        return view('admin.formproduk', compact('kategoris'));
    }

    public function show($id)
    {
        $produk = Produk::with(['kategori', 'stoks'])->find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $produk
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            // 'stok_awal' => 'required|integer|min:0', // Ubah dari 'stok' ke 'stok_awal'
            'harga' => 'required|integer|min:0',
            'warna' => 'nullable|string|max:100',
            'ukuran_tersedia' => 'nullable|string|max:100',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar_produk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            // 'lokasi' => 'required|string|max:100', // Tambahkan lokasi stok                          
            'catatan' => 'nullable|string',
        ]);

        if ($request->hasFile('gambar_produk')) {
            $path = $request->file('gambar_produk')->store('produk_images', 'public');
            $validatedData['gambar_produk'] = $path;
        }

        // Simpan produk
        $produk = Produk::create($validatedData);

        // Buat record stok awal
        $warnas = $request->input('warna');
        $ukurans = $request->input('ukuran');
        $jumlahs = $request->input('jumlah');

        if (is_array($warnas) && is_array($ukurans) && is_array($jumlahs)) {
            foreach ($warnas as $i => $warna) {
                foreach ($ukurans as $j => $ukuran) {
                    $jumlah = $jumlahs[$i][$j] ?? null;

                    if (is_numeric($jumlah) && $jumlah > 0) {
                        Stok::create([
                            'id_produk' => $produk->id_produk ?? $produk->id,
                            'warna' => $warna,
                            'ukuran' => $ukuran,
                            'jumlah' => $jumlah,
                            'tipe' => 'tambah',
                            'alamat' => $validatedData['lokasi'],
                            'catatan' => $validatedData['catatan'] ?? '-',
                        ]);
                    }
                }
            }
        }


        return redirect()->route('admin.formproduk')->with('success', 'Produk dan stok awal berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'stok' => 'required|integer|min:0',
            'harga' => 'required|integer|min:0',
            'warna' => 'nullable|string|max:100',
            'ukuran_tersedia' => 'nullable|string|max:100',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar_produk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'lokasi' => 'required|string|max:100'
        ]);

        if ($request->hasFile('gambar_produk')) {
            if ($produk->gambar_produk && Storage::exists('public/' . $produk->gambar_produk)) {
                Storage::delete('public/' . $produk->gambar_produk);
            }

            $path = $request->file('gambar_produk')->store('produk_images', 'public');
            $validatedData['gambar_produk'] = $path;
        }

        // Hitung selisih stok untuk update
        $selisih = $validatedData['stok'] - $produk->stok;

        if ($selisih != 0) {
            Stok::create([
                'id_produk' => $produk->id_produk,
                'tipe' => $selisih > 0 ? 'masuk' : 'keluar',
                'jumlah' => abs($selisih),
                'alamat' => $validatedData['lokasi'],
                'catatan' => 'Penyesuaian stok',
                'total' => abs($selisih) * $validatedData['harga']
            ]);
        }

        $produk->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Produk dan stok berhasil diperbarui',
            'data' => $produk
        ]);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        if ($produk->gambar_produk && Storage::exists('public/' . $produk->gambar_produk)) {
            Storage::delete('public/' . $produk->gambar_produk);
        }

        // Hapus semua stok terkait produk ini
        $produk->stoks()->delete();

        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk dan stok terkait berhasil dihapus'
        ]);
    }
}
