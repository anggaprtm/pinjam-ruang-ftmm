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
            // Taruh setelah status atau kolom lain yang relevan
            $table->timestamp('verifikasi_kemahasiswaan_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn('verifikasi_kemahasiswaan_at');
        });
    }
    
};
