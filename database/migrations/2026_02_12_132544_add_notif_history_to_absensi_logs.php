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
        Schema::table('absensi_logs', function (Blueprint $table) {
            // Kolom JSON untuk simpan history notif (bisa null)
            $table->json('notif_history')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('absensi_logs', function (Blueprint $table) {
            $table->dropColumn('notif_history');
        });
    }
};
