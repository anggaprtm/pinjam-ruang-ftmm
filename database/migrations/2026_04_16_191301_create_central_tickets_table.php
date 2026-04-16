<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_tickets', function (Blueprint $table) {
            $table->id();
            // ID asli dari database TickTrack, berguna kalau mau nembak API balik
            $table->unsignedBigInteger('original_ticket_id')->unique(); 
            $table->string('code')->unique();
            $table->string('reporter_name');
            $table->string('reporter_email');
            $table->boolean('is_guest')->default(false);
            $table->string('title');
            $table->string('category');
            $table->text('description');
            $table->string('priority');
            $table->string('status');
            $table->string('attachment_url')->nullable(); // Simpan URL file
            $table->timestamps(); // Buat nyatet kapan masuk ke Nexus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_tickets');
    }
};