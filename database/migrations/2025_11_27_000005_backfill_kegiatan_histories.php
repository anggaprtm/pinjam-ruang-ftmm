<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillKegiatanHistories extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Iterasi semua kegiatan untuk membuat snapshot history dari kolom existing
        $kegiatans = DB::table('kegiatan')->get();

        foreach ($kegiatans as $k) {
            // 1. Created
            DB::table('kegiatan_histories')->insert([
                'kegiatan_id' => $k->id,
                'user_id' => $k->user_id,
                'action' => 'created',
                'note' => null,
                'meta' => null,
                'created_at' => $k->created_at,
            ]);

            // 2. Verifikasi Operator (verifikasi_sarpras_at)
            if (!empty($k->verifikasi_sarpras_at)) {
                DB::table('kegiatan_histories')->insert([
                    'kegiatan_id' => $k->id,
                    'user_id' => null,
                    'action' => 'verifikasi_sarpras',
                    'note' => null,
                    'meta' => null,
                    'created_at' => $k->verifikasi_sarpras_at,
                ]);
            }

            // 3. Revisi
            if (!empty($k->revisi_at)) {
                DB::table('kegiatan_histories')->insert([
                    'kegiatan_id' => $k->id,
                    'user_id' => $k->revisi_by ?? null,
                    'action' => 'revisi_' . ($k->revisi_level ?? 'operator'),
                    'note' => $k->revisi_notes ?? null,
                    'meta' => json_encode(['level' => $k->revisi_level ?? null]),
                    'created_at' => $k->revisi_at,
                ]);
            }

            // 4. Resubmitted: if updated_at > created_at and status is belum_disetujui after revisi
            if (!empty($k->revisi_at) && !empty($k->updated_at) && strtotime($k->updated_at) > strtotime($k->revisi_at)) {
                DB::table('kegiatan_histories')->insert([
                    'kegiatan_id' => $k->id,
                    'user_id' => $k->user_id,
                    'action' => 'resubmitted',
                    'note' => null,
                    'meta' => null,
                    'created_at' => $k->updated_at,
                ]);
            }

            // 5. Verifikasi Akademik
            if (!empty($k->verifikasi_akademik_at)) {
                DB::table('kegiatan_histories')->insert([
                    'kegiatan_id' => $k->id,
                    'user_id' => null,
                    'action' => 'verifikasi_akademik',
                    'note' => null,
                    'meta' => null,
                    'created_at' => $k->verifikasi_akademik_at,
                ]);
            }

            // 6. Disetujui
            if (!empty($k->disetujui_at)) {
                DB::table('kegiatan_histories')->insert([
                    'kegiatan_id' => $k->id,
                    'user_id' => null,
                    'action' => 'disetujui',
                    'note' => null,
                    'meta' => null,
                    'created_at' => $k->disetujui_at,
                ]);
            }

            // 7. Ditolak
            if (!empty($k->ditolak_at)) {
                DB::table('kegiatan_histories')->insert([
                    'kegiatan_id' => $k->id,
                    'user_id' => null,
                    'action' => 'ditolak',
                    'note' => $k->notes ?? null,
                    'meta' => null,
                    'created_at' => $k->ditolak_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // tidak di-rollback otomatis (berisiko menghapus history yang sudah dibuat setelah), jadi kosongkan
    }
}
