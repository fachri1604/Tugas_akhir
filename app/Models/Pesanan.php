<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanans'; // pastikan sama dengan nama tabel di database
    protected $primaryKey = 'id_pesanan';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_user',
        'total_harga',
        'status'
    ];

    /**
     * Relasi ke user (pembuat pesanan)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Relasi ke detail pesanan
     */
    public function detailPesanans()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * Event: hapus detail pesanan jika pesanan dihapus
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($pesanan) {
            $pesanan->detailPesanans()->delete();
        });
    }

    /**
     * Hitung total harga dari semua detail
     */
    public function hitungTotal()
    {
        $this->total_harga = $this->detailPesanans->sum('subtotal');
        $this->save();
    }
}
