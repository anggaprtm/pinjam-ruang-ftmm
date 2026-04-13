<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\ProductivityTask;
use App\Models\ProductivityHabit;
use Carbon\Carbon;

class SendProductivityReminder extends Command
{
    /**
     * Argumen {tipe} bisa diisi: morning atau deadline
     */
    protected $signature = 'productivity:remind {tipe}';
    protected $description = 'Kirim reminder produktivitas via Telegram (morning / deadline)';

    public function handle(TelegramService $telegram)
    {
        $tipe = $this->argument('tipe');
        $validTypes = ['morning', 'deadline'];

        if (!in_array($tipe, $validTypes)) {
            $this->error("Tipe tidak valid! Gunakan: morning atau deadline");
            return;
        }

        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        $this->info("🚀 Menjalankan Productivity Reminder tipe: [{$tipe}]...");

        if ($tipe === 'morning') {
            $this->processMorningSummary($telegram, $today);
        } elseif ($tipe === 'deadline') {
            $this->processDeadlineReminder($telegram, $now);
        }

        $this->info("✅ Proses Reminder [{$tipe}] Selesai.");
    }

    /**
     * ─── LOGIKA MORNING SUMMARY (REKAP PAGI) ───────────────────────────
     */
    private function processMorningSummary(TelegramService $telegram, $today)
    {
        // Cari user yang punya Telegram ID
        $users = User::whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->where('telegram_remind_morning', true) // <-- Tambahan filter
            ->get();

        foreach ($users as $user) {
            // Ambil tugas yang deadline-nya hari ini dan belum selesai
            $tasks = ProductivityTask::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereDate('deadline_at', $today)
                ->where('remind_morning', true)
                ->orderBy('deadline_at', 'asc')
                ->get();

            // Ambil habit untuk diingatkan
            $habits = ProductivityHabit::where('user_id', $user->id)->get();

            if ($tasks->isNotEmpty() || $habits->isNotEmpty()) {
                $msg = "🌅 <b>Selamat Pagi, {$user->name}!</b> 👋\n\n";
                $msg .= "Mari buat hari ini lebih produktif. Berikut adalah hal yang menantimu hari ini:\n\n";

                if ($tasks->isNotEmpty()) {
                    $msg .= "📌 <b>Fokus Tugas Hari Ini:</b>\n";
                    foreach ($tasks as $i => $task) {
                        $time = Carbon::parse($task->deadline_at)->format('H:i');
                        $tag = $task->tag ? " [{$task->tag}]" : "";
                        $msg .= ($i + 1) . ". {$task->title}{$tag} ⏰ <b>{$time}</b>\n";
                    }
                    $msg .= "\n";
                }

                if ($habits->isNotEmpty()) {
                    $msg .= "🌱 <b>Jangan Lupa Habitmu:</b>\n";
                    foreach ($habits as $habit) {
                        $msg .= "- {$habit->name}\n";
                    }
                    $msg .= "\n";
                }

                $msg .= "Cek dashboard untuk detailnya. Semangat! 🔥";

                try {
                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                    $this->info("   -> Rekap Pagi dikirim ke {$user->name}");
                } catch (\Exception $e) {
                    $this->error("   -> Gagal mengirim ke {$user->name}: " . $e->getMessage());
                }

                sleep(1); // Kasih jeda agar tidak kena rate limit Telegram
            }
        }
    }

    /**
     * ─── LOGIKA DEADLINE REMINDER (H-1 JAM) ────────────────────────────
     */
    private function processDeadlineReminder(TelegramService $telegram, Carbon $now)
    {
        $oneHourLater = $now->copy()->addMinutes(65); // Jendela waktu 1 jam ke depan

        // Ambil tugas yang mendekati deadline (H-1 Jam)
        $tasks = ProductivityTask::with('user')
            ->whereHas('user', function($q) {
                // <-- Tambahan filter untuk mengecek user yang mengizinkan notif deadline
                $q->whereNotNull('telegram_chat_id')
                  ->where('telegram_chat_id', '!=', '')
                  ->where('telegram_remind_deadline', true);
            })
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('deadline_at')
            ->where('remind_h_minus_1', true)
            ->where('is_reminded_h_1', false)
            ->whereBetween('deadline_at', [$now, $oneHourLater])
            ->get();

        if ($tasks->isEmpty()) {
            $this->info("   -> Tidak ada tugas yang mendekati deadline saat ini.");
            return;
        }

        foreach ($tasks as $task) {
            $user = $task->user;

            if ($user && !empty($user->telegram_chat_id)) {
                $time = Carbon::parse($task->deadline_at)->format('H:i');
                
                $msg = "⚠️ <b>REMINDER DEADLINE!</b> ⚠️\n\n";
                $msg .= "Halo {$user->name}, tugasmu akan segera jatuh tempo dalam waktu kurang dari 1 jam!\n\n";
                $msg .= "📌 <b>Tugas:</b> {$task->title}\n";
                if ($task->tag) {
                    $msg .= "🏷️ <b>Tag:</b> {$task->tag}\n";
                }
                $msg .= "⏰ <b>Batas Waktu:</b> {$time} WIB\n\n";
                $msg .= "Yuk segera diselesaikan atau perbarui statusnya di dashboard! 💪";

                try {
                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                    
                    // Tandai bahwa tugas ini sudah dikirimkan notifikasinya
                    $task->update(['is_reminded_h_1' => true]);
                    
                    $this->info("   -> Reminder H-1 Jam untuk tugas '{$task->title}' dikirim ke {$user->name}");
                } catch (\Exception $e) {
                    $this->error("   -> Gagal mengirim ke {$user->name}: " . $e->getMessage());
                }

                sleep(1);
            }
        }
    }
}