<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dosen_details', function (Blueprint $table) {
            $table->string('jabatan_struktural')->nullable()->after('jabatan_fungsional');
        });
    }
    public function down()
    {
        Schema::table('dosen_details', function (Blueprint $table) {
            $table->dropColumn('jabatan_struktural');
        });
    }
};
