<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {    
    $table->bigIncrements('id_produk');
    $table->string('nama_produk');
    $table->text('deskripsi');
    $table->integer('harga');
    $table->string('gambar_produk');
    $table->string('warna');
    $table->string('ukuran_tersedia');
    $table->unsignedBigInteger('kategori_id'); 
    $table->foreign('kategori_id')
          ->references('id')
          ->on('kategoris')
          ->onDelete('cascade');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
