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
        $produks = Produk::with('kategori')->paginate(10);
        return view('admin.produk', compact('produks'));
    }

    public function create()
    {
        $kategoris = Kategori::all();
        return view('admin.formproduk', compact('kategoris'));
    }

    /**
     * Simpan produk baru dengan SATU input multiple: images[]
     * primary_index (opsional) untuk menentukan foto utama dari batch baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk'      => 'required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'harga'            => 'required|numeric',
            'warna'            => 'nullable|string',
            'ukuran_tersedia'  => 'nullable|string',
            'alamat'           => 'nullable|string',
            'stok'             => 'nullable|integer',
            'kategori_id'      => 'nullable|exists:kategoris,id',

            // ==== SATU INPUT MULTIPLE ====
            'images'           => 'required', // harus ada minimal 1
            'images.*'         => 'image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
            'primary_index'    => 'nullable|integer|min:0',
        ]);

        // 1) Simpan produk (tanpa gambar dulu)
        $produk = Produk::create(collect($validated)->except(['images', 'primary_index'])->toArray());

        // 2) Upload semua gambar ke folder khusus produk
        $pk      = $produk->getKey(); // aman meskipun PK bukan "id"
        $baseDir = "produk_images/{$pk}";
        $uploaded = [];

        foreach ($request->file('images', []) as $file) {
            if (!$file) continue;
            $name = uniqid('img_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($baseDir, $name, 'public');
            $uploaded[] = $path;
        }

        // 3) Tentukan foto utama
        $mainPath = $uploaded[0] ?? null;
        if ($request->filled('primary_index')) {
            $idx = (int) $request->primary_index;
            if (isset($uploaded[$idx])) {
                $mainPath = $uploaded[$idx];
            }
        }

        $produk->update(['gambar_produk' => $mainPath]);

        return redirect()->route('admin.produk')->with('success', 'Produk & foto berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $produk    = Produk::findOrFail($id);
        $kategoris = Kategori::all();

        // Kumpulkan semua foto yang sudah ada di folder produk
        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";
        $files   = Storage::disk('public')->exists($baseDir) ? Storage::disk('public')->files($baseDir) : [];
        $existingImages = array_values(array_filter($files, function ($p) {
            return preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $p);
        }));

        return view('admin.formproduk', compact('produk', 'kategoris', 'existingImages'));
    }

    /**
     * Update data produk + tambah/hapus/set utama gambar
     * Tetap memakai SATU input multiple: images[]
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validated = $request->validate([
            'nama_produk'      => 'required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'harga'            => 'required|numeric',
            'warna'            => 'nullable|string',
            'ukuran_tersedia'  => 'nullable|string',
            'alamat'           => 'nullable|string',
            'stok'             => 'nullable|integer',
            'kategori_id'      => 'nullable|exists:kategoris,id',

            // tambah batch baru (opsional)
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
            'primary_index'    => 'nullable|integer|min:0', // pilih utama dari batch baru
            'primary_path'     => 'nullable|string',        // set utama dari foto lama
            'delete_paths'     => 'array',
            'delete_paths.*'   => 'string',                 // path relatif yang ingin dihapus
        ]);

        // Update field non-file
        $produk->update(collect($validated)->except(['images', 'primary_index', 'primary_path', 'delete_paths'])->toArray());

        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";

        // 1) Hapus foto yang dipilih
        if ($request->filled('delete_paths')) {
            foreach ($request->delete_paths as $path) {
                if (is_string($path) && str_starts_with($path, $baseDir)) {
                    Storage::disk('public')->delete($path);
                }
            }
            // Jika yang terhapus adalah foto utama, kosongkan dulu
            if ($produk->gambar_produk && in_array($produk->gambar_produk, $request->delete_paths)) {
                $produk->update(['gambar_produk' => null]);
            }
        }

        // 2) Upload batch baru (jika ada)
        $newFiles = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if (!$file) continue;
                $name = uniqid('img_') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($baseDir, $name, 'public');
                $newFiles[] = $path;
            }
        }

        // 3) Set utama dari batch baru (primary_index)
        if ($request->filled('primary_index')) {
            $idx = (int) $request->primary_index;
            if (isset($newFiles[$idx])) {
                $produk->update(['gambar_produk' => $newFiles[$idx]]);
            }
        }

        // 4) Atau set utama dari gambar lama (primary_path)
        if ($request->filled('primary_path')) {
            $target = $request->primary_path;
            if (is_string($target) && str_starts_with($target, $baseDir) && Storage::disk('public')->exists($target)) {
                $produk->update(['gambar_produk' => $target]);
            }
        }

        // 5) Fallback: kalau belum ada utama, ambil salah satu yang tersedia
        if (!$produk->gambar_produk) {
            $all = Storage::disk('public')->exists($baseDir) ? Storage::disk('public')->files($baseDir) : [];
            $imagesOnly = array_values(array_filter($all, fn($p) => preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $p)));
            if (!empty($imagesOnly)) {
                $produk->update(['gambar_produk' => $imagesOnly[0]]);
            }
        }

        return redirect()->route('admin.produk')->with('success', 'Produk & foto berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        // Hapus seluruh folder foto produk agar bersih
        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";
        if (Storage::disk('public')->exists($baseDir)) {
            Storage::disk('public')->deleteDirectory($baseDir);
        }

        $produk->delete();

        return redirect()->route('admin.produk')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Katalog produk.
     * (Opsional) Tambahkan gallery ke tiap produk bila ingin mini-slider di kartu katalog.
     */
public function katalog(Request $request)
{
    $q          = trim((string) $request->get('q', ''));
    $kategoriId = $request->get('kategori_id');
    $warna      = trim((string) $request->get('warna', ''));
    $ukuran     = trim((string) $request->get('ukuran', ''));
    $hargaMin   = $request->get('harga_min');
    $hargaMax   = $request->get('harga_max');
    $sort       = $request->get('sort'); // 'terbaru','termurah','termahal','nama'

    $produkQuery = Produk::with('kategori')
        ->where('stok', '>', 0); // ⬅️ hanya tampilkan produk yang stoknya masih ada

    // Search text di nama/deskripsi
    if ($q !== '') {
        $produkQuery->where(function($w) use ($q) {
            $w->where('nama_produk', 'like', "%{$q}%")
              ->orWhere('deskripsi', 'like', "%{$q}%");
        });
    }

    // Filter kategori (opsional)
    if (!empty($kategoriId)) {
        $produkQuery->where('kategori_id', $kategoriId);
    }

    // Filter warna (opsional)
    if ($warna !== '') {
        $produkQuery->where('warna', 'like', "%{$warna}%");
    }

    // Filter ukuran (opsional)
    if ($ukuran !== '') {
        $produkQuery->where('ukuran_tersedia', 'like', "%{$ukuran}%");
    }

    // Filter rentang harga (opsional)
    if ($hargaMin !== null && $hargaMin !== '') {
        $produkQuery->where('harga', '>=', (float) $hargaMin);
    }
    if ($hargaMax !== null && $hargaMax !== '') {
        $produkQuery->where('harga', '<=', (float) $hargaMax);
    }

    // Sorting
    switch ($sort) {
        case 'termurah':  $produkQuery->orderBy('harga', 'asc'); break;
        case 'termahal':  $produkQuery->orderBy('harga', 'desc'); break;
        case 'nama':      $produkQuery->orderBy('nama_produk', 'asc'); break;
        default:          $produkQuery->latest('id_produk'); // terbaru
    }

    $produks = $produkQuery->paginate(12)->withQueryString();

    // OPSIONAL: siapkan gallery per produk (kalau kartumu butuh)
    foreach ($produks as $p) {
        $dir = "produk_images/".$p->getKey();
        $files = Storage::disk('public')->exists($dir)
            ? Storage::disk('public')->files($dir)
            : [];
        $p->gallery = array_values(array_filter($files, fn($x)=>preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $x)));
    }

    $kategoris = Kategori::orderBy('nama_kategori')->get();

    return view('katalog', compact('produks','kategoris','q','kategoriId','warna','ukuran','hargaMin','hargaMax','sort'));
}




    /**
     * Halaman detail / beli: kirim seluruh foto untuk galeri.
     */
    public function beli($id)
    {
        $produk = Produk::findOrFail($id);

        // warna → array
        $warnaArray = [];
        if (!empty($produk->warna)) {
            $warnaArray = array_map('trim', explode(',', $produk->warna));
        }

        // kumpulkan semua foto untuk galeri detail
        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";
        $files   = Storage::disk('public')->exists($baseDir) ? Storage::disk('public')->files($baseDir) : [];
        $images  = array_values(array_filter($files, fn($p) => preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $p)));

        return view('produk2', compact('produk', 'warnaArray', 'images'));
    }
}
