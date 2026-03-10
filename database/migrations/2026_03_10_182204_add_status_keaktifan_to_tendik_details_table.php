<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tendik_details', function (Blueprint $table) {
            $table->string('status_keaktifan')->default('Aktif')->after('status_kepegawaian');
        });
    }

    public function down()
    {
        Schema::table('tendik_details', function (Blueprint $table) {
            $table->dropColumn('status_keaktifan');
        });
    }
};
