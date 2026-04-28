<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_logs', function (Blueprint $table) {
            // Nilai: 'WFO', 'WFH', 'dinas_dalam', 'dinas_luar', null
            $table->string('mode_kerja')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_logs', function (Blueprint $table) {
            $table->dropColumn('mode_kerja');
        });
    }
};