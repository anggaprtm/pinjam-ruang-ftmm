<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('mobils', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mobil'); // Contoh: Toyota Innova
            $table->string('plat_nomor')->unique(); // Contoh: L 1234 AB
            $table->string('warna')->nullable();
            // Status real-time saat ini
            $table->enum('status', ['tersedia', 'dipakai', 'maintenance'])->default('tersedia');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobils');
    }
};
