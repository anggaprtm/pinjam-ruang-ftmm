<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sik_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sik_application_id')->constrained('sik_applications')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('alasan_perubahan');
            $table->json('old_payload_json')->nullable();
            $table->json('new_payload_json')->nullable();
            $table->enum('status_amendment', ['submitted', 'on_verification', 'approved', 'rejected'])->default('submitted');
            $table->timestamp('effective_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status_amendment', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sik_amendments');
    }
};
