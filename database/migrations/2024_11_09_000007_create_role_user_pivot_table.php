<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleUserPivotTable extends Migration
{
    public function up()
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'user_id_fk_10251940')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id', 'role_id_fk_10251940')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Menghapus foreign key constraints terlebih dahulu
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropForeign('user_id_fk_10251940');
            $table->dropForeign('role_id_fk_10251940');
        });

        // Menghapus tabel 'role_user'
        Schema::dropIfExists('role_user');
    }
}
