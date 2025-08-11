<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::with('kategori')->paginate(10); // 10 item per halaman
        return view('admin.produk', compact('produks'));
    }

    public function create()
    {
        $kategoris = Kategori::all();
        return view('admin.formproduk', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric',
            'warna' => 'nullable|string',
            'ukuran_tersedia' => 'nullable|string',
            'alamat' => 'nullable|string',
            'stok' => 'nullable|integer',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'gambar_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('gambar_produk')) {
            $path = $request->file('gambar_produk')->store('produk_images', 'public');
            $validatedData['gambar_produk'] = $path;
        }

        Produk::create($validatedData);

        return redirect()->route('admin.produk')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.formproduk', compact('produk', 'kategoris'));
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric',
            'warna' => 'nullable|string',
            'ukuran_tersedia' => 'nullable|string',
            'alamat' => 'nullable|string',
            'stok' => 'nullable|integer',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'gambar_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('gambar_produk')) {
            if ($produk->gambar_produk && Storage::exists('public/' . $produk->gambar_produk)) {
                Storage::delete('public/' . $produk->gambar_produk);
            }

            $path = $request->file('gambar_produk')->store('produk_images', 'public');
            $validatedData['gambar_produk'] = $path;
        }

        $produk->update($validatedData);

        return redirect()->route('admin.produk')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        if ($produk->gambar_produk && Storage::exists('public/' . $produk->gambar_produk)) {
            Storage::delete('public/' . $produk->gambar_produk);
        }

        $produk->delete();

        return redirect()->route('admin.produk')->with('success', 'Produk berhasil dihapus.');
    }

    public function katalog()
    {
        // Ambil data produk terbaru, 9 per halaman
        $produks = Produk::latest()->paginate(9);
        return view('katalog', compact('produks'));
    }

    public function beli($id)
    {
        $produk = Produk::findOrFail($id);

        // Ubah string warna jadi array
        $warnaArray = [];
        if (!empty($produk->warna)) {
            $warnaArray = array_map('trim', explode(',', $produk->warna));
        }

        return view('produk2', compact('produk', 'warnaArray'));
    }
}
