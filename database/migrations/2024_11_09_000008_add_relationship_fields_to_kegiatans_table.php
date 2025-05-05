<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsTokegiatanTable extends Migration
{
    public function up()
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->unsignedBigInteger('ruangan_id')->nullable();
            $table->foreign('ruangan_id', 'ruangan_fk_10251974')->references('id')->on('ruangans');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_10251979')->references('id')->on('users');
        });
    }
}