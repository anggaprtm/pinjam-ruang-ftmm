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
            // 1. Jenis Kegiatan (Wajib, kita kasih default 'Lainnya' untuk data lama)
            $table->string('jenis_kegiatan')->default('Lainnya')->after('nama_kegiatan');
            
            // 2. Poster (Opsional / Nullable)
            $table->string('poster')->nullable()->after('surat_izin');
        });
    }

    public function down()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn(['jenis_kegiatan', 'poster']);
        });
    }
};
