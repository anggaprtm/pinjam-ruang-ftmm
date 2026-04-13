<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_undangans', function (Blueprint $table) {
            // Simpan tanggal acara mentah agar form edit bisa di-populate ulang
            $table->string('tanggal_acara_raw')->nullable()->after('hari_tanggal_acara');

            // Soft delete support
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('surat_undangans', function (Blueprint $table) {
            $table->dropColumn('tanggal_acara_raw');
            $table->dropSoftDeletes();
        });
    }
};