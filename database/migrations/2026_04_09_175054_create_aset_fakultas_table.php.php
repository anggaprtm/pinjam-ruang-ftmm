<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset_fakultas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->year('tahun_aset')->nullable();
            $table->string('nama_barang');
            $table->enum('kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->default('Baik');
            $table->string('merk')->nullable();
            $table->text('deskripsi')->nullable();
            // Relasi ke tabel ruangan yang sudah ada
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangan')->nullOnDelete();
            // Kolom lokasi teks (dari Excel, bisa di-mapping manual ke ruangan_id)
            $table->string('lokasi_text')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_fakultas');
    }
};
