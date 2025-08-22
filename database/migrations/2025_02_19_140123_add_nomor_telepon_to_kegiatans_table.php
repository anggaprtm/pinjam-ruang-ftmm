<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNomorTeleponToKegiatansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menambahkan kolom 'nomor_telepon' dengan tipe data 'text' setelah kolom 'user_id'
            $table->text('nomor_telepon')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menghapus kolom 'nomor_telepon' jika rollback
            $table->dropColumn('nomor_telepon');
        });
    }
};
