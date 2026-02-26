<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('periode_jam_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode'); // cth: "Reguler", "Ramadhan 2026"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->time('jam_masuk');
            $table->time('jam_pulang_senin_kamis');
            $table->time('jam_pulang_jumat');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('periode_jam_kerjas');
    }
};