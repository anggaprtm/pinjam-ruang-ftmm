<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. EXPAND: Perluas ENUM untuk menampung status LAMA + BARU sementara waktu
        // Kita gabungkan list lama dan baru supaya tidak error saat alter
        DB::statement("ALTER TABLE `kegiatan` MODIFY `status` ENUM(
            'belum_disetujui',
            'verifikasi_akademik',        /* LAMA */
            'verifikasi_sarpras',         /* LAMA */
            'revisi_akademik',            /* LAMA */
            'revisi_sarpras',             /* LAMA */
            'verifikasi_kemahasiswaan',   /* BARU */
            'verifikasi_kasubag_akademik',/* BARU */
            'verifikasi_kasubag_sarpras', /* BARU */
            'revisi_kemahasiswaan',       /* BARU */
            'revisi_kasubag_akademik',    /* BARU */
            'revisi_kasubag_sarpras',     /* BARU */
            'revisi_operator',
            'disetujui',
            'ditolak'
        ) NOT NULL DEFAULT 'belum_disetujui';");

        // 2. MAP: Pindahkan data dari bucket lama ke bucket baru
        // Logika: Data yg nyangkut di "verifikasi_akademik" kita anggap sama dengan "verifikasi_kasubag_akademik"
        
        // Mapping Akademik
        DB::table('kegiatan')
            ->where('status', 'verifikasi_akademik')
            ->update(['status' => 'verifikasi_kasubag_akademik']);
            
        DB::table('kegiatan')
            ->where('status', 'revisi_akademik')
            ->update(['status' => 'revisi_kasubag_akademik']);

        // Mapping Sarpras
        DB::table('kegiatan')
            ->where('status', 'verifikasi_sarpras')
            ->update(['status' => 'verifikasi_kasubag_sarpras']);

        DB::table('kegiatan')
            ->where('status', 'revisi_sarpras')
            ->update(['status' => 'revisi_kasubag_sarpras']);

        // Catatan: Untuk 'verifikasi_kemahasiswaan', karena ini step baru di awal, 
        // data lama (yg sudah lewat step awal) tidak perlu dimundurkan. Biarkan mereka lanjut dari posisinya.

        // 3. CONTRACT: Bersihkan ENUM, hapus status lama yang sudah tidak dipakai (kosong)
        DB::statement("ALTER TABLE `kegiatan` MODIFY `status` ENUM(
            'belum_disetujui',
            'verifikasi_kemahasiswaan',
            'verifikasi_kasubag_akademik',
            'verifikasi_kasubag_sarpras',
            'revisi_operator',
            'revisi_kemahasiswaan',
            'revisi_kasubag_akademik',
            'revisi_kasubag_sarpras',
            'disetujui',
            'ditolak'
        ) NOT NULL DEFAULT 'belum_disetujui';");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Kalau mau rollback, lakukan kebalikannya
        
        // 1. Expand lagi (campur lama & baru)
        DB::statement("ALTER TABLE `kegiatan` MODIFY `status` ENUM(
            'belum_disetujui',
            'verifikasi_akademik',
            'verifikasi_sarpras',
            'revisi_akademik',
            'revisi_sarpras',
            'verifikasi_kemahasiswaan',
            'verifikasi_kasubag_akademik',
            'verifikasi_kasubag_sarpras',
            'revisi_kemahasiswaan',
            'revisi_kasubag_akademik',
            'revisi_kasubag_sarpras',
            'revisi_operator',
            'disetujui',
            'ditolak'
        ) NOT NULL DEFAULT 'belum_disetujui';");

        // 2. Map Balik (New -> Old)
        DB::table('kegiatan')
            ->where('status', 'verifikasi_kasubag_akademik')
            ->update(['status' => 'verifikasi_akademik']);

        DB::table('kegiatan')
            ->where('status', 'revisi_kasubag_akademik')
            ->update(['status' => 'revisi_akademik']);
            
        DB::table('kegiatan')
            ->where('status', 'verifikasi_kasubag_sarpras')
            ->update(['status' => 'verifikasi_sarpras']);

        DB::table('kegiatan')
            ->where('status', 'revisi_kasubag_sarpras')
            ->update(['status' => 'revisi_sarpras']);

        // Khusus Kemahasiswaan (flow baru), kalau di-rollback mau dijadikan apa?
        // Opsi paling aman: kembalikan ke 'belum_disetujui' atau biarkan error (tergantung kebijakan).
        // Disini kita kembalikan ke 'belum_disetujui'
        DB::table('kegiatan')
            ->whereIn('status', ['verifikasi_kemahasiswaan', 'revisi_kemahasiswaan'])
            ->update(['status' => 'belum_disetujui']);

        // 3. Contract Balik (Hanya status lama)
        DB::statement("ALTER TABLE `kegiatan` MODIFY `status` ENUM(
            'belum_disetujui',
            'verifikasi_akademik',
            'verifikasi_sarpras',
            'disetujui',
            'ditolak',
            'revisi_operator',
            'revisi_akademik',
            'revisi_sarpras'
        ) NOT NULL DEFAULT 'belum_disetujui';");
    }
};
