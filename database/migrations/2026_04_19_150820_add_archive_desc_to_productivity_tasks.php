<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom is_archived dan description ke tabel productivity_tasks.
     *
     * Jalankan dengan: php artisan migrate
     */
    public function up(): void
    {
        Schema::table('productivity_tasks', function (Blueprint $table) {
            // Kolom arsip: true = diarsipkan, default false
            $table->boolean('is_archived')->default(false)->after('recurrence');
        });
    }

    public function down(): void
    {
        Schema::table('productivity_tasks', function (Blueprint $table) {
            $table->dropColumn('is_archived');
        });
    }
};