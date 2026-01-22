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
        Schema::create('permintaan_kegiatans', function (Blueprint $table) {
            $table->id();
            
            // User Pemohon (yang login)
            $table->foreignId('user_id')->constrained('users');
            
            // User Penanggung Jawab (Dropdown Pegawai)
            $table->foreignId('pic_user_id')->constrained('users');
            
            // Data Kegiatan
            $table->string('nama_kegiatan');
            $table->string('jenis_kegiatan'); // Rapat, Seminar, dll
            
            // Kita pisah Date & Time agar sesuai input form user, nanti digabung pas create Kegiatan
            $table->date('tanggal_kegiatan');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            
            $table->integer('jumlah_peserta')->nullable();
            $table->text('deskripsi')->nullable(); // Catatan untuk pemroses / Deskripsi kegiatan
            
            // --- FITUR KONSUMSI ---
            $table->boolean('request_konsumsi')->default(false);
            $table->time('waktu_konsumsi')->nullable(); // Jam makan/snack datang
            $table->text('catatan_konsumsi')->nullable(); // Menu, box/prasmanan
            
            // --- FITUR RUANG ---
            $table->boolean('request_ruang')->default(false);
            
            // File
            $table->string('lampiran')->nullable(); // Path file Undangan/Nota Dinas
            
            // --- STATUS FLOW ---
            // Status Utama
            $table->enum('status_permintaan', ['pending', 'proses', 'selesai', 'ditolak'])->default('pending');
            
            // Sub-Status Ruang
            $table->enum('status_ruang', ['pending', 'selesai', 'tidak_perlu'])->default('tidak_perlu');
            
            // Sub-Status Konsumsi
            $table->enum('status_konsumsi', ['pending', 'diproses', 'selesai', 'tidak_perlu'])->default('tidak_perlu');
            
            // Relasi ke Kegiatan Resmi (Jika ruang sudah diplot)
            $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatan')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_kegiatans');
    }
};
