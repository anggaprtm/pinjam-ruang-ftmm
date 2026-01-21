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
        Schema::create('riwayat_perjalanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Driver
            $table->foreignId('mobil_id')->constrained('mobils');
            
            $table->string('tujuan');
            $table->string('keperluan')->nullable();
            
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable(); // Diisi estimasi saat booking, atau real saat finish
            
            // Status Log:
            // 'terjadwal' (Booking masa depan)
            // 'berlangsung' (Sedang jalan)
            // 'selesai' (Sudah balik)
            $table->enum('status', ['terjadwal', 'berlangsung', 'selesai'])->default('terjadwal');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_perjalanans');
    }
};
