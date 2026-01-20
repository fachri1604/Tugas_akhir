<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stoks', function (Blueprint $table) {
            $table->id('id_stok');

            // Pastikan foreign key cocok dengan nama kolom primary di tabel produks
            $table->unsignedBigInteger('produk_id');
            $table->foreign('produk_id')->references('id_produk')->on('produks')->onDelete('cascade');

            $table->integer('jumlah'); // bisa negatif untuk pengurangan stok
            $table->string('catatan')->nullable();
            $table->enum('tipe', ['masuk', 'keluar'])->default('masuk'); // masuk = restock, keluar = pengurangan stok
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stoks');
    }
};
