<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id('id_pesanan'); // Primary key auto-increment

            // Kolom foreign key            

            // Kolom lainnya
            $table->decimal('total_harga', 12, 2);
            $table->enum('status', ['pending', 'diproses', 'selesai', 'dibatalkan'])->default('pending');
            $table->timestamps();

            // Foreign key ke users
             $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')
                ->references('id_user')->on('users') // Merujuk ke id_user bukan id
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
