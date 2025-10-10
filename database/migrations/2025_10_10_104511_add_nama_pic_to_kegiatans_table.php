<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menambahkan kolom 'nama_pic' setelah kolom 'nomor_telepon'
            $table->string('nama_pic')->nullable()->after('nomor_telepon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menghapus kolom 'nama_pic' jika migrasi di-rollback
            $table->dropColumn('nama_pic');
        });
    }
};
