<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Ubah tabel utama
        Schema::table('jadwal_wfh', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // Hapus relasi lama
            $table->dropColumn('user_id');    // Hapus kolom
            $table->boolean('is_global')->default(false)->after('keterangan'); // Penanda massal/semua
        });

        // 2. Buat tabel pivot
        Schema::create('jadwal_wfh_user', function (Blueprint $table) {
            $table->foreignId('jadwal_wfh_id')->constrained('jadwal_wfh')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_wfh_user');
        Schema::table('jadwal_wfh', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->dropColumn('is_global');
        });
    }
};