<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalPerkuliahanTable extends Migration {
    public function up(): void
    {
        Schema::create('jadwal_perkuliahan', function (Blueprint $table) {
            $table->id();
            $table->string('mata_kuliah');
            $table->unsignedBigInteger('ruangan_id');
            $table->string('hari'); // Senin, Selasa, dst
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->date('berlaku_mulai'); // tanggal awal semester
            $table->date('berlaku_sampai'); // tanggal akhir semester
            $table->string('dosen')->nullable(); // opsional
            $table->timestamps();

            $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_perkuliahan');
    }
};
