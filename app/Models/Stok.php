<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stoks'; // atau 'stok' jika sesuai di database kamu
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_produk',
        'tipe',
        'jumlah',
        'alamat',
        'catatan',
        'ukuran_tersedia',
        'warna',
    ];

    public $timestamps = false; // Hapus jika kamu pakai timestamps di table

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
