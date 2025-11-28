<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKegiatanHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('kegiatan_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kegiatan_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action'); // e.g. created, verifikasi_sarpras, revisi_operator, resubmitted, disetujui, ditolak
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('kegiatan_histories', function (Blueprint $table) {
            $table->foreign('kegiatan_id')->references('id')->on('kegiatan')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('kegiatan_histories');
    }
}
