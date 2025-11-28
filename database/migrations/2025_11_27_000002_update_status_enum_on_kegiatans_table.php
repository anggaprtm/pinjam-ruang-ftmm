<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateStatusEnumOnKegiatansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Add new enum values for revision workflow
        DB::statement("ALTER TABLE `kegiatan` MODIFY `status` ENUM('belum_disetujui','verifikasi_akademik','verifikasi_sarpras','disetujui','ditolak','revisi_operator','revisi_akademik','revisi_sarpras') NOT NULL DEFAULT 'belum_disetujui';");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Revert to previous enum values (without revisi_*)
        DB::statement("ALTER TABLE `kegiatan` MODIFY `status` ENUM('belum_disetujui','verifikasi_akademik','verifikasi_sarpras','disetujui','ditolak') NOT NULL DEFAULT 'belum_disetujui';");
    }
}
