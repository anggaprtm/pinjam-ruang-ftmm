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
        Schema::create('absensi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal'); // 2026-02-02
            $table->string('jam_masuk')->nullable(); // 07:30
            $table->string('jam_keluar')->nullable(); // 16:00
            $table->string('status')->default('alpha'); // alpha, hadir, terlambat
            $table->string('keterangan')->nullable(); // Catatan tambahan
            $table->timestamps();
            
            // Agar 1 user cuma punya 1 log per hari
            $table->unique(['user_id', 'tanggal']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_logs');
    }
};
