<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ormawa_program_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('ormawas');
            $table->unsignedSmallInteger('tahun');
            $table->foreignId('dibuat_oleh_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_plan', ['draft', 'published', 'locked'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ormawa_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ormawa_program_plans');
    }
};
