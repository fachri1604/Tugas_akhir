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
        Schema::create('stoks', function (Blueprint $table) {
        $table->id('id_log');
        $table->unsignedBigInteger('id_produk'); 
        $table->foreign('id_produk')->references('id_produk')->on('produks')->onDelete('cascade');
        $table->unsignedBigInteger('id_user');
        $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        $table->enum('tipe', ['tambah', 'kurang']);
        $table->integer('jumlah');
        $table->text('catatan');
        $table->integer('total');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoks');
    }
};
