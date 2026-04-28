<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jadwal_wfh', function (Blueprint $table) {
            $table->id();
            // user_id null = berlaku untuk semua pegawai (massal)
            // user_id isi = berlaku khusus pegawai tersebut
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // tanggal isi = WFH insidental (misal tanggal 20)
            $table->date('tanggal')->nullable();
            
            // hari_rutin isi (1-5) = WFH rutin (misal 3 = Tiap Rabu)
            $table->integer('hari_rutin')->nullable(); 
            
            $table->string('keterangan')->nullable(); // Misal: "WFH Rutin Rabu"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_wfh');
    }
};