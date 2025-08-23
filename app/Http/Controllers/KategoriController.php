<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    /**
     * List kategori (10 per halaman) + pencarian opsional ?q=...
     * Dibuat tanpa parameter agar aman dipanggil dari route closure.
     */
    public function index()
    {
        $q = trim((string) request('q', ''));

        $kategoris = Kategori::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama_kategori', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString(); // pertahankan parameter ?q

        return view('admin.kategori', compact('kategoris', 'q'));
    }

    /**
     * Form create.
     */
    public function create()
    {
        return view('admin.formkategori');
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', 'unique:kategoris,nama_kategori'],
        ]);

        Kategori::create($validated);

        return redirect()
            ->route('admin.kategori')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Form edit.
     */
    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.formkategori', compact('kategori'));
    }

    /**
     * Update kategori.
     */
    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategoris', 'nama_kategori')->ignore($kategori->id),
            ],
        ]);

        $kategori->update($validated);

        return redirect()
            ->route('admin.kategori')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori.
     */
    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return redirect()
            ->route('admin.kategori')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
