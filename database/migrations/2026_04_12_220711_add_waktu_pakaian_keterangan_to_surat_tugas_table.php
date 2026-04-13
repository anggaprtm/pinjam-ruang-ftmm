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
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->string('waktu_tugas')->nullable()->after('hari_tanggal_tugas');
            $table->string('pakaian')->nullable()->after('tempat_tugas');
            $table->text('keterangan')->nullable()->after('pakaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropColumn('waktu_tugas');
            $table->dropColumn('pakaian');
            $table->dropColumn('keterangan');
        });
    }
};
