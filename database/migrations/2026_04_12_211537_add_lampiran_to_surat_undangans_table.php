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
        Schema::table('surat_undangans', function (Blueprint $table) {
            $table->boolean('use_lampiran')->default(false); // Penanda pakai lampiran/nggak
            $table->longText('lampiran_content')->nullable(); // Isinya (bisa JSON atau Text)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_undangans', function (Blueprint $table) {
            //
        });
    }
};
