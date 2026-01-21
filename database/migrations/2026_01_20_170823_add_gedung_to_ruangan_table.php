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
        Schema::table('ruangan', function (Blueprint $table) {
            // Kita taruh setelah kolom nama, boleh kosong (nullable) dulu
            $table->string('gedung')->nullable()->after('nama');
        });
    }

    public function down()
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropColumn('gedung');
        });
    }
};
