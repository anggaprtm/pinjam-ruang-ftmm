<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('bot_settings', 'pagi_pesan_dosen')) {
                $table->text('pagi_pesan_dosen')->nullable()->after('pagi_pesan');
            }
            $table->boolean('siang_dosen_aktif')->default(false)->after('evaluasi_pesan');
            $table->time('siang_dosen_jam')->nullable()->after('siang_dosen_aktif');
            $table->text('siang_dosen_pesan_belum')->nullable()->after('siang_dosen_jam');
            $table->text('siang_dosen_pesan_sudah')->nullable()->after('siang_dosen_pesan_belum');
        });
    }

    public function down()
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->dropColumn([
                'pagi_pesan_dosen', 
                'siang_dosen_aktif', 
                'siang_dosen_jam', 
                'siang_dosen_pesan_belum', 
                'siang_dosen_pesan_sudah'
            ]);
        });
    }
};