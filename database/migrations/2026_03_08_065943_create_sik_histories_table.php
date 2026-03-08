<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sik_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sik_application_id')->constrained('sik_applications')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->json('payload_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sik_application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sik_histories');
    }
};
