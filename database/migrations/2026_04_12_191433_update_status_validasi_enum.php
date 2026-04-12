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
        DB::statement("ALTER TABLE lembur_kegiatan_pegawai 
            MODIFY COLUMN status_validasi 
            ENUM('menunggu', 'valid', 'tidak_valid', 'tidak_fr') 
            NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE lembur_kegiatan_pegawai 
            MODIFY COLUMN status_validasi 
            ENUM('menunggu', 'valid', 'tidak_valid') 
            NOT NULL DEFAULT 'menunggu'");
    }
};
