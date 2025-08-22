<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesToKegiatansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menambahkan kolom 'notes' setelah kolom 'status' dengan tipe data 'text'
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Menghapus kolom 'notes' jika rollback
            $table->dropColumn('notes');
        });
    }
};
