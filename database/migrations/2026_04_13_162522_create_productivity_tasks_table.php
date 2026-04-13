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
        Schema::create('productivity_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->dateTime('deadline_at')->nullable();
            
            // Konfigurasi Reminder via Telegram khusus task ini (H-1 Jam, Pagi, dsb)
            $table->boolean('remind_morning')->default(true); 
            $table->boolean('remind_h_minus_1')->default(true);
            $table->boolean('is_reminded_h_1')->default(false); // Flag agar bot tidak spam
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productivity_tasks');
    }
};
