<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\Produk;
use Illuminate\Http\Request;

class StokController extends Controller
{
    /**
     * Menampilkan data stok terbaru
     */
    public function index()
    {
        $stoks = Stok::with('produk')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.stok', compact('stoks'));
    }

    /**
     * Form tambah stok
     */
    public function create()
    {
        $produks = Produk::all();
        return view('admin.formstok', compact('produks'));
    }

    /**
     * Simpan stok baru (tanpa duplikasi ukuran)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id'   => 'required|exists:produks,id_produk',
            'tipe'        => 'required|in:masuk,keluar',
            'catatan'     => 'nullable|string',
            'ukuran_data' => 'required|string',
        ]);

        $ukuranData = json_decode($validated['ukuran_data'], true);
        $produk = Produk::findOrFail($validated['produk_id']);

        // 🔹 Ambil ukuran lama dan ubah ke array
        $ukuranTersedia = [];
        if ($produk->ukuran_tersedia) {
            $pairs = explode(',', $produk->ukuran_tersedia);
            foreach ($pairs as $pair) {
                [$uk, $val] = array_pad(explode('=', trim($pair)), 2, 0);
                $ukuranTersedia[$uk] = (int) $val;
            }
        }

        $totalJumlah = 0;

        // 🔹 Loop data ukuran dari form
        foreach ($ukuranData as $data) {
            $ukuran = trim($data['ukuran_baru'] ?: $data['ukuran']);
            $jumlah = (int) $data['jumlah'];

            if (!$ukuran || $jumlah <= 0) continue;

            // Jika ukuran sudah ada, update jumlahnya (tidak buat duplikat)
            if (array_key_exists($ukuran, $ukuranTersedia)) {
                if (strtolower($validated['tipe']) === 'masuk') {
                    $ukuranTersedia[$ukuran] += $jumlah;
                } else {
                    $ukuranTersedia[$ukuran] = max(0, $ukuranTersedia[$ukuran] - $jumlah);
                }
            } else {
                // Jika ukuran belum ada, tambahkan baru
                $ukuranTersedia[$ukuran] = $jumlah;
            }

            // Hitung total jumlah stok untuk disimpan di tabel stok
            $totalJumlah += $jumlah;

            // Update stok global produk
            if (strtolower($validated['tipe']) === 'masuk') {
                $produk->stok += $jumlah;
            } else {
                $produk->stok -= $jumlah;
            }
        }

        // 🔹 Pastikan stok tidak negatif
        if ($produk->stok < 0) $produk->stok = 0;

        // 🔹 Simpan ukuran produk terbaru (tanpa duplikasi)
        $produk->ukuran_tersedia = collect($ukuranTersedia)
            ->map(fn($v, $k) => "{$k}={$v}")
            ->implode(', ');
        $produk->save();

        // 🔹 Buat 1 entri stok saja untuk total transaksi
        Stok::create([
            'produk_id'  => $produk->id_produk,
            'jumlah'     => $totalJumlah,
            'tipe'       => ucfirst(strtolower($validated['tipe'])),
            'catatan'    => $validated['catatan'] ?? '-',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.stok.index')
            ->with('success', "Stok berhasil diperbarui tanpa duplikasi ukuran.");
    }
}
