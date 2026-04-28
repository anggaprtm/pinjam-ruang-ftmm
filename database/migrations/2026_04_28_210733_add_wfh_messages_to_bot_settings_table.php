<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->text('pagi_pesan_wfh')->nullable()->after('pagi_pesan');
            $table->text('pagi_pesan_dosen_wfh')->nullable()->after('pagi_pesan_dosen');
            $table->text('masuk_pesan_wfh')->nullable()->after('masuk_pesan');
            $table->text('pulang_pesan_wfh')->nullable()->after('pulang_pesan');
        });
    }

    public function down()
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->dropColumn(['pagi_pesan_wfh','pagi_pesan_dosen_wfh','masuk_pesan_wfh', 'pulang_pesan_wfh']);
        });
    }
};