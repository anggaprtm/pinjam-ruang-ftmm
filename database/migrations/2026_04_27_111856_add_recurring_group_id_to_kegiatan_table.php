<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Kita pakai UUID karena lebih aman dan unik untuk mengelompokkan ID
            $table->uuid('recurring_group_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn('recurring_group_id');
        });
    }
};