<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembur_kegiatan_pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembur_kegiatan_id')->constrained('lembur_kegiatan')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('peran')->nullable()->comment('Peran pegawai, misal: Koordinator, Anggota, Petugas');

            // Status validasi dihitung otomatis saat SyncAttendance berjalan:
            // 'menunggu' → belum ada data presensi hari itu
            // 'valid'    → presensi ≥ 4 jam
            // 'tidak_valid' → presensi ada tapi < 4 jam, atau tidak ada sama sekali setelah hari H
            $table->enum('status_validasi', ['menunggu', 'valid', 'tidak_valid'])->default('menunggu');

            $table->timestamps();

            // Satu pegawai hanya bisa ada di 1 kegiatan per entri tabel ini,
            // tapi constraint "1 orang 1 kegiatan per hari" di-handle di level Controller.
            $table->unique(['lembur_kegiatan_id', 'user_id']); // Tidak boleh assign 2x di kegiatan sama
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembur_kegiatan_pegawai');
    }
};