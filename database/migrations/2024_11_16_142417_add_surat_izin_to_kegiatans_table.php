<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuratIzinToKegiatansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->string('surat_izin')->nullable()->after('status'); // Kolom untuk menyimpan nama file
        });
    }

    public function down()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn('surat_izin');
        });
    }
};
