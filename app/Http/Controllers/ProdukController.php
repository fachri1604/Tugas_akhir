<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Support\SizeStock; // ⬅️ helper untuk parsing ukuran+stok

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
     * Simpan produk baru
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
            'images'           => 'required',
            'images.*'         => 'image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
            'primary_index'    => 'nullable|integer|min:0',
        ]);

        // Normalisasi ukuran: ubah ke bentuk standar (SizeStock::build)
        // dan hitung stok total (jumlah angka pada setiap ukuran)
        $rawSizes = $validated['ukuran_tersedia'] ?? '';
        $parsed = SizeStock::parse($rawSizes);          // hasil map: key => ['label'=>..., 'stock'=>...]
        $normalizedSizes = SizeStock::build($parsed);   // string yang akan disimpan di DB

        // compute total stock dari parsed map: jumlah semua angka (null/empty diabaikan)
        $totalStock = 0;
        foreach ($parsed as $row) {
            if (isset($row['stock']) && $row['stock'] !== null && $row['stock'] !== '') {
                $totalStock += (int) $row['stock'];
            }
        }

        // siapkan data untuk disimpan (tanpa images)
        $dataToSave = collect($validated)->except(['images','primary_index'])->toArray();
        // pastikan kolom ukuran_tersedia berisi normalisasi
        $dataToSave['ukuran_tersedia'] = $normalizedSizes;
        // pastikan stok tidak null (jika admin tidak mengisi angka ukuran -> totalStock = 0)
        $dataToSave['stok'] = $totalStock;

        $produk = Produk::create($dataToSave);

        // Upload images ke folder per produk
        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";
        $uploaded = [];

        foreach ($request->file('images', []) as $file) {
            if (!$file) continue;
            $name = uniqid('img_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($baseDir, $name, 'public');
            $uploaded[] = $path;
        }

        // tentukan foto utama
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

        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";
        $files   = Storage::disk('public')->exists($baseDir) ? Storage::disk('public')->files($baseDir) : [];
        $existingImages = array_values(array_filter($files, fn($p) => preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $p)));

        return view('admin.formproduk', compact('produk', 'kategoris', 'existingImages'));
    }

    /**
     * Update produk
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
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
            'primary_index'    => 'nullable|integer|min:0',
            'primary_path'     => 'nullable|string',
            'delete_paths'     => 'array',
            'delete_paths.*'   => 'string',
        ]);

        // Normalisasi ukuran dan hitung stok total
        $rawSizes = $validated['ukuran_tersedia'] ?? ($produk->ukuran_tersedia ?? '');
        $parsed = SizeStock::parse($rawSizes);
        $normalizedSizes = SizeStock::build($parsed);

        $totalStock = 0;
        foreach ($parsed as $row) {
            if (isset($row['stock']) && $row['stock'] !== null && $row['stock'] !== '') {
                $totalStock += (int) $row['stock'];
            }
        }

        $updateData = collect($validated)->except(['images','primary_index','primary_path','delete_paths'])->toArray();
        $updateData['ukuran_tersedia'] = $normalizedSizes;
        $updateData['stok'] = $totalStock;

        $produk->update($updateData);

        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";

        // Hapus foto jika diminta
        if ($request->filled('delete_paths')) {
            foreach ($request->delete_paths as $path) {
                if (is_string($path) && str_starts_with($path, $baseDir)) {
                    Storage::disk('public')->delete($path);
                }
            }
            if ($produk->gambar_produk && in_array($produk->gambar_produk, $request->delete_paths)) {
                $produk->update(['gambar_produk' => null]);
            }
        }

        // Upload file baru
        $newFiles = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if (!$file) continue;
                $name = uniqid('img_') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($baseDir, $name, 'public');
                $newFiles[] = $path;
            }
        }

        // Set utama dari batch baru
        if ($request->filled('primary_index')) {
            $idx = (int) $request->primary_index;
            if (isset($newFiles[$idx])) {
                $produk->update(['gambar_produk' => $newFiles[$idx]]);
            }
        }

        // Atau set utama dari foto lama
        if ($request->filled('primary_path') && Storage::disk('public')->exists($request->primary_path)) {
            $produk->update(['gambar_produk' => $request->primary_path]);
        }

        // Fallback: jika belum ada utama, ambil salah satu
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

        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";
        if (Storage::disk('public')->exists($baseDir)) {
            Storage::disk('public')->deleteDirectory($baseDir);
        }

        $produk->delete();
        return redirect()->route('admin.produk')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Katalog
     */
    public function katalog(Request $request)
    {
        $q          = trim((string) $request->get('q', ''));
        $kategoriId = $request->get('kategori_id');
        $warna      = trim((string) $request->get('warna', ''));
        $ukuran     = trim((string) $request->get('ukuran', ''));
        $hargaMin   = $request->get('harga_min');
        $hargaMax   = $request->get('harga_max');
        $sort       = $request->get('sort');

        $produkQuery = Produk::with('kategori')->where('stok', '>', 0);

        if ($q !== '') {
            $produkQuery->where(function($w) use ($q) {
                $w->where('nama_produk', 'like', "%{$q}%")
                  ->orWhere('deskripsi', 'like', "%{$q}%");
            });
        }
        if (!empty($kategoriId)) $produkQuery->where('kategori_id', $kategoriId);
        if ($warna !== '') $produkQuery->where('warna', 'like', "%{$warna}%");
        if ($ukuran !== '') $produkQuery->where('ukuran_tersedia', 'like', "%{$ukuran}%");

        switch ($sort) {
            case 'termurah':  $produkQuery->orderBy('harga', 'asc'); break;
            case 'termahal':  $produkQuery->orderBy('harga', 'desc'); break;
            case 'nama':      $produkQuery->orderBy('nama_produk', 'asc'); break;
            default:          $produkQuery->latest('id_produk');
        }

        $produks = $produkQuery->paginate(12)->withQueryString();
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
     * Detail produk (beli)
     */
    public function beli($id)
    {
        $produk = Produk::findOrFail($id);

        $warnaArray = [];
        if (!empty($produk->warna)) {
            $warnaArray = array_map('trim', explode(',', $produk->warna));
        }

        $pk      = $produk->getKey();
        $baseDir = "produk_images/{$pk}";
        $files   = Storage::disk('public')->exists($baseDir) ? Storage::disk('public')->files($baseDir) : [];
        $images  = array_values(array_filter($files, fn($p) => preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $p)));

        // parse ukuran jadi map (key => ['label','stock'])
        $sizeMap = SizeStock::parse((string) $produk->ukuran_tersedia);
        $sizeOptions = collect($sizeMap)->map(function ($row, $key) {
            return [
                'key'   => $key,
                'label' => $row['label'] ?? '',
                'stock' => $row['stock'],
            ];
        })->values();

        // ukuran sudah dibeli user login (opsional)
        $alreadyBoughtSizes = collect();
        if (Auth::check()) {
            $user = Auth::user();
            $successStatuses = ['paid','success','settlement','capture'];
            $alreadyBoughtSizes = DetailPesanan::query()
                ->where('id_produk', $produk->id_produk)
                ->whereNotNull('ukuran')
                ->whereHas('pesanan', function ($q) use ($user, $successStatuses) {
                    $q->where('id_user', $user->id_user)->whereIn('status', $successStatuses);
                })
                ->pluck('ukuran')
                ->map(fn($u) => mb_strtolower(trim($u)))
                ->unique()
                ->values();
        }

        return view('produk2', compact(
            'produk','warnaArray','images','sizeOptions','alreadyBoughtSizes'
        ));
    }
   public function getUkuran($id)
{
    $produk = \App\Models\Produk::find($id);

    if (!$produk || !$produk->ukuran_tersedia) {
        return response()->json([]);
    }

    // Pecah string seperti "S:10, M:5, L:3" jadi array ["S", "M", "L"]
    $ukuranList = collect(explode(',', $produk->ukuran_tersedia))
        ->map(function ($item) {
            $parts = explode(':', trim($item));
            return trim($parts[0]);
        })
        ->filter()
        ->values();

    return response()->json($ukuranList);
}

}
