<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi_logs', function (Blueprint $table) {
            // Tambahkan nullable karena data lama mungkin tidak punya batas jam
            $table->time('batas_jam_masuk')->nullable()->after('jam_keluar');
            $table->time('batas_jam_keluar')->nullable()->after('batas_jam_masuk');
        });
    }

    public function down()
    {
        Schema::table('absensi_logs', function (Blueprint $table) {
            $table->dropColumn(['batas_jam_masuk', 'batas_jam_keluar']);
        });
    }
};