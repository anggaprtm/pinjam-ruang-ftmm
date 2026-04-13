<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('productivity_habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained('productivity_habits')->cascadeOnDelete();
            $table->date('tanggal');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            // Mencegah duplikasi log habit di hari yang sama
            $table->unique(['habit_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productivity_habit_logs');
    }
};
