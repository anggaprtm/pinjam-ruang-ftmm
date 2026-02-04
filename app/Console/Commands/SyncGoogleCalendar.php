<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\GoogleCalendar\Event;
use App\Models\Kegiatan;
use App\Models\User;
use Carbon\Carbon;

class SyncGoogleCalendar extends Command
{
    protected $signature = 'sync:google-calendar';
    protected $description = 'Sinkronisasi jadwal dari Google Calendar ke Database Kegiatan';

    public function handle()
    {
        $this->info('🔄 Memulai sinkronisasi Google Calendar...');

        // 1. Tentukan Range Waktu (PENTING untuk konsistensi)
        $startRange = Carbon::now();
        $endRange   = Carbon::now()->addYear();

        try {
            // Ambil event dari Google
            $googleEvents = Event::get($startRange, $endRange);
        } catch (\Exception $e) {
            $this->error("❌ Gagal konek ke Google: " . $e->getMessage());
            return;
        }

        $defaultRuanganId = 40; 
        $defaultUserId = 24;

        // --- STEP 1: Kumpulkan ID Event dari Google ---
        $googleEventIds = [];

        foreach ($googleEvents as $event) {
            if (!$event->startDateTime || !$event->endDateTime) continue;

            $googleEventIds[] = $event->id; // Simpan ID-nya ke array

            // Logic Update/Create yang lama (Tetap Dipakai)
            $start = Carbon::parse($event->startDateTime)->setTimezone('Asia/Jakarta');
            $end   = Carbon::parse($event->endDateTime)->setTimezone('Asia/Jakarta');

            Kegiatan::updateOrCreate(
                [
                    'google_event_id' => $event->id 
                ],
                [
                    'nama_kegiatan' => $event->name ?? '(Tanpa Judul)',
                    'jenis_kegiatan'=> 'Rapat',
                    'deskripsi'     => $event->description,
                    'waktu_mulai'   => $start,
                    'waktu_selesai' => $end,
                    'ruangan_id'    => $defaultRuanganId,
                    'user_id'       => $defaultUserId, 
                    'nama_pic'      => '*Sinkron Google Calendar',
                    'status'        => 'disetujui',
                    'nomor_telepon' => '-'
                ]
            );
        }

        $this->info("✅ Berhasil update/create data dari Google.");

        // --- STEP 2: Hapus Data Lokal yang Sudah Tidak Ada di Google ---
        // Logic: Cari Kegiatan yang punya google_event_id, 
        // TAPI id-nya TIDAK ADA di array $googleEventIds yang barusan kita ambil.
        // DAN pastikan hanya menghapus yang rentang waktunya sesuai ($startRange) 
        // supaya event masa lalu (sejarah) tidak ikut terhapus.

        $deletedCount = Kegiatan::whereNotNull('google_event_id') // Hanya yang berasal dari Google
            ->whereNotIn('google_event_id', $googleEventIds)      // Yang ID-nya sudah ga ada di Google
            ->where('waktu_mulai', '>=', $startRange)             // Hanya hapus event masa depan (sesuai range fetch)
            ->delete();

        if ($deletedCount > 0) {
            $this->warn("🗑️  Ditemukan $deletedCount event yang dihapus di Google, menghapus dari database lokal...");
        }

        $this->info("🎉 Sinkronisasi Selesai Sepenuhnya!");
    }

}