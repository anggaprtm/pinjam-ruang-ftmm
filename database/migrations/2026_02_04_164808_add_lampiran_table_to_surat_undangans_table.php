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
        Schema::table('surat_undangans', function (Blueprint $table) {
            $table->longText('lampiran_table')->nullable()->after('lampiran_content');
        });
    }

    public function down()
    {
        Schema::table('surat_undangans', function (Blueprint $table) {
            $table->dropColumn('lampiran_table');
        });
    }
};
