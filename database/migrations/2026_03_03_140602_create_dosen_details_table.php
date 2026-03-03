<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dosen_details', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Data Pribadi & Identitas
            $table->string('nama_lengkap_gelar')->nullable(); // Nama + Gelar Akademik
            $table->string('nik', 16)->unique()->nullable();
            $table->string('nuptk')->nullable();
            $table->string('nidn')->unique()->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_ponsel')->nullable();
            $table->string('npwp')->nullable();
            
            // Data Kepegawaian & Akademik
            $table->string('status_kepegawaian')->nullable(); // cth: PNS, CPNS, Tetap Non-PNS
            $table->string('status_keaktifan')->default('Aktif'); // cth: Aktif, Tugas Belajar, Izin, Pensiun
            $table->string('homebase_prodi')->nullable(); 
            $table->string('pangkat_golongan')->nullable(); // cth: III/b, III/c, IV/a
            $table->date('tgl_mulai_dosen')->nullable();
            $table->string('jabatan_fungsional')->nullable(); // cth: Asisten Ahli, Lektor, Lektor Kepala, Guru Besar

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dosen_details');
    }
};