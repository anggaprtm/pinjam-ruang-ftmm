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
            $table->enum('tipe', ['Kuliah Reguler', 'Seminar Proposal', 'Seminar Hasil', 'PHL'])->nullable()->after('dosen');
            $table->enum('program_studi', ['TI', 'TRKB', 'TSD', 'TE', 'RN'])->nullable()->after('tipe');
        });
    }

    public function down()
    {
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->dropColumn('tipe');
            $table->dropColumn('program_studi');
        });
    }

};
