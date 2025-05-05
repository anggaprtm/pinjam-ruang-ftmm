<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('perkuliahan', function (Blueprint $table) {
            $table->id();
            $table->string('matkul'); // Nama mata kuliah
            $table->string('dosen'); // Nama dosen
            $table->string('day'); // Hari kuliah (Senin, Selasa, dll)
            $table->time('start_time'); // Waktu mulai kuliah
            $table->time('end_time'); // Waktu selesai kuliah
            $table->foreignId('ruangan_id')->constrained()->onDelete('cascade'); // Hubungkan dengan tabel rooms jika perlu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perkuliahan');
    }
};
