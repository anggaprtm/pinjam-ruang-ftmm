<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ormawa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('ormawas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ormawa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ormawa_user');
    }
};
