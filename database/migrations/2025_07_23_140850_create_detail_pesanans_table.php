<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanans', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_produk');
            $table->decimal('harga_satuan', 12, 2);
            $table->integer('jumlah');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
            $table->decimal('harga_satuan', 12, 2);

            // Asumsikan tabel 'pesanans' dan 'produks' sudah ada
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanans')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produks')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('detail_pesanans', function (Blueprint $table) {
            // Drop foreign keys menggunakan array kolom
            $table->dropForeign(['id_pesanan']);
            $table->dropForeign(['id_produk']);
        });

        Schema::dropIfExists('detail_pesanans');
    }
};
