<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sik_applications', function (Blueprint $table) {
            $table->boolean('is_amendment_open')->default(false)->after('catatan_terakhir');
            $table->foreignId('amendment_opened_by_user_id')->nullable()->after('is_amendment_open')->constrained('users')->nullOnDelete();
            $table->timestamp('amendment_opened_at')->nullable()->after('amendment_opened_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('sik_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('amendment_opened_by_user_id');
            $table->dropColumn(['is_amendment_open', 'amendment_opened_at']);
        });
    }
};
