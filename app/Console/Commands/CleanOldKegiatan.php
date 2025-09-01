<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kegiatan;
use Carbon\Carbon;

class CleanOldKegiatan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-old-kegiatan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete old kegiatan records on or before a specific month.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting the cleanup process for old kegiatan...');

        // Menentukan tanggal akhir: akhir bulan Juli 2025
        $cutoffDate = Carbon::create(2025, 7, 31)->endOfDay();
        $this->info("The cutoff date is set to: " . $cutoffDate->toDateTimeString());

        // Mencari kegiatan yang akan dihapus
        $query = Kegiatan::where('waktu_mulai', '<=', $cutoffDate);

        // Menghitung jumlah data yang akan dihapus untuk konfirmasi
        $count = $query->count();

        if ($count === 0) {
            $this->info('No old kegiatan records found to delete. All clean!');
            return 0;
        }

        $this->warn("Found {$count} kegiatan records on or before July 2025.");

        // Meminta konfirmasi sebelum benar-benar menghapus
        if ($this->confirm('Are you sure you want to permanently delete these ' . $count . ' records? This action cannot be undone.')) {
            
            $this->info('Deleting records in chunks to save memory...');
            $deletedCount = 0;

            // Menghapus data dalam chunk untuk efisiensi memori
            $query->chunkById(200, function ($kegiatans) use (&$deletedCount) {
                $idsToDelete = $kegiatans->pluck('id');
                $numDeleted = Kegiatan::whereIn('id', $idsToDelete)->delete();
                $deletedCount += $numDeleted;
                $this->info($deletedCount . ' records deleted...');
            });

            $this->info('-----------------------------------------');
            $this->info("Cleanup complete. Successfully deleted {$deletedCount} records.");
        } else {
            $this->info('Cleanup cancelled by user.');
        }

        return 0;
    }
}
