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
        Schema::table('productivity_tasks', function (Blueprint $table) {
            // Pilihan: none (sekali), daily (tiap hari), weekly (tiap minggu), monthly (tiap bulan)
            $table->string('recurrence')->default('none')->after('deadline_at');
        });
    }

    public function down()
    {
        Schema::table('productivity_tasks', function (Blueprint $table) {
            $table->dropColumn('recurrence');
        });
    }
};
