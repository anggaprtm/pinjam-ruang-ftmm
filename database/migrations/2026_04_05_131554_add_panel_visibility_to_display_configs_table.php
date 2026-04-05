<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('display_configs', function (Blueprint $table) {
            // Tambahkan kolom JSON, nullable agar aman untuk data yang sudah ada
            $table->json('panel_visibility')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('display_configs', function (Blueprint $table) {
            $table->dropColumn('panel_visibility');
        });
    }
};