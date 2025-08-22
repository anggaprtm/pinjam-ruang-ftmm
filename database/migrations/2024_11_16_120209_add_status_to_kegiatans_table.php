<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToKegiatansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menghapus kolom 'status' yang lama jika ada
            if (Schema::hasColumn('kegiatan', 'status')) {
                $table->dropColumn('status');
            }

            // Menambahkan kolom 'status' baru dengan enum yang diperbarui
            $table->enum('status', ['belum_disetujui', 'verifikasi_akademik', 'verifikasi_sarpras', 'disetujui', 'ditolak'])
                  ->default('belum_disetujui');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menghapus kolom 'status' jika rollback migrasi
            $table->dropColumn('status');
        });
    }
}
