<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuangansTable extends Migration
{
    public function up()
    {
        Schema::create('ruangan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama');
            $table->longText('deskripsi')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Menghapus tabel 'ruangan' jika rollback migrasi
        Schema::dropIfExists('ruangan');
    }
}
