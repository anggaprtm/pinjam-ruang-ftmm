<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_tugas', function (Blueprint $table) {
            $table->id();

            // Header surat
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->string('hal_surat')->default('Surat Tugas');

            // Isi penugasan
            $table->text('dasar_surat')->nullable();       // Dasar penugasan (opsional)
            $table->text('isi_tugas');                     // Deskripsi tugas / keperluan
            $table->string('hari_tanggal_tugas');          // Format string: "Senin s.d. Rabu, 5–7 Mei 2025"
            $table->string('tanggal_tugas_raw')->nullable(); // Untuk re-edit: tanggal mulai
            $table->string('tanggal_tugas_akhir_raw')->nullable(); // Untuk re-edit: tanggal selesai
            $table->string('tempat_tugas');

            // Pegawai yang ditugaskan (disimpan sebagai JSON array of objects)
            $table->text('pegawai_list');                  // JSON: [{nama, nip_nik, jabatan, golongan?}]

            // Penandatangan
            $table->string('jabatan_penandatangan');
            $table->string('nama_penandatangan');
            $table->string('nip_penandatangan');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_tugas');
    }
};