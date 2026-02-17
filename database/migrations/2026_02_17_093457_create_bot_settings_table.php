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
        Schema::create('bot_settings', function (Blueprint $table) {
            $table->id();
            // Reminder Pagi
            $table->boolean('pagi_aktif')->default(true);
            $table->time('pagi_jam')->default('06:30:00');
            $table->text('pagi_pesan');
            
            // Peringatan Telat Masuk
            $table->boolean('masuk_aktif')->default(true);
            $table->time('masuk_jam')->default('07:50:00');
            $table->text('masuk_pesan');

            // Peringatan Pulang
            $table->boolean('pulang_aktif')->default(true);
            $table->time('pulang_jam')->default('17:00:00');
            $table->text('pulang_pesan');

            // Evaluasi Telat 2x
            $table->boolean('evaluasi_aktif')->default(true);
            $table->time('evaluasi_jam')->default('19:00:00');
            $table->text('evaluasi_pesan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_settings');
    }
};
