<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id('id_pesanan');

            // Data pengiriman
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kota_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('kurir', 40)->nullable();
            $table->string('service_code', 60)->nullable();
            $table->string('service_desc')->nullable();
            $table->string('etd', 60)->nullable();
            $table->integer('weight')->nullable();
            $table->integer('ongkir')->default(0);

            $table->decimal('total_harga', 12, 2);
            $table->enum('status', ['pending', 'failed', 'success'])->default('pending');
            $table->timestamps();

            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')
                ->references('id_user')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
