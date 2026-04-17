<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_ticket_replies', function (Blueprint $table) {
            $table->id();
            // Relasi ke CentralTicket di Nexus
            $table->foreignId('central_ticket_id')->constrained('central_tickets')->onDelete('cascade');
            
            // ID asli dari TickTrack untuk referensi
            $table->unsignedBigInteger('original_reply_id')->nullable(); 
            
            // Siapa yang balas? (Kita pakai string biar fleksibel, bisa nampung nama Guest, Admin TickTrack, atau Admin Nexus)
            $table->string('replier_name');
            $table->string('replier_role')->default('user'); // misal: 'user', 'admin_ticktrack', 'admin_nexus'
            
            $table->text('content');
            $table->string('attachment_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_ticket_replies');
    }
};