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

        if ((int) $produk->stok <= 0) {
            return back()->with('error', 'Stok produk habis.');
        }

        $parseList = function (?string $s) {
            if (!$s) return collect();
            return collect(preg_split('/\s*[,;|\/]\s*/', $s))
                ->map(fn($v) => trim($v))
                ->filter();
        };

        // ====== SIZE (ukuran) ======
        $allowedSizeLabels = $parseList($produk->ukuran_tersedia)
            ->map(function ($v) {
                $lower = mb_strtolower($v);
                if (in_array($lower, ['allsize','all size','all-size','all sz','all'])) return 'All Size';
                return preg_match('/^\d+$/', $v) ? $v : strtoupper($v);
            })->unique()->values();
        $allowedSizeKeys = $allowedSizeLabels->map(fn($l) => trim(mb_strtolower($l)))->values()->toArray();

        // ====== COLOR (warna) ======
        $allowedColorLabels = $parseList($produk->warna)->unique()->values();
        $allowedColorKeys   = $allowedColorLabels->map(fn($v)=>mb_strtolower($v))->values()->toArray();

        // ====== VALIDASI ======
        // Catatan:
        // - ukuran: tetap required jika produk punya daftar ukuran.
        // - warna : TIDAK REQUIRED walaupun produk punya daftar warna; jika diisi, harus termasuk daftar (Rule::in).
        $rules = [
            'jumlah' => ['required','integer','min:1'],
            'ukuran' => empty($allowedSizeKeys)
                ? ['nullable','string','max:20']
                : ['required', Rule::in($allowedSizeKeys)],
        ];

        if (empty($allowedColorKeys)) {
            // produk tidak punya daftar warna -> boleh kosong, tanpa Rule::in
            $rules['warna'] = ['nullable','string','max:30'];
        } else {
            // produk punya daftar warna -> tetap nullable (tidak wajib), tapi kalau diisi harus valid
            $rules['warna'] = ['nullable','string','max:30', Rule::in($allowedColorKeys)];
        }

        $validated = $request->validate($rules, [
            'ukuran.in' => 'Ukuran yang dipilih tidak tersedia untuk produk ini.',
            'warna.in'  => 'Warna yang dipilih tidak tersedia untuk produk ini.',
        ]);

        // Map key -> label untuk penyimpanan yang rapi
        $sizeKey       = $validated['ukuran'] ?? null;
        $colorKey      = $validated['warna']  ?? null;
        $sizeLabelMap  = array_combine($allowedSizeKeys,  $allowedSizeLabels->all());
        $colorLabelMap = array_combine($allowedColorKeys, $allowedColorLabels->all());
        $ukuran        = $sizeKey  !== null ? ($sizeLabelMap[$sizeKey]  ?? $sizeKey)  : null;
        $warna         = $colorKey !== null ? ($colorLabelMap[$colorKey] ?? $colorKey) : null;

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $pesanan = Pesanan::firstOrCreate(
            ['id_user' => $user->id_user, 'status' => 'pending'],
            ['total_harga' => 0]
        );

        // Cek apakah sudah ada item yang sama (id_produk + ukuran (nullable) + warna (nullable))
        $detailQuery = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)
            ->where('id_produk', $produk->id_produk);

        $ukuran === null ? $detailQuery->whereNull('ukuran') : $detailQuery->where('ukuran', $ukuran);
        $warna  === null ? $detailQuery->whereNull('warna')  : $detailQuery->where('warna',  $warna);

        $detail       = $detailQuery->first();
        $qtyRequested = (int) ($validated['jumlah'] ?? 1);
        $inCart       = $detail ? (int) $detail->jumlah : 0;

        if ($qtyRequested + $inCart > (int) $produk->stok) {
            $maks = max(0, (int) $produk->stok - $inCart);
            return back()->with('error', 'Jumlah melebihi stok. Maksimal yang bisa ditambahkan: '.$maks);
        }

        if ($detail) {
            $detail->jumlah   += $qtyRequested;
            $detail->subtotal  = $detail->jumlah * $produk->harga;
            $detail->save();
        } else {
            DetailPesanan::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'id_produk'  => $produk->id_produk,
                'jumlah'     => $qtyRequested,
                'ukuran'     => $ukuran,   // bisa null
                'warna'      => $warna,    // bisa null
                'subtotal'   => $qtyRequested * $produk->harga,
            ]);
        }

        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function update(Request $request, $id_detail)
    {
        $detail = DetailPesanan::findOrFail($id_detail);
        $produk = $detail->produk;
        $newQty = (int) $request->jumlah;
        if ($newQty < 1) $newQty = 1;
        if ($newQty > (int) $produk->stok) {
            return back()->with('error', 'Jumlah melebihi stok yang tersedia.');
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

            $response = Http::withHeaders(['key' => config('rajaongkir.api_key')])
                ->get(rtrim(config('rajaongkir.base_url'), '/').'/destination/province');

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

            $cities = collect(); // biarkan kosong, diisi via AJAX setelah pilih provinsi

            $couriers = [
                ['code' => 'jne',     'name' => 'JNE'],
                ['code' => 'sicepat', 'name' => 'SiCepat'],
                ['code' => 'jnt',     'name' => 'JNT'],
            ];

            $originCityId = config('rajaongkir.origin');

            return view('checkoutform', compact('pesanan', 'provinces', 'cities', 'originCityId', 'couriers'));
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
        // Validasi input dari form (total_bayar tidak dipakai; kita hitung sendiri)
        $data = $request->validate([
            'alamat'      => 'required|string|max:255',
            'provinsi'    => 'required',
            'kota'        => 'required',
            'district_id' => 'required',
            'kurir'       => 'required|string',
            'weight'      => 'required|integer|min:1',
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

        // Verifikasi ongkir ke API; jika gagal, pakai nilai dari client
        try {
            $resp     = $this->callOngkirApi([
                'origin'      => (string) config('rajaongkir.origin'),
                'destination' => (string) $data['district_id'],
                'weight'      => (int)    $data['weight'],
                'courier'     => (string) $data['kurir'],
                'price'       => 'lowest',
            ]);
            $rows       = $this->normalizeOngkirResponse($resp);
            $selected   = $this->pickService($rows, $data['service']);
            $ongkir     = (int) $selected['cost'];
            $service    = (string) $selected['service'];
            $serviceDesc= (string) $selected['desc'];
            $etd        = (string) $selected['etd'];
        } catch (\Throwable $e) {
            Log::warning('[Ongkir] fallback to client value: '.$e->getMessage());
            $ongkir     = (int) $data['ongkir'];
            $service    = (string) $data['service'];
            $serviceDesc= '';
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
        $pesanan->weight       = (int) $data['weight'];
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
        $midtransOrderId = 'ORD-'.$pesanan->id_pesanan.'-'.Str::upper(Str::random(6));
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
            Log::error('[Midtrans] '.$e->getMessage(), ['params' => $params]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyiapkan pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }

    /** ================== Helpers ================== */

    private function validateCheckout(Request $request): array
    {
        // Tidak dipakai lagi, tapi disediakan kalau ingin dipanggil
        return $request->validate([
            'alamat'      => 'required|string|max:255',
            'provinsi'    => 'required',
            'kota'        => 'required',
            'district_id' => 'required',
            'kurir'       => 'required|string',
            'weight'      => 'required|integer|min:1',
            'service'     => 'required|string',
            'ongkir'      => 'required|integer|min:0',
        ]);
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
        return $rows[0];
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
}
