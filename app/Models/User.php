<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{    
    use Notifiable;

    protected $primaryKey = 'id_user';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'alamat',       
        'provinsi_id',  
        'kota_id',      
        'kode_pos', 
        'phone',  
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin()
    {
        return strtolower($this->role) === 'admin';
    }

    /**
     * Cek apakah user adalah user biasa
     */
    public function isUser()
    {
        return strtolower($this->role) === 'user';
    }

    /**
     * Relasi ke tabel Pesanan
     */
    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_user', 'id_user');
    }

    /**
     * Relasi ke DetailPesanan melalui Pesanan
     */
    public function cartItems()
    {
        // Relasi manyThrough: User -> Pesanan -> DetailPesanan
        return $this->hasManyThrough(
            DetailPesanan::class,
            Pesanan::class,
            'id_user',      // Foreign key di tabel pesanan
            'id_pesanan',   // Foreign key di tabel detail_pesanan
            'id_user',      // Local key di tabel users
            'id_pesanan'    // Local key di tabel pesanan
        )->whereHas('pesanan', function ($query) {
            $query->where('status', 'pending');
        });
    }
}
