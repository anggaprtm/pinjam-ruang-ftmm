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
        Schema::create('surat_undangans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('hal_surat')->default('Undangan');
            $table->date('tanggal_surat');
            $table->text('tujuan_surat'); // Disimpan sebagai JSON atau Text list
            
            // Detail Acara
            $table->string('hari_tanggal_acara'); // String bebas (e.g. "Rabu, 21 Mei 2025")
            $table->string('waktu_acara');        // e.g. "14.00 - 16.00 WIB"
            $table->string('tempat_acara');
            $table->string('agenda_acara');
            $table->string('dresscode')->nullable();

            // Penanda Tangan
            $table->string('jabatan_penandatangan'); // e.g. "Dekan"
            $table->string('nama_penandatangan');
            $table->string('nip_penandatangan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_undangans');
    }
};
