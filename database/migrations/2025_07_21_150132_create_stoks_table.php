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
        $table->bigIncrements('id_log');
        $table->unsignedBigInteger('id_produk'); 
         $table->string('ukuran'); // per ukuran
        $table->string('warna'); 
        $table->foreign('id_produk')->references('id_produk')->on('produks')->onDelete('cascade');           
        $table->enum('tipe', ['tambah', 'kurang']);
        $table->integer('jumlah');
        $table->text('alamat');
        $table->text('catatan');            
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
