<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Produk extends Model
{
    use HasFactory;

    // Nama tabel jika tidak sesuai default (jamak)
    protected $table = 'produks';

    // Primary key bukan 'id'
    protected $primaryKey = 'id_produk'; // Kolom primary key
    public $incrementing = true; // Pastikan true (default)
    protected $keyType = 'int';

    // Kolom yang bisa diisi (fillable)
    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'harga',
        'gambar_produk',
        'warna',
        'alamat',
        'stok',
        'ukuran_tersedia',
        'kategori_id',
    ];

public function kategori()
{
    return $this->belongsTo(Kategori::class, 'kategori_id', 'id');
}


    

}
