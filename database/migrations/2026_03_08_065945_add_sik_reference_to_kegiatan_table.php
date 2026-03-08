<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->foreignId('sik_application_id')->nullable()->after('google_event_id')->constrained('sik_applications')->nullOnDelete();
            $table->boolean('is_admin_override_sik')->default(false)->after('sik_application_id');
            $table->text('override_reason')->nullable()->after('is_admin_override_sik');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sik_application_id');
            $table->dropColumn(['is_admin_override_sik', 'override_reason']);
        });
    }
};
