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
        Schema::create('riwayat_bbms', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal');
            $table->integer('km_odometer');
            $table->integer('biaya')->nullable(); // Opsional untuk nambahin harga
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_bbms');
    }
};
