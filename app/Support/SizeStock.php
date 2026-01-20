<?php

namespace App\Support;

class SizeStock
{
    // Ubah string jadi array: "S=2,M=0,L=5" -> ['S'=>['label'=>'S','stock'=>2], ...]
    public static function parse(?string $string): array
    {
        $result = [];
        if (!$string) return $result;

        $pairs = explode(',', $string);
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if ($pair === '') continue;

            if (strpos($pair, '=') !== false) {
                [$label, $stok] = explode('=', $pair, 2);
                $result[$label] = [
                    'label' => trim($label),
                    'stock' => (int) trim($stok),
                ];
            } else {
                $result[$pair] = [
                    'label' => trim($pair),
                    'stock' => null, // artinya stok global
                ];
            }
        }
        return $result;
    }

    // Ubah array balik ke string "S=2,M=0,L=5"
    public static function build(array $rows): string
    {
        $out = [];
        foreach ($rows as $r) {
            if (!empty($r['label'])) {
                $stok = isset($r['stock']) && $r['stock'] !== '' ? (int) $r['stock'] : '';
                $out[] = $r['label'] . ($stok !== '' ? '=' . $stok : '');
            }
        }
        return implode(',', $out);
    }
}
