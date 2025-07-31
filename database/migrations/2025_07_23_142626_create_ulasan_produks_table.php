<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan_produks', function (Blueprint $table) {
            $table->id();

            // Pastikan tipe data sama dengan tabel produks
            $table->unsignedBigInteger('id_produk');
            $table->unsignedBigInteger('id_user');

            // Kolom lainnya
            $table->text('ulasan');
            $table->unsignedTinyInteger('rating');
            $table->timestamps();

            // Tambahkan pengecekan sebelum membuat foreign key
            if (Schema::hasTable('produks')) {
                $table->foreign('id_produk')
                    ->references('id_produk')->on('produks')
                    ->onDelete('cascade');
            }

            $table->foreign('id_user')
                ->references('id_user')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan_produks');
    }
};
