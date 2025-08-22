<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionRolePivotTable extends Migration
{
    public function up()
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id', 'role_id_fk_10251931')->references('id')->on('roles')->onDelete('cascade');
            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id', 'permission_id_fk_10251931')->references('id')->on('permissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Menghapus foreign key constraints terlebih dahulu
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign('role_id_fk_10251931');
            $table->dropForeign('permission_id_fk_10251931');
        });

        // Menghapus tabel 'permission_role'
        Schema::dropIfExists('permission_role');
    }
}
