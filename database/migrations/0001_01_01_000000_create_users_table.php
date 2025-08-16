<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id_user');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->text('alamat')->nullable();
            $table->integer('provinsi_id')->nullable()->after('alamat');
            $table->integer('kota_id')->nullable()->after('provinsi_id');
            $table->string('kode_pos', 10)->nullable()->after('kota_id');
            $table->string('phone')->nullable();
            $table-> string('role',50  )->nullable();  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
