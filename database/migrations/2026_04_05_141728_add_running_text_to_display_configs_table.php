<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('display_configs', function (Blueprint $table) {
            $table->text('running_text')->nullable()->after('panel_visibility');
        });
    }

    public function down(): void
    {
        Schema::table('display_configs', function (Blueprint $table) {
            $table->dropColumn('running_text');
        });
    }
};