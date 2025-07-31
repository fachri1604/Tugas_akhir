<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function edit($id)
{
    $produk = Produk::findOrFail($id);
    $kategoris = Kategori::all();
    return view('admin.formproduk', compact('produk', 'kategoris'));
}
    public function index()
    {
        $produks = Produk::with('kategori')->get();
        return view('admin.produk', compact('produks'));
    }

    public function create()
    {
        $kategoris = Kategori::all();
        return view('admin.formproduk', compact('kategoris'));
    }

    public function show($id)
    {
        $produk = Produk::with('kategori')->find($id);

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
        // dd($request->all());                
        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|integer|min:0',
            'warna' => 'nullable|string|max:100',
            'ukuran_tersedia' => 'nullable|string|max:100',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar_produk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('gambar_produk')) {
            $path = $request->file('gambar_produk')->store('produk_images', 'public');
            $validatedData['gambar_produk'] = $path;
        }

        Produk::create($validatedData);

        return redirect()->route('admin.formproduk')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|integer|min:0',
            'warna' => 'nullable|string|max:100',
            'ukuran_tersedia' => 'nullable|string|max:100',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar_produk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('gambar_produk')) {
            if ($produk->gambar_produk && Storage::exists('public/' . $produk->gambar_produk)) {
                Storage::delete('public/' . $produk->gambar_produk);
            }

            $path = $request->file('gambar_produk')->store('produk_images', 'public');
            $validatedData['gambar_produk'] = $path; // sudah rapi, tidak perlu str_replace

        }

        $produk->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $produk
        ]);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        if ($produk->gambar_produk && Storage::exists('public/' . $produk->gambar_produk)) {
            Storage::delete('public/' . $produk->gambar_produk);
        }

        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }
    
}
