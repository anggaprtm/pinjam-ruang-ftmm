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
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->string('dosen_pembimbing_1')->nullable()->after('jenis_kegiatan');
            $table->string('dosen_pembimbing_2')->nullable()->after('dosen_pembimbing_1');
            $table->string('dosen_penguji_1')->nullable()->after('dosen_pembimbing_2');
            $table->string('dosen_penguji_2')->nullable()->after('dosen_penguji_1');
        });
    }

    public function down()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn([
                'dosen_pembimbing_1', 
                'dosen_pembimbing_2', 
                'dosen_penguji_1', 
                'dosen_penguji_2'
            ]);
        });
    }
};
