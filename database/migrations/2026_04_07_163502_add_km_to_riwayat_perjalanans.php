<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_perjalanans', function (Blueprint $table) {
            // KM odometer saat mulai (hanya diisi sekali di trip pertama hari itu)
            $table->unsignedInteger('km_awal')->nullable()->after('status');

            // KM odometer saat selesai (diisi opsional saat selesaikan)
            $table->unsignedInteger('km_akhir')->nullable()->after('km_awal');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_perjalanans', function (Blueprint $table) {
            $table->dropColumn(['km_awal', 'km_akhir']);
        });
    }
};