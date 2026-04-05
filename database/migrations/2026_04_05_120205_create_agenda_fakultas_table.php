<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_fakultas', function (Blueprint $table) {
            $table->id();

            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('kategori')->default('Akademik'); // Akademik, Wisuda, Kemahasiswaan, dll
            $table->string('warna')->default('#2dd4bf');     // Accent color untuk UI
            
            // Tanggal & Waktu
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();     // null = single day event
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->boolean('is_all_day')->default(true);
            
            // Tampilan di signage
            $table->boolean('tampil_di_signage')->default(true);
            $table->boolean('tampil_countdown')->default(false); // Apakah tampil sebagai countdown
            $table->integer('urutan')->default(0);              // Untuk sorting manual
            
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_fakultas');
    }
};