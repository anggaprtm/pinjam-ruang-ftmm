<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembur_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->comment('Tanggal pelaksanaan lembur');
            $table->string('nama_kegiatan')->comment('Nama/judul kegiatan lembur');
            $table->text('deskripsi')->nullable()->comment('Deskripsi detail kegiatan');
            $table->string('file_surat_tugas')->nullable()->comment('Path file surat tugas (opsional)');
            $table->foreignId('dibuat_oleh')->constrained('users')->comment('Admin yang membuat kegiatan ini');
            $table->timestamps();

            $table->index('tanggal'); // Sering difilter by tanggal
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembur_kegiatan');
    }
};