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
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            // Kita taruh setelah ID, nullable dulu biar data lama aman
            $table->string('kode_matkul')->nullable()->after('id'); 
        });
    }

    public function down()
    {
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->dropColumn('kode_matkul');
        });
    }
};
