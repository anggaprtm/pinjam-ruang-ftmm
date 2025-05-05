<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuangansTable extends Migration
{
    public function up()
    {
        Schema::create('ruangans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama');
            $table->longText('deskripsi')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}