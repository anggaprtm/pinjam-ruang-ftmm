<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sik_application_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sik_application_id')->constrained('sik_applications')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('role_target');
            $table->enum('status_step', ['pending', 'approved', 'rejected', 'revised'])->default('pending');
            $table->foreignId('acted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->unsignedSmallInteger('sla_days')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sik_application_id', 'step_order']);
            $table->index(['role_target', 'status_step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sik_application_steps');
    }
};
