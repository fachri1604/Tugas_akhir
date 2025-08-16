<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class RajaOngkirController extends Controller
{
    public function getCities($provinceId)
    {
        $res = Http::withHeaders([
            'key'    => config('rajaongkir.api_key'),
            'Accept' => 'application/json',
        ])->get(rtrim(config('rajaongkir.base_url'), '/')."/destination/city/{$provinceId}");

        if (!$res->successful()) {
            return response()->json(['success' => false, 'cities' => []]);
        }

        $rows  = $res->json()['data'] ?? [];
        $items = collect($rows)->map(fn($r) => ['id' => $r['id'], 'name' => $r['name']])->values();

        return response()->json(['success' => true, 'cities' => $items]);
    }

    public function getDistricts($cityId)
    {
        $res = Http::withHeaders([
            'key'    => config('rajaongkir.api_key'),
            'Accept' => 'application/json',
        ])->get(rtrim(config('rajaongkir.base_url'), '/')."/destination/district/{$cityId}");

        if (!$res->successful()) {
            return response()->json(['success' => false, 'districts' => []]);
        }

        $rows  = $res->json()['data'] ?? [];
        $items = collect($rows)->map(fn($r) => ['id' => $r['id'], 'name' => $r['name']])->values();

        return response()->json(['success' => true, 'districts' => $items]);
    }

   // app/Http/Controllers/RajaOngkirController.php

public function getCost(Request $request)
{
    $request->validate([
        'destination' => ['required'],                 // ID kecamatan tujuan
        'weight'      => ['required','numeric','min:1'],
        // boleh single 'jne' atau multi 'jne:sicepat:jnt' — lihat catatan di bawah
        'courier'     => ['nullable','string'],
    ]);

    // kalau kamu ingin “seperti website Komerce” (cari semua kurir),
    // kirim semua kurir yang kamu izinkan ketika user tidak memilih apa pun:
    $courier = trim($request->courier ?? '');
    if ($courier === '' || $courier === 'all') {
        $courier = implode(':', config('rajaongkir.allowed_couriers')); // ex: 'jne:sicepat:jnt'
        // kalau mau full seperti demo Komerce:
        // $courier = 'jne:sicepat:ide:sap:jnt:ninja:tiki:lion:anteraja:pos:ncs:rex:rpx:sentral:star:wahana:dse';
    }

    $payload = [
        'origin'      => (string) config('rajaongkir.origin'),  // ID kecamatan asal
        'destination' => (string) $request->destination,         // ID kecamatan tujuan
        'weight'      => (int)    $request->weight,              // gram
        'courier'     => $courier,
        'price'       => 'lowest',
    ];

    $res = Http::asForm()
        ->withHeaders(['key' => config('rajaongkir.api_key')])
        ->post(rtrim(config('rajaongkir.base_url'), '/').'/calculate/district/domestic-cost', $payload);

    $json = $res->json();

    // ==== NORMALISASI BENTUK RESP ====
    $rows = [];

    // Bentuk 1: data.costs (nested)
    if (isset($json['data']['costs']) && is_array($json['data']['costs'])) {
        foreach ($json['data']['costs'] as $c) {
            $first = $c['cost'][0] ?? ['value'=>0,'etd'=>''];
            $rows[] = [
                'courier'     => strtoupper($c['courier'] ?? ''),     // mis. JNE
                'service'     => $c['service'] ?? '',                 // mis. REG
                'description' => $c['description'] ?? '',             // mis. Regular Service
                'cost'        => (int)($first['value'] ?? 0),
                'etd'         => (string)($first['etd'] ?? ''),
            ];
        }
    }
    // Bentuk 2: data (array langsung) → contoh yang kamu tunjukkan
    elseif (isset($json['meta']['status']) && $json['meta']['status'] === 'success'
        && isset($json['data']) && is_array($json['data'])) {
        foreach ($json['data'] as $c) {
            $rows[] = [
                'courier'     => strtoupper($c['code'] ?? $c['name'] ?? ''), // JNE/JNT/LION/…
                'service'     => $c['service'] ?? '',
                'description' => $c['description'] ?? '',
                'cost'        => (int)($c['cost'] ?? 0),
                'etd'         => (string)($c['etd'] ?? ''),
            ];
        }
    }

    if (empty($rows)) {
        return response()->json([
            'meta' => ['status' => 'error', 'message' => 'Layanan tidak tersedia'],
            'data' => []
        ], 200);
    }

    return response()->json([
        'meta' => ['status' => 'success'],
        'data' => $rows
    ]);
}

}
