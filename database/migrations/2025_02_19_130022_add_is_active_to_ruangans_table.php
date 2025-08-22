<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToRuangansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            // Menambahkan kolom 'is_active' dengan tipe data 'tinyint(1)' untuk status aktif
            $table->tinyInteger('is_active')->default(1)->after('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            // Menghapus kolom 'is_active' jika rollback
            $table->dropColumn('is_active');
        });
    }
};
