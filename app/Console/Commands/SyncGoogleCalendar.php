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
        $this->info('Memulai sinkronisasi Google Calendar...');

        // 1. Ambil event dari Google (Mulai hari ini sampai 1 tahun ke depan)
        // Pastikan config/google-calendar.php sudah benar calendar_id nya
        try {
            $events = Event::get(Carbon::now(), Carbon::now()->addYear());
        } catch (\Exception $e) {
            $this->error("Gagal konek ke Google: " . $e->getMessage());
            return;
        }

        // 2. Setting Default (Karena Google Calendar gak tau ID user/ruangan di aplikasimu)
        // GANTI ID INI SESUAI DATABASE KAMU
        $defaultRuanganId = 40; // Misal ID 1 adalah Ruang Rapat Utama
        $defaultUserId = 1;    // Misal ID 1 adalah Admin/Sekretaris

        $count = 0;

        foreach ($events as $event) {
            // Skip jika event tidak punya waktu mulai/selesai yang jelas (misal all day event yang kadang bikin error)
            if (!$event->startDateTime || !$event->endDateTime) continue;

            $start = Carbon::parse($event->startDateTime)->setTimezone('Asia/Jakarta');
            $end   = Carbon::parse($event->endDateTime)->setTimezone('Asia/Jakarta');

            // 3. Simpan atau Update ke Database
            Kegiatan::updateOrCreate(
                [
                    'google_event_id' => $event->id 
                ],
                [
                    'nama_kegiatan' => $event->name ?? '(Tanpa Judul)',
                    'jenis_kegiatan'=> 'Rapat', // Default otomatis Rapat
                    'deskripsi'     => $event->description,
                    'waktu_mulai'   => $start,
                    'waktu_selesai' => $end,
                    'ruangan_id'    => $defaultRuanganId,
                    'user_id'       => $defaultUserId, 
                    'nama_pic'      => '*Sinkron Google Calendar', // Penanda ini dari GCal
                    'status'        => 'disetujui', // Langsung setujui
                    'nomor_telepon' => '-'
                ]
            );
            $count++;
        }

        $this->info("Berhasil sinkronisasi $count kegiatan!");
    }
}