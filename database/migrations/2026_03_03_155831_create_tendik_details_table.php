<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tendik_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Identitas Pribadi
            $table->string('nama_lengkap')->nullable();
            $table->string('nik_ktp', 16)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_ponsel')->nullable();
            $table->string('npwp')->nullable();
            
            // Data Kepegawaian
            $table->string('nik')->nullable(); // NIK Pegawai / Universitas
            $table->date('tmt')->nullable(); // Terhitung Mulai Tanggal
            $table->string('pangkat_golongan')->nullable();
            $table->string('nama_jabatan')->nullable();
            $table->string('sub_bagian')->nullable();
            $table->string('status_kepegawaian')->nullable(); // PNS, PTT, BLU, dll
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tendik_details');
    }
};