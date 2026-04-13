<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            // Mengubah kolom menjadi nullable
            $table->string('nomor_surat')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->string('nomor_surat')->nullable(false)->change();
        });
    }
};