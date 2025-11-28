<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->unsignedBigInteger('revisi_by')->nullable()->after('notes');
            $table->timestamp('revisi_at')->nullable()->after('revisi_by');
            $table->string('revisi_level')->nullable()->after('revisi_at');
            $table->text('revisi_notes')->nullable()->after('revisi_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn(['revisi_by', 'revisi_at', 'revisi_level', 'revisi_notes']);
        });
    }
};
