<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\ProductivityTask;
use App\Models\ProductivityHabit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http; // Tambahan untuk tombol inline

class SendProductivityReminder extends Command
{
    /**
     * Argumen {tipe} bisa diisi: morning, deadline, atau exact
     */
    protected $signature = 'productivity:remind {tipe}';
    protected $description = 'Kirim reminder produktivitas via Telegram (morning / deadline / exact)';

    public function handle(TelegramService $telegram)
    {
        $tipe = $this->argument('tipe');
        $validTypes = ['morning', 'deadline', 'exact']; // Tambahkan 'exact'

        if (!in_array($tipe, $validTypes)) {
            $this->error("Tipe tidak valid! Gunakan: morning, deadline, atau exact");
            return;
        }

        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        $this->info("🚀 Menjalankan Productivity Reminder tipe: [{$tipe}]...");

        if ($tipe === 'morning') {
            $this->processMorningSummary($telegram, $today);
        } elseif ($tipe === 'deadline') {
            $this->processDeadlineReminder($telegram, $now);
        } elseif ($tipe === 'exact') {
            $this->processExactReminder($now); // Fungsi Baru
        }

        $this->info("✅ Proses Reminder [{$tipe}] Selesai.");
    }

    /**
     * ─── LOGIKA MORNING SUMMARY (REKAP PAGI) ───────────────────────────
     */
    private function processMorningSummary(TelegramService $telegram, $today)
    {
        // ... (KODE LAMA KAMU TETAP SAMA DI SINI) ...
        $users = User::whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->where('telegram_remind_morning', true)
            ->get();

        foreach ($users as $user) {
            $tasks = ProductivityTask::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereDate('deadline_at', $today)
                ->where('remind_morning', true)
                ->orderBy('deadline_at', 'asc')
                ->get();

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

                // if ($habits->isNotEmpty()) {
                //     $msg .= "🌱 <b>Jangan Lupa Habitmu:</b>\n";
                //     foreach ($habits as $habit) {
                //         $msg .= "- {$habit->name}\n";
                //     }
                //     $msg .= "\n";
                // }

                $msg .= "Cek dashboard untuk detailnya. Semangat! 🔥";

                try {
                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                    $this->info("   -> Rekap Pagi dikirim ke {$user->name}");
                } catch (\Exception $e) {
                    $this->error("   -> Gagal mengirim ke {$user->name}: " . $e->getMessage());
                }

                sleep(1); 
            }
        }
    }

    /**
     * ─── LOGIKA DEADLINE REMINDER (H-1 JAM) ────────────────────────────
     */
    private function processDeadlineReminder(TelegramService $telegram, Carbon $now)
    {
        // ... (KODE LAMA KAMU TETAP SAMA DI SINI) ...
        $oneHourLater = $now->copy()->addMinutes(65); 

        $tasks = ProductivityTask::with('user')
            ->whereHas('user', function($q) {
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
            $this->info("   -> Tidak ada tugas yang mendekati deadline (H-1) saat ini.");
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
                    $task->update(['is_reminded_h_1' => true]);
                    $this->info("   -> Reminder H-1 Jam untuk tugas '{$task->title}' dikirim ke {$user->name}");
                } catch (\Exception $e) {
                    $this->error("   -> Gagal mengirim ke {$user->name}: " . $e->getMessage());
                }

                sleep(1);
            }
        }
    }

    /**
     * ─── LOGIKA BARU: EXACT REMINDER (PAS WAKTUNYA) ────────────────────
     */
    private function processExactReminder(Carbon $now)
    {
        $targetExact = $now->format('Y-m-d H:i'); // Menit persis saat ini

        // Ambil tugas/pengingat yang belum selesai dan waktunya pas di menit ini
        $tasks = ProductivityTask::with('user')
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('deadline_at')
            ->whereRaw("DATE_FORMAT(deadline_at, '%Y-%m-%d %H:%i') = ?", [$targetExact])
            ->get();

        if ($tasks->isEmpty()) {
            $this->info("   -> Tidak ada pengingat persis di menit ini.");
            return;
        }

        $token = env('TELEGRAM_BOT_TOKEN');
        $baseUrl = "https://api.telegram.org/bot{$token}/sendMessage";

        foreach ($tasks as $task) {
            $user = $task->user;

            if ($user && !empty($user->telegram_chat_id)) {
                $isReminderCommand = ($task->tag === '⏰ Pengingat');
                
                $msg = $isReminderCommand ? "⏰ <b>PENGINGAT WAKTUNYA TIBA!</b>\n\n" : "🚨 <b>DEADLINE TIBA!</b>\n\n";
                $msg .= "📌 <b>{$task->title}</b>\n";
                $msg .= "Ayo, segera eksekusi sekarang!";

                // Bikin tombol Interaktif "Tandai Selesai"
                $keyboard = [
                    'inline_keyboard' => [
                        [ ['text' => '✅ Tandai Selesai', 'callback_data' => "complete_task_{$task->id}"] ]
                    ]
                ];

                try {
                    Http::post($baseUrl, [
                        'chat_id' => $user->telegram_chat_id,
                        'text' => $msg,
                        'parse_mode' => 'HTML',
                        'reply_markup' => json_encode($keyboard)
                    ]);
                    $this->info("   -> Exact Reminder untuk '{$task->title}' dikirim ke {$user->name}");
                } catch (\Exception $e) {
                    $this->error("   -> Gagal mengirim Exact Reminder ke {$user->name}: " . $e->getMessage());
                }
                
                sleep(1);
            }
        }
    }
}