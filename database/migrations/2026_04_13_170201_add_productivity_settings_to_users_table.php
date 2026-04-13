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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan 2 kolom boolean dengan nilai default true (aktif)
            $table->boolean('telegram_remind_morning')->default(true)->after('telegram_chat_id');
            $table->boolean('telegram_remind_deadline')->default(true)->after('telegram_remind_morning');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_remind_morning', 'telegram_remind_deadline']);
        });
    }
};
