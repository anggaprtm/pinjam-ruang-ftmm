<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabel untuk Sub-Task (Checklist)
        Schema::create('productivity_sub_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('productivity_tasks')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // Tabel untuk Lampiran File
        Schema::create('productivity_task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('productivity_tasks')->cascadeOnDelete();
            $table->string('file_name'); // Nama asli file (contoh: laporan_sk.pdf)
            $table->string('file_path'); // Path di storage (contoh: tasks/attachments/xxx.pdf)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productivity_task_attachments');
        Schema::dropIfExists('productivity_sub_tasks');
    }
};