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
        // Ubah kolom program_studi dari ENUM menjadi VARCHAR(255)
        // Kita juga set NULLABLE sesuai request awalmu
        DB::statement("ALTER TABLE jadwal_perkuliahan MODIFY COLUMN program_studi VARCHAR(255) NULL");
    }

    public function down()
    {
        // Kembalikan ke ENUM jika rollback (Opsional, sesuaikan list prodi lama)
        DB::statement("ALTER TABLE jadwal_perkuliahan MODIFY COLUMN program_studi ENUM('TI', 'TRKB', 'TSD', 'TE', 'RN') NULL");
    }
};
