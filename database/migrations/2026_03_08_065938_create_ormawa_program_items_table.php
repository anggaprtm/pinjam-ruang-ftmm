<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ormawa_program_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('ormawa_program_plans')->cascadeOnDelete();
            $table->string('kode_proker')->nullable();
            $table->string('nama_rencana');
            $table->date('timeline_mulai_rencana')->nullable();
            $table->date('timeline_selesai_rencana')->nullable();
            $table->text('deskripsi_rencana')->nullable();
            $table->enum('status_item', ['belum_diajukan', 'diajukan', 'proses', 'sik_terbit', 'ditolak', 'arsip'])->default('belum_diajukan');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['plan_id', 'kode_proker']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ormawa_program_items');
    }
};
