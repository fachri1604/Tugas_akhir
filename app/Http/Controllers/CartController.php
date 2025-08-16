<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Validation\Rule;


class CartController extends Controller
{
    public function index()
    {
        $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('id_user', Auth::user()->id_user)
            ->where('status', 'pending')
            ->first();

        return view('cart', compact('pesanan'));
    }

    public function checkout()
    {
        $pesanan = Pesanan::with('detailPesanans.produk')
            ->where('id_user', Auth::user()->id_user)
            ->where('status', 'pending')
            ->firstOrFail();

        foreach ($pesanan->detailPesanans as $detail) {
            if ($detail->produk->stok < $detail->jumlah) {
                return redirect()->route('cart.index')->with(
                    'error',
                    "Stok produk '{$detail->produk->nama_produk}' tidak cukup."
                );
            }
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id'     => $pesanan->id_pesanan . '-' . time(),
                'gross_amount' => $pesanan->total_harga,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'phone'      => Auth::user()->no_hp,
                'address'    => Auth::user()->alamat,
            ],
            'item_details' => $pesanan->detailPesanans->map(function ($item) {
                return [
                    'id' => $item->id_produk,
                    'price' => $item->produk->harga,
                    'quantity' => $item->jumlah,
                    'name' => $item->produk->nama_produk,
                ];
            })->toArray()
        ];

        $snapToken = Snap::getSnapToken($params);

        return view('payment', compact('pesanan', 'snapToken'));
    }



 public function add(Request $request, $id_produk)
{
    $produk = Produk::findOrFail($id_produk);

    // Allowed sizes (dari kolom produk.ukuran_tersedia, CSV)
    $allowedSizes = [];
    if (!empty($produk->ukuran_tersedia)) {
        $allowedSizes = array_values(array_filter(array_map(function ($v) {
            return strtoupper(trim($v));
        }, explode(',', $produk->ukuran_tersedia))));
    }

    // Allowed colors (dari kolom produk.warna, CSV)
    $allowedColors = [];
    if (!empty($produk->warna)) {
        $allowedColors = array_values(array_filter(array_map(function ($v) {
            return trim($v);
        }, explode(',', $produk->warna))));
    }

    // Validasi
    $rules = [
        'jumlah' => 'required|integer|min:1',
    ];
    // ukuran: required kalau ada daftar ukuran
    if (count($allowedSizes) > 0) {
        $rules['ukuran'] = ['required', Rule::in($allowedSizes)];
    } else {
        $rules['ukuran'] = ['nullable', 'string', 'max:10'];
    }
    // warna: required kalau ada daftar warna
    if (count($allowedColors) > 0) {
        $rules['warna'] = ['required', Rule::in($allowedColors)];
    } else {
        $rules['warna'] = ['nullable', 'string', 'max:30'];
    }
    $request->validate($rules);

    // Normalisasi
    $ukuran = strtoupper(trim((string) $request->input('ukuran', '')));
    $ukuran = $ukuran !== '' ? $ukuran : null;

    $warna = trim((string) $request->input('warna', ''));
    $warna = $warna !== '' ? $warna : null;

    // Ambil/buat pesanan pending
    $pesanan = Pesanan::firstOrCreate(
        ['id_user' => Auth::user()->id_user, 'status' => 'pending'],
        ['total_harga' => 0]
    );

    // Cari detail berdasarkan produk + ukuran + warna
    $detailQuery = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)
        ->where('id_produk', $produk->id_produk);

    is_null($ukuran) ? $detailQuery->whereNull('ukuran') : $detailQuery->where('ukuran', $ukuran);
    is_null($warna)  ? $detailQuery->whereNull('warna')  : $detailQuery->where('warna',  $warna);

    $detail     = $detailQuery->first();
    $jumlahBaru = (int) $request->input('jumlah', 1);

    if ($detail) {
        $detail->jumlah   += $jumlahBaru;
        $detail->subtotal  = $detail->jumlah * $produk->harga;
        $detail->save();
    } else {
        DetailPesanan::create([
            'id_pesanan' => $pesanan->id_pesanan,
            'id_produk'  => $produk->id_produk,
            'jumlah'     => $jumlahBaru,
            'ukuran'     => $ukuran,
            'warna'      => $warna,   // <— SIMPAN WARNA
            'subtotal'   => $jumlahBaru * $produk->harga,
        ]);
    }

    // Rehitung total
    $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
    $pesanan->save();

    return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang');
}



    public function update(Request $request, $id_detail)
    {
        $detail = DetailPesanan::findOrFail($id_detail);
        $detail->jumlah = $request->jumlah;
        $detail->subtotal = $detail->jumlah * $detail->produk->harga;
        $detail->save();

        $pesanan = $detail->pesanan;
        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui');
    }

    public function remove($id_detail)
    {
        $detail = DetailPesanan::findOrFail($id_detail);
        $pesanan = $detail->pesanan;
        $detail->delete();

        $pesanan->total_harga = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)->sum('subtotal');
        $pesanan->save();

        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang');
    }

    // Di dalam CartController

    public function checkoutForm($id_pesanan)
    {
        try {
            $user = Auth::user();
            $pesanan = Pesanan::where('id_pesanan', $id_pesanan)
                ->where('id_user', $user->id_user)
                ->firstOrFail();



            // Ambil list provinsi
            $response = Http::withHeaders([
                'key' => config('rajaongkir.api_key')
            ])->get(config('rajaongkir.base_url') . '/destination/province');

            if (!$response->successful()) {
                throw new \Exception("API request failed with status: " . $response->status());
            }

            $responseData = $response->json();

            if (!isset($responseData['data']) || !is_array($responseData['data'])) {
                throw new \Exception("Invalid API response format");
            }

            $provinces = collect($responseData['data'])->map(function ($item) {
                return [
                    'id'   => $item['id'],
                    'name' => $item['name'],
                ];
            });

            $firstProvinceId = $provinces->first()['id'] ?? null;
            $cities = collect();

            if ($firstProvinceId) {
                $cityResponse = Http::withHeaders([
                    'key' => config('rajaongkir.api_key')
                ])->get(config('rajaongkir.base_url') . "/destination/city/{$firstProvinceId}");

                if ($cityResponse->successful()) {
                    $cityData = $cityResponse->json();
                    if (isset($cityData['data']) && is_array($cityData['data'])) {
                        $cities = collect($cityData['data'])->map(function ($item) {
                            return [
                                'id' => $item['id'],
                                'name' => $item['name'],
                            ];
                        });
                    }
                }
            }

            $couriers = [
                ['code' => 'jne', 'name' => 'JNE'],
                ['code' => 'sicepat', 'name' => 'SiCepat'],
                ['code' => 'jnt', 'name' => 'JNT'],
            ];

            $originCityId = config('rajaongkir.origin');

            return view('checkoutform', compact('pesanan', 'provinces', 'cities', 'originCityId', 'couriers'));
        } catch (\Exception $e) {
            Log::error('CheckoutForm Error: ' . $e->getMessage());

            return view('checkoutform', [
                'pesanan' => null,
                'provinces' => collect([]),
                'cities' => collect([]),
                'originCityId' => config('rajaongkir.origin'),
                'couriers' => [],
                'api_error' => 'Gagal memuat data provinsi dari API.'
            ]);
        }
    }
    
   /**
 * Proses checkout (dipersingkat untuk IntelliSense).
 * - Validasi input
 * - Ambil & pastikan pesanan milik user
 * - Hitung ongkir via API (district-level)
 * - Simpan ke tabel pesanan
 * - Buat Snap Token Midtrans dan redirect
 */
public function checkoutProcess(Request $request, $id_pesanan)
{
    // 1) Validasi
    $data = $this->validateCheckout($request);

    // 2) Ambil pesanan milik user
    $pesanan = Pesanan::with('detailPesanans.produk')
        ->where('id_pesanan', $id_pesanan)
        ->where('id_user', Auth::user()->id_user)
        ->firstOrFail();

    // 3) Simpan alamat teks saja ke profil user
    $user = Auth::user();
    $user->alamat = $data['alamat'];
    $user->save();

    // 4) Hitung ulang subtotal dari DB
    $subtotalProduk = $this->calculateSubtotal($pesanan);

    // 5) Hitung ongkir via API
    $payloadOngkir = [
        'origin'      => (string) config('rajaongkir.origin'),
        'destination' => (string) $data['district_id'],
        'weight'      => (int)    $data['weight'],   // gram
        'courier'     => (string) $data['kurir'],    // 'jne' dll
        'price'       => 'lowest',
    ];

    try {
        $resp   = $this->callOngkirApi($payloadOngkir);
        $rows   = $this->normalizeOngkirResponse($resp);
    } catch (\Throwable $e) {
        Log::error('[Ongkir] '.$e->getMessage(), ['payload' => $payloadOngkir]);

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Gagal menghitung ongkir.'], 500);
        }
        return back()->with('error', 'Gagal menghitung ongkir.')->withInput();
    }

    if (empty($rows)) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan ongkir tidak tersedia untuk rute/berat ini.'
            ], 422);
        }
        return back()->with('error', 'Layanan ongkir tidak tersedia untuk rute/berat ini.')->withInput();
    }

    // 6) Pilih service (pakai yang dipilih user kalau ada, else termurah)
    $selected = $this->pickService($rows, $data['service'] ?? null);
    $ongkir   = (int) $selected['cost'];
    $total    = (int) ($subtotalProduk + $ongkir);

    // 7) Simpan data kirim & total ke pesanan
    $pesanan->provinsi_id  = $data['provinsi'];
    $pesanan->kota_id      = $data['kota'];
    $pesanan->district_id  = $data['district_id'];
    $pesanan->kurir        = strtolower($data['kurir']);
    $pesanan->service_code = $selected['service'];
    $pesanan->service_desc = $selected['desc'];
    $pesanan->etd          = $selected['etd'];
    $pesanan->weight       = (int) $data['weight'];
    $pesanan->ongkir       = $ongkir;
    $pesanan->total_harga  = $total;
    $pesanan->status       = 'pending';
    $pesanan->save();

    // 8) Midtrans config
    Config::$serverKey    = (string) config('midtrans.server_key');
    Config::$isProduction = (bool)   config('midtrans.is_production');
    Config::$isSanitized  = true;
    Config::$is3ds        = true;

    // item_details (produk + ongkir)
    $itemDetails = $pesanan->detailPesanans->map(function ($d) {
        return [
            'id'       => (string) $d->id_produk,
            'price'    => (int)    $d->produk->harga,
            'quantity' => (int)    $d->jumlah,
            'name'     => (string) $d->produk->nama_produk,
        ];
    })->toArray();

    $itemDetails[] = [
        'id'       => 'SHIPPING',
        'price'    => (int) $ongkir,
        'quantity' => 1,
        'name'     => 'Ongkos Kirim',
    ];

    $params = $this->buildMidtransParams($pesanan, $user, $itemDetails);

    try {
        $snapToken = Snap::getSnapToken($params);
    } catch (\Throwable $e) {
        Log::error('[Midtrans] '.$e->getMessage(), ['params' => $params]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyiapkan pembayaran: '.$e->getMessage(),
            ], 500);
        }
        return back()->with('error', 'Gagal menyiapkan pembayaran.')->withInput();
    }

    // 9) Balasan untuk AJAX (dipakai Blade kamu)
    if ($request->expectsJson()) {
        return response()->json([
            'success'    => true,
            'snap_token' => $snapToken,
            'order_id'   => (string) $pesanan->id_pesanan,
        ]);
    }

    // Fallback flow lama (kalau bukan AJAX)
    return redirect()
        ->route('payment.show', $pesanan->id_pesanan)
        ->with('snapToken', $snapToken);
}

/** ================== Helper Functions (tetap) ================== */

private function validateCheckout(Request $request): array
{
    return $request->validate([
        'alamat'      => 'required|string|max:255',
        'provinsi'    => 'required',
        'kota'        => 'required',
        'district_id' => 'required',
        'kurir'       => 'required|string',
        'weight'      => 'required|integer|min:1',
        'service'     => 'nullable|string', // KODE LAYANAN (bukan biaya!)
    ]);
}

private function calculateSubtotal(Pesanan $pesanan): int
{
    return (int) $pesanan->detailPesanans->sum('subtotal');
}

private function callOngkirApi(array $payload): array
{
    $api = rtrim((string) config('rajaongkir.base_url'), '/') . '/calculate/district/domestic-cost';

    $resp = Http::asForm()
        ->withHeaders(['key' => (string) config('rajaongkir.api_key')])
        ->post($api, $payload);

    if (!$resp->ok()) {
        throw new \RuntimeException('HTTP '.$resp->status().' dari layanan ongkir.');
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

    if (
        ($resp['meta']['status'] ?? null) === 'success' &&
        isset($resp['data']) && is_array($resp['data'])
    ) {
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
            if (strcasecmp($r['service'], $requestedService) === 0) {
                return $r;
            }
        }
    }
    usort($rows, fn($a, $b) => $a['cost'] <=> $b['cost']);
    return $rows[0];
}

private function buildMidtransParams(Pesanan $pesanan, $user, array $itemDetails): array
{
    return [
        'transaction_details' => [
            'order_id'     => (string) $pesanan->id_pesanan,   // pastikan unik
            'gross_amount' => (int)    $pesanan->total_harga,  // integer >= 1
        ],
        'customer_details' => [
            'first_name' => (string) $user->name,
            'email'      => (string) $user->email,
            'phone'      => (string) $user->no_hp,
            'billing_address' => [
                'first_name' => (string) $user->name,
                'phone'      => (string) $user->no_hp,
                'address'    => (string) ($user->alamat ?? ''),
            ],
            'shipping_address' => [
                'first_name' => (string) $user->name,
                'phone'      => (string) $user->no_hp,
                'address'    => (string) ($user->alamat ?? ''),
            ],
        ],
        'item_details' => $itemDetails,
    ];
}

}
