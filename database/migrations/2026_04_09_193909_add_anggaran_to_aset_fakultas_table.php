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
        Schema::table('aset_fakultas', function (Blueprint $table) {
            // Menambahkan kolom anggaran dengan default DAMAS
            $table->enum('anggaran', ['DAMAS', 'HIBAH', 'IKU'])->default('DAMAS')->after('kondisi');
        });
    }

    public function down()
    {
        Schema::table('aset_fakultas', function (Blueprint $table) {
            $table->dropColumn('anggaran');
        });
    }
};
