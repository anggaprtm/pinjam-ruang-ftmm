<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifikasiTimestampsToKegiatansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menambahkan kolom timestamp untuk verifikasi
            $table->timestamp('verifikasi_akademik_at')->nullable()->after('status');
            $table->timestamp('verifikasi_sarpras_at')->nullable()->after('verifikasi_akademik_at');
            $table->timestamp('disetujui_at')->nullable()->after('verifikasi_sarpras_at');
            $table->timestamp('ditolak_at')->nullable()->after('disetujui_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menghapus kolom timestamp jika rollback
            $table->dropColumn('verifikasi_akademik_at');
            $table->dropColumn('verifikasi_sarpras_at');
            $table->dropColumn('disetujui_at');
            $table->dropColumn('ditolak_at');
        });
    }
};
