<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;
use App\Support\SizeStock; // ⬅️ PENTING: helper stok per-ukuran

class CartController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('id_user', $user->id_user)
            ->where('status', 'pending')
            ->first();

        return view('cart', compact('pesanan'));
    }

    // (Opsional) halaman payment terpisah; boleh tidak dipakai bila Snap langsung di checkout
    public function checkout()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('id_user', $user->id_user)
            ->where('status', 'pending')
            ->firstOrFail();

        foreach ($pesanan->detailPesanans as $detail) {
            if ($detail->produk->stok < $detail->jumlah) {
                return redirect()->route('cart.index')
                    ->with('error', "Stok produk '{$detail->produk->nama_produk}' tidak cukup.");
            }
        }

        Config::$serverKey    = (string) config('midtrans.server_key');
        Config::$isProduction = (bool)   config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        $params = [
            'transaction_details' => [
                'order_id'     => $pesanan->id_pesanan . '-' . time(),
                'gross_amount' => (int) $pesanan->total_harga,
            ],
            'customer_details' => [
                'first_name' => (string) $user->name,
                'email'      => (string) $user->email,
                'phone'      => (string) ($user->phone ?? $user->no_hp),
                'address'    => (string) ($user->alamat ?? ''),
            ],
            'item_details' => $pesanan->detailPesanans->map(function ($item) {
                return [
                    'id'       => (string) $item->id_produk,
                    'price'    => (int)    $item->produk->harga,
                    'quantity' => (int)    $item->jumlah,
                    'name'     => (string) $item->produk->nama_produk,
                ];
            })->toArray(),
        ];

        $snapToken = Snap::getSnapToken($params);
        return view('payment', compact('pesanan', 'snapToken'));
    }

public function add(Request $request, $id_produk)
{
    $produk = Produk::findOrFail($id_produk);

    if ($this->isOutOfStock($produk)) {
        return back()->with('error', 'Stok produk habis.');
    }

    // ----------------------------------------------------
    // Ambil rules dengan aman (bisa return array / Collection / null)
    // ----------------------------------------------------
    $res = $this->buildValidationRules($produk);

    // Jika Collection -> jadikan array ber-index numerik
    if ($res instanceof \Illuminate\Support\Collection) {
        $res = $res->values()->all();
    } elseif (!is_array($res)) {
        // cast (misal null -> [], object -> array)
        $res = (array) $res;
    }

    // pastikan index 0..2 tersedia
    $res = array_values($res);           // reindex numeric keys
    $res = array_pad($res, 3, []);      // tambahkan default apabila kurang
    [$allowedSizeKeys, $allowedColorKeys, $rules] = $res;

    // safeguard: pastikan semuanya array
    $allowedSizeKeys  = is_array($allowedSizeKeys)  ? $allowedSizeKeys  : (array)$allowedSizeKeys;
    $allowedColorKeys = is_array($allowedColorKeys) ? $allowedColorKeys : (array)$allowedColorKeys;
    $rules            = is_array($rules)            ? $rules            : (array)$rules;

    // ----------------------------------------------------
    // Validasi input
    // ----------------------------------------------------
    $validated = $request->validate($rules, [
        'ukuran.in' => 'Ukuran tidak tersedia untuk produk ini.',
        'warna.in'  => 'Warna tidak tersedia untuk produk ini.',
    ]);

    // ----------------------------------------------------
    // Normalisasi input dari helper normalizeInput() dengan aman
    // ----------------------------------------------------
    $res2 = $this->normalizeInput($validated, $allowedSizeKeys, $allowedColorKeys, $produk);

    if ($res2 instanceof \Illuminate\Support\Collection) {
        $res2 = $res2->values()->all();
    } elseif (!is_array($res2)) {
        $res2 = (array) $res2;
    }
    $res2 = array_pad(array_values($res2), 4, null); // ensure 4 slots
    [$sizeKey, $colorKey, $ukuranLabel, $warnaLabel] = $res2;

    /** @var \App\Models\User $user */
    $user = Auth::user();

    // Cek sudah pernah dibeli
    if ($this->alreadyBought($produk, $user, $sizeKey)) {
        return back()->with('error', 'Ukuran ini sudah pernah kamu beli. Pilih ukuran lain.');
    }

    // Ambil/buat pesanan pending
    $pesanan = $this->findOrCreatePendingOrder($user);

    $qtyRequested = (int) ($validated['jumlah'] ?? 1);

    if ($this->exceedsStock($produk, $pesanan, $sizeKey, $colorKey, $qtyRequested)) {
        return back()->with('error', 'Jumlah melebihi stok yang tersedia.');
    }

    $this->addToCart($pesanan, $produk, $sizeKey, $colorKey, $qtyRequested);

    return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang');
}


/**
 * Cek stok global produk
 */
private function isOutOfStock($produk): bool
{
    return (int)$produk->stok <= 0;
}

/**
 * Bangun rules validasi berdasarkan ukuran & warna
 */
/**
 * Bangun rules validasi berdasarkan ukuran & warna
 */
private function parseStockString(?string $value): array
{
    if (!$value) return [];

    // Jika format "S=1,M=1,L=1"
    if (str_contains($value, '=')) {
        $pairs = explode(',', $value);
        $result = [];

        foreach ($pairs as $pair) {
            if (str_contains($pair, '=')) {
                [$key, $val] = explode('=', $pair);
                $result[trim($key)] = (int) $val;
            }
        }
        return $result;
    }

    // Jika hanya teks biasa seperti "Biru" atau "Merah"
    return [trim($value) => 1];
}


private function buildValidationRules($produk)
{
    // ✅ Gunakan kolom yang benar
    $availableSizes = $this->parseStockString($produk->ukuran_tersedia ?? $produk->ukuran ?? '');
    $availableColors = $this->parseStockString($produk->warna ?? '');

    $allowedSizeKeys = array_keys($availableSizes);
    $allowedColorKeys = array_keys($availableColors);

    // Jika kosong, tambahkan placeholder biar validasi tidak error
    $allowedSizeKeys = count($allowedSizeKeys) ? $allowedSizeKeys : ['default'];
    $allowedColorKeys = count($allowedColorKeys) ? $allowedColorKeys : ['default'];

    $rules = [
        'ukuran' => ['required', Rule::in($allowedSizeKeys)],
        'warna'  => ['required', Rule::in($allowedColorKeys)],
        'jumlah' => ['required', 'integer', 'min:1']
    ];

    return [$allowedSizeKeys, $allowedColorKeys, $rules];
}





/**
 * Normalisasi input ukuran & warna
 */
/**
 * Normalisasi input ukuran & warna dengan aman
 */
private function normalizeInput(array $validated, array $allowedSizeKeys, array $allowedColorKeys, $produk): array
{
    $parseList = function (?string $s) {
        if (!$s) return collect();
        return collect(preg_split('/\s*[,;|\/]\s*/', $s))
            ->map(fn($v) => trim($v))
            ->filter();
    };

    $allowedSizeLabels = $parseList($produk->ukuran_tersedia)
        ->map(function ($v) {
            $lower = mb_strtolower($v);
            if (in_array($lower, ['allsize','all size','all-size','all sz','all'])) return 'All Size';
            return preg_match('/^\d+$/', $v) ? $v : strtoupper($v);
        })->unique()->values()->all();

    $allowedColorLabels = $parseList($produk->warna)->unique()->values()->all();

    // ==== Buat mapping aman tanpa array_combine() ====
    $sizeLabelMap = [];
    $lenS = min(count($allowedSizeKeys), count($allowedSizeLabels));
    for ($i = 0; $i < $lenS; $i++) {
        $sizeLabelMap[$allowedSizeKeys[$i]] = $allowedSizeLabels[$i];
    }
    for ($i = $lenS; $i < count($allowedSizeKeys); $i++) {
        $sizeLabelMap[$allowedSizeKeys[$i]] = strtoupper($allowedSizeKeys[$i]);
    }

    $colorLabelMap = [];
    $lenC = min(count($allowedColorKeys), count($allowedColorLabels));
    for ($i = 0; $i < $lenC; $i++) {
        $colorLabelMap[$allowedColorKeys[$i]] = $allowedColorLabels[$i];
    }
    for ($i = $lenC; $i < count($allowedColorKeys); $i++) {
        $colorLabelMap[$allowedColorKeys[$i]] = strtoupper($allowedColorKeys[$i]);
    }

    // ==== Normalisasi input ====
    $sizeKey  = isset($validated['ukuran']) ? trim(mb_strtolower($validated['ukuran'])) : null;
    $colorKey = isset($validated['warna'])  ? trim(mb_strtolower($validated['warna']))  : null;

    $ukuranLabel = $sizeKey !== null ? ($sizeLabelMap[$sizeKey] ?? $sizeKey) : null;
    $warnaLabel  = $colorKey !== null ? ($colorLabelMap[$colorKey] ?? $colorKey) : null;

    return [$sizeKey, $colorKey, $ukuranLabel, $warnaLabel];
}


/**
 * Cek apakah user sudah pernah beli ukuran ini
 */
private function alreadyBought($produk, $user, ?string $sizeKey): bool
{
    $successStatuses = ['paid','success','settlement','capture'];

    return DetailPesanan::query()
        ->where('id_produk', $produk->id_produk)
        ->when($sizeKey !== null, fn($q)=>$q->whereRaw('LOWER(ukuran)=?', [$sizeKey]))
        ->whereHas('pesanan', function ($q) use ($user, $successStatuses) {
            $q->where('id_user', $user->id_user)->whereIn('status', $successStatuses);
        })
        ->exists();
}

/**
 * Ambil / buat pesanan pending user
 */
private function findOrCreatePendingOrder($user)
{
    return Pesanan::firstOrCreate(
        ['id_user' => $user->id_user, 'status' => 'pending'],
        ['total_harga' => 0]
    );
}

/**
 * Cek stok global & stok per ukuran
 */
private function exceedsStock($produk, $pesanan, ?string $sizeKey, ?string $colorKey, int $qtyRequested): bool
{
    $detailQuery = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)
        ->where('id_produk', $produk->id_produk);

    $sizeKey === null ? $detailQuery->whereNull('ukuran') : $detailQuery->whereRaw('LOWER(ukuran)=?', [$sizeKey]);
    $colorKey === null ? $detailQuery->whereNull('warna')  : $detailQuery->whereRaw('LOWER(warna)=?',  [$colorKey]);

    $detail = $detailQuery->first();
    $inCart = $detail ? (int) $detail->jumlah : 0;

    // Cek stok global
    if ($qtyRequested + $inCart > (int) $produk->stok) {
        return true;
    }

    // (Opsional) jika kamu pakai sistem stok per ukuran → tambahkan pengecekan di sini

    return false;
}

/**
 * Tambahkan item ke keranjang
 */
private function addToCart($pesanan, $produk, ?string $sizeKey, ?string $colorKey, int $qtyRequested): void
{
    $detailQuery = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)
        ->where('id_produk', $produk->id_produk);

    $sizeKey === null ? $detailQuery->whereNull('ukuran') : $detailQuery->whereRaw('LOWER(ukuran)=?', [$sizeKey]);
    $colorKey === null ? $detailQuery->whereNull('warna')  : $detailQuery->whereRaw('LOWER(warna)=?',  [$colorKey]);

    $detail = $detailQuery->first();

    if ($detail) {
        $detail->jumlah   += $qtyRequested;
        $detail->subtotal  = $detail->jumlah * $produk->harga;
        $detail->save();
    } else {
        DetailPesanan::create([
            'id_pesanan' => $pesanan->id_pesanan,
            'id_produk'  => $produk->id_produk,
            'jumlah'     => $qtyRequested,
            'ukuran'     => $sizeKey,
            'warna'      => $colorKey,
            'subtotal'   => $qtyRequested * $produk->harga,
        ]);
    }

    $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
    $pesanan->save();
}

    public function update(Request $request, $id_detail)
    {
        $detail = DetailPesanan::findOrFail($id_detail);
        $produk = $detail->produk;

        $newQty = (int) $request->jumlah;
        if ($newQty < 1) $newQty = 1;

        // Cek stok global
        if ($newQty > (int) $produk->stok) {
            return back()->with('error', 'Jumlah melebihi stok yang tersedia.');
        }

        // Cek stok per-ukuran (bila ada angkanya & detail memiliki ukuran)
        $sizeKey = $detail->ukuran ? trim(mb_strtolower($detail->ukuran)) : null;
        if ($sizeKey !== null) {
            $sizeMap = SizeStock::parse((string)$produk->ukuran_tersedia);
            if (isset($sizeMap[$sizeKey]) && $sizeMap[$sizeKey]['stock'] !== null) {
                $sizeStock = (int) $sizeMap[$sizeKey]['stock'];

                // berapa qty item varian ini selain current detail dalam pesanan yang sama?
                $inCartOther = DetailPesanan::where('id_pesanan', $detail->id_pesanan)
                    ->where('id_produk', $detail->id_produk)
                    ->whereRaw('LOWER(ukuran)=?', [$sizeKey])
                    ->where('id_detail', '!=', $detail->id_detail)
                    ->sum('jumlah');

                if ($newQty + (int)$inCartOther > $sizeStock) {
                    $maks = max(0, $sizeStock - (int)$inCartOther);
                    return back()->with('error', 'Jumlah melebihi stok ukuran. Maksimal: ' . $maks);
                }
            }
        }

        $detail->jumlah   = $newQty;
        $detail->subtotal = $detail->jumlah * $produk->harga;
        $detail->save();

        $pesanan = $detail->pesanan;
        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui');
    }

    public function remove($id_detail)
    {
        $detail  = DetailPesanan::findOrFail($id_detail);
        $pesanan = $detail->pesanan;

        $detail->delete();

        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang');
    }

    public function checkoutForm($id_pesanan)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $pesanan = Pesanan::with('detailPesanans.produk')
                ->where('id_pesanan', $id_pesanan)
                ->where('id_user', $user->id_user)
                ->where('status', 'pending')
                ->firstOrFail();

            // ✅ Hitung total berat di server
            $totalWeight = $this->calculateTotalWeight($pesanan);

            // Ambil provinsi dari API
            $response = Http::withHeaders(['key' => config('rajaongkir.api_key')])
                ->get(rtrim(config('rajaongkir.base_url'), '/') . '/destination/province');

            if (!$response->successful()) {
                throw new \Exception("API request failed with status: " . $response->status());
            }

            $responseData = $response->json();
            if (!isset($responseData['data']) || !is_array($responseData['data'])) {
                throw new \Exception("Invalid API response format");
            }

            $provinces = collect($responseData['data'])->map(fn($item) => [
                'id'   => $item['id'],
                'name' => $item['name'],
            ]);

            $cities = collect(); // diisi via AJAX setelah pilih provinsi

            $couriers = [
                ['code' => 'jne',     'name' => 'JNE'],
                ['code' => 'sicepat', 'name' => 'SiCepat'],
                ['code' => 'jnt',     'name' => 'JNT'],
            ];

            $originCityId = config('rajaongkir.origin');

            return view('checkoutform', compact(
                'pesanan',
                'provinces',
                'cities',
                'originCityId',
                'couriers',
                'totalWeight'
            ));
        } catch (\Exception $e) {
            Log::error('CheckoutForm Error: ' . $e->getMessage());

            return view('checkoutform', [
                'pesanan'      => null,
                'provinces'    => collect([]),
                'cities'       => collect([]),
                'originCityId' => config('rajaongkir.origin'),
                'couriers'     => [],
                'api_error'    => 'Gagal memuat data provinsi dari API.'
            ]);
        }
    }

    public function checkoutProcess(Request $request, $id_pesanan)
    {        
        $data = $request->validate([
            'alamat'      => 'required|string|max:255',
            'provinsi'    => 'required',
            'kota'        => 'required',
            'district_id' => 'required',
            'kurir'       => 'required|string',
            'service'     => 'required|string',
            'ongkir'      => 'required|integer|min:0',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('id_pesanan', $id_pesanan)
            ->where('id_user', $user->id_user)
            ->firstOrFail();

        // Simpan alamat terbaru pengguna
        $user->alamat = (string) $data['alamat'];
        $user->save();

        // ✅ Hitung ulang berat di server (abaikan input user)
        $serverWeight = $this->calculateTotalWeight($pesanan);

        // Verifikasi ongkir ke API; jika gagal, pakai nilai dari client
        try {
            $resp     = $this->callOngkirApi([
                'origin'      => (string) config('rajaongkir.origin'),
                'destination' => (string) $data['district_id'],
                'weight'      => (int)    $serverWeight,   // ⬅️ pakai berat server
                'courier'     => (string) $data['kurir'],
                'price'       => 'lowest',
            ]);
            $rows       = $this->normalizeOngkirResponse($resp);
            $selected   = $this->pickService($rows, $data['service']);
            $ongkir     = (int) $selected['cost'];
            $service    = (string) $selected['service'];
            $serviceDesc = (string) $selected['desc'];
            $etd        = (string) $selected['etd'];
        } catch (\Throwable $e) {
            Log::warning('[Ongkir] fallback to client value: ' . $e->getMessage());
            $ongkir     = (int) $data['ongkir'];
            $service    = (string) $data['service'];
            $serviceDesc = '';
            $etd        = '';
        }

        // Update informasi pengiriman pada pesanan
        $pesanan->provinsi_id  = $data['provinsi'];
        $pesanan->kota_id      = $data['kota'];
        $pesanan->district_id  = $data['district_id'];
        $pesanan->kurir        = strtolower($data['kurir']);
        $pesanan->service_code = $service;
        $pesanan->service_desc = $serviceDesc;
        $pesanan->etd          = $etd;
        $pesanan->weight       = (int) $serverWeight;  // ⬅️ simpan berat yang benar
        $pesanan->ongkir       = (int) $ongkir;
        $pesanan->status       = 'pending';

        // === Build item_details + hitung GROSS dari SUM TOTAL ===
        [$itemDetails, $gross] = $this->buildItemsAndGross($pesanan, (int) $ongkir);

        // Simpan total ke DB agar konsisten dengan gross yang akan ditagih Midtrans
        $pesanan->total_harga = (int) $gross;
        $pesanan->save();

        // Midtrans config
        Config::$serverKey    = (string) config('midtrans.server_key');
        Config::$isProduction = (bool)   config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        // Order ID unik untuk Midtrans
        $midtransOrderId = 'ORD-' . $pesanan->id_pesanan . '-' . Str::upper(Str::random(6));
        $pesanan->midtrans_order_id = $midtransOrderId;
        $pesanan->save();

        // Payload Midtrans: gross_amount = SUM TOTAL
        $params = [
            'transaction_details' => [
                'order_id'     => $midtransOrderId,
                'gross_amount' => (int) $gross,
            ],
            'customer_details' => [
                'first_name' => (string) $user->name,
                'email'      => (string) $user->email,
                'phone'      => (string) ($user->phone ?? $user->no_hp),
                'billing_address'  => ['address' => (string) ($user->alamat ?? '')],
                'shipping_address' => ['address' => (string) ($user->alamat ?? '')],
            ],
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['success' => true, 'snap_token' => $snapToken]);
        } catch (\Throwable $e) {
            Log::error('[Midtrans] ' . $e->getMessage(), ['params' => $params]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyiapkan pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** ================== Helpers ================== */

    // ✅ Helper: hitung total berat dari item keranjang
    private function calculateTotalWeight(Pesanan $pesanan): int
    {
        // jumlah total item di keranjang (menjumlahkan kolom 'jumlah' di semua detail)
        $totalItems = 0;
        foreach ($pesanan->detailPesanans as $d) {
            $totalItems += (int) ($d->jumlah ?? 0);
        }

        // 1 kg per item -> 1000 gram × total item
        $totalWeightGrams = $totalItems * 1000;

        // minimal 1 gram agar API tidak error
        return max(1, $totalWeightGrams);
    }

    private function callOngkirApi(array $payload): array
    {
        $api = rtrim((string) config('rajaongkir.base_url'), '/') . '/calculate/district/domestic-cost';
        $resp = Http::asForm()->withHeaders(['key' => (string) config('rajaongkir.api_key')])->post($api, $payload);

        if (!$resp->ok()) {
            throw new \RuntimeException('HTTP ' . $resp->status() . ' dari layanan ongkir.');
        }
        $json = $resp->json();
        if (!is_array($json)) {
            throw new \RuntimeException('Respons ongkir bukan JSON yang valid.');
        }
        return $json;
    }

    private function normalizeOngkirResponse(array $resp): array
    {
        $rows = [];

        if (isset($resp['data']['costs']) && is_array($resp['data']['costs'])) {
            foreach ($resp['data']['costs'] as $c) {
                $first = $c['cost'][0] ?? ['value' => 0, 'etd' => ''];
                $rows[] = [
                    'courier' => strtoupper((string) ($c['courier'] ?? '')),
                    'service' => (string) ($c['service'] ?? ''),
                    'desc'    => (string) ($c['description'] ?? ''),
                    'cost'    => (int)    ($first['value'] ?? 0),
                    'etd'     => (string) ($first['etd'] ?? ''),
                ];
            }
            return $rows;
        }

        if (($resp['meta']['status'] ?? null) === 'success' && isset($resp['data']) && is_array($resp['data'])) {
            foreach ($resp['data'] as $c) {
                $rows[] = [
                    'courier' => strtoupper((string) ($c['code'] ?? $c['name'] ?? '')),
                    'service' => (string) ($c['service'] ?? ''),
                    'desc'    => (string) ($c['description'] ?? ''),
                    'cost'    => (int)    ($c['cost'] ?? 0),
                    'etd'     => (string) ($c['etd'] ?? ''),
                ];
            }
        }

        return $rows;
    }

    private function pickService(array $rows, ?string $requestedService): array
    {
        if ($requestedService) {
            foreach ($rows as $r) {
                if (strcasecmp($r['service'], $requestedService) === 0) return $r;
            }
        }
        usort($rows, fn($a, $b) => $a['cost'] <=> $b['cost']);
        return $rows[0] ?? ['courier' => '', 'service' => '', 'desc' => '', 'cost' => 0, 'etd' => ''];
    }

    // === Helper: buat item_details dan hitung gross dari sum total ===
    private function buildItemsAndGross(Pesanan $pesanan, int $ongkir): array
    {
        $items = $pesanan->detailPesanans->map(function ($d) {
            return [
                'id'       => (string) $d->id_produk,
                'price'    => (int)    $d->produk->harga,
                'quantity' => (int)    $d->jumlah,
                'name'     => (string) $d->produk->nama_produk,
            ];
        })->values()->all();

        $items[] = [
            'id'       => 'SHIPPING',
            'price'    => (int) $ongkir,
            'quantity' => 1,
            'name'     => 'Ongkos Kirim',
        ];

        $gross = 0;
        foreach ($items as $it) {
            $gross += ((int)$it['price']) * ((int)$it['quantity']);
        }

        return [$items, $gross];
    }

    // Bayar di Lokasi (COD) – tidak terpengaruh perubahan berat
    public function payOnSite(Request $request, $id_pesanan)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('id_pesanan', $id_pesanan)
            ->where('id_user', $user->id_user)
            ->where('status', 'pending') // tetap pending, karena enum cuma 3 nilai
            ->firstOrFail();

        // Cek stok (opsional)
        foreach ($pesanan->detailPesanans as $detail) {
            if ($detail->produk->stok < $detail->jumlah) {
                return redirect()->route('cart.index')
                    ->with('error', "Stok produk '{$detail->produk->nama_produk}' tidak cukup.");
            }
        }

        // Tandai sebagai COD via midtrans_order_id, status tetap pending
        $pesanan->midtrans_order_id = 'COD-' . $pesanan->id_pesanan . '-' . Str::upper(Str::random(6));
        $pesanan->status = 'pending'; // biarkan pending sampai admin proses
        $pesanan->save();

        return redirect()
            ->route('orders.thankyou', $pesanan->id_pesanan)
            ->with('success', 'Pesanan dibuat sebagai Bayar di Lokasi (COD). Admin akan memproses.');
    }
}
