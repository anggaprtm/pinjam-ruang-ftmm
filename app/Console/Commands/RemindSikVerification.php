<?php

namespace App\Console\Commands;

use App\Models\SikApplicationStep;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemindSikVerification extends Command
{
    protected $signature = 'sik:remind-verification';
    protected $description = 'Kirim reminder SLA step verifikasi SIK yang pending';

    public function handle(TelegramService $telegram): int
    {
        $targetChat = env('TELEGRAM_KEMAHASISWAAN_GROUP_ID') ?: env('TELEGRAM_ADMIN_GROUP_ID');
        if (! $targetChat) {
            $this->warn('TELEGRAM_KEMAHASISWAAN_GROUP_ID / TELEGRAM_ADMIN_GROUP_ID belum diset.');
            return self::SUCCESS;
        }

        $now = Carbon::now();
        $soon = $now->copy()->addDay();

        $steps = SikApplicationStep::with(['application.programItem.plan.ormawa'])
            ->where('status_step', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $soon)
            ->orderBy('due_at', 'asc')
            ->limit(25)
            ->get();

        if ($steps->isEmpty()) {
            $this->info('Tidak ada step SIK yang perlu diingatkan.');
            return self::SUCCESS;
        }

        $lines = [];
        foreach ($steps as $step) {
            $app = $step->application;
            $ormawa = $app?->programItem?->plan?->ormawa?->nama ?? 'Ormawa';
            $judul = $app?->judul_final_kegiatan ?? '-';
            $due = Carbon::parse($step->due_at);
            $statusWaktu = $due->lt($now) ? 'OVERDUE' : 'H-1/Hari ini';

            $lines[] = sprintf(
                "• [%s] %s | %s | Step #%d (%s) | Due: %s",
                $statusWaktu,
                $ormawa,
                $judul,
                $step->step_order,
                $step->role_target,
                $due->format('d M Y H:i')
            );
        }

        $message = "⏰ <b>Reminder SLA Verifikasi SIK</b>\n\n"
            . implode("\n", $lines)
            . "\n\n<i>Auto reminder sistem</i>";

        $telegram->sendMessage($targetChat, $message);
        $this->info('Reminder SIK berhasil dikirim.');

        return self::SUCCESS;
    }
}
