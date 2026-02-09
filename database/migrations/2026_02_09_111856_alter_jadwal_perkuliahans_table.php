<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. PASTIKAN DATA SEMESTER ADA DULU
        // Kita cari semester pertama, kalau tidak ada buat baru.
        $defaultSemester = DB::table('semesters')->first();
        
        if (!$defaultSemester) {
            $defaultSemesterId = DB::table('semesters')->insertGetId([
                'nama'            => 'Semester Migrasi (Default)',
                'tanggal_mulai'   => now(),
                'tanggal_selesai' => now()->addMonths(6),
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } else {
            $defaultSemesterId = $defaultSemester->id;
        }

        // 2. CEK & BUAT KOLOM (Safe Mode)
        if (!Schema::hasColumn('jadwal_perkuliahan', 'semester_id')) {
            Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
                // Buat nullable dulu biar tidak error saat creation
                $table->unsignedBigInteger('semester_id')->nullable()->after('id');
            });
        }

        // 3. [SOLUSI UTAMA] UPDATE SEMUA DATA YANG INVALID
        // Kita paksa semua jadwal yang semester_id-nya NULL atau 0 menjadi ID semester default.
        DB::table('jadwal_perkuliahan')
            ->whereNull('semester_id')
            ->orWhere('semester_id', 0)
            ->update(['semester_id' => $defaultSemesterId]);

        // 4. PASANG CONSTRAINT & BERSIH-BERSIH
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            // Ubah jadi NOT NULL (sekarang aman karena data sudah diisi semua di langkah 3)
            $table->unsignedBigInteger('semester_id')->nullable(false)->change();
            
            // Pasang Foreign Key
            // Menggunakan array syntax agar Laravel otomatis generate nama constraint yang unik
            // tapi kita cek dulu apa constraintnya sudah ada (manual check jarang bisa di migration, jadi kita try-catch logic via Schema)
            
            // Hapus kolom lama jika masih ada
            if (Schema::hasColumn('jadwal_perkuliahan', 'berlaku_mulai')) {
                $table->dropColumn(['berlaku_mulai', 'berlaku_sampai']);
            }
        });
        
        // Pasang Foreign Key di step terpisah untuk memastikan struktur sudah siap
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
             $table->foreign('semester_id', 'jadwal_perkuliahan_semester_id_foreign')
                  ->references('id')->on('semesters')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            // Hapus FK
            $table->dropForeign('jadwal_perkuliahan_semester_id_foreign');
            
            // Kembalikan kolom lama
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_sampai')->nullable();
            
            // Hapus kolom baru
            $table->dropColumn('semester_id');
        });
    }
};