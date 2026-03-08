<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sik_verification_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('sik_verification_flows')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('role_target');
            $table->enum('action_type', ['verify', 'approve', 'issue'])->default('verify');
            $table->string('label_step');
            $table->unsignedSmallInteger('sla_days')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['flow_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sik_verification_flow_steps');
    }
};
