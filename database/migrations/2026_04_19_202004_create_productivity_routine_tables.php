<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabel Master: Definisi Tugas Rutinan
        Schema::create('productivity_routine_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete(); // KTU / Kasubag
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();     // Staf yang ditugaskan
            $table->string('title');
            $table->json('target_months'); // Menyimpan array bulan target, misal: [1, 3, 5, 7, 9, 11]
            $table->integer('year');       // Tahun berjalannya tugas, misal: 2026
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel Transaksi: Log / Bukti Pelaksanaan Bulanan
        Schema::create('productivity_routine_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_task_id')->constrained('productivity_routine_tasks')->cascadeOnDelete();
            $table->integer('month'); // Bulan pelaporan (1 s.d 12)
            $table->datetime('completed_at')->nullable();
            $table->string('proof_file_path')->nullable(); // Lokasi file PDF/Foto bukti dukung
            $table->text('notes')->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->timestamps();

            // Cegah duplikasi: 1 tugas hanya boleh punya 1 log di bulan yang sama
            $table->unique(['routine_task_id', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('productivity_routine_logs');
        Schema::dropIfExists('productivity_routine_tasks');
    }
};