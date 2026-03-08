<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sik_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_item_id')->constrained('ormawa_program_items');
            $table->foreignId('ormawa_id')->constrained('ormawas');
            $table->foreignId('flow_id')->nullable()->constrained('sik_verification_flows')->nullOnDelete();
            $table->string('judul_final_kegiatan');
            $table->date('timeline_mulai_final');
            $table->date('timeline_selesai_final');
            $table->string('rencana_tempat')->nullable();
            $table->string('proposal_path')->nullable();
            $table->string('surat_permohonan_path')->nullable();
            $table->enum('status_sik', ['draft', 'submitted', 'on_verification', 'need_revision', 'approved_final', 'issued', 'cancelled'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nomor_sik_eoffice')->nullable();
            $table->text('catatan_terakhir')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('program_item_id');
            $table->index(['ormawa_id', 'status_sik']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sik_applications');
    }
};
