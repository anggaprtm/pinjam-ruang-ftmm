<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\ProductivityNote;
use App\Models\ProductivityTask;
use Carbon\Carbon;

class TelegramBotListen extends Command
{
    protected $signature = 'telegram:listen';
    protected $description = 'Menjalankan bot Telegram listener (Long Polling)';

    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function handle()
    {
        $this->info("🤖 Bot sedang berjalan... Menunggu perintah /start, /note, /task, atau /remind");
        $this->info("Tekan Ctrl+C untuk berhenti.");

        $offset = 0;

        while (true) {
            try {
                $response = Http::timeout(40)->get("{$this->baseUrl}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 30 
                ]);

                if ($response->successful()) {
                    $result = $response->json('result');

                    foreach ($result as $update) {
                        $offset = $update['update_id'] + 1;

                        // 1. HANDLE PESAN TEKS BIASA
                        if (isset($update['message']['text'])) {
                            $chatId = $update['message']['chat']['id'];
                            $text = $update['message']['text'];
                            $firstName = $update['message']['from']['first_name'] ?? 'User';

                            $textLower = strtolower($text);

                            if ($textLower === '/start') {
                                $this->replyWithId($chatId, $firstName);
                            } elseif ($textLower === '/help' || $textLower === '/menu') {
                                $this->sendHelpMenu($chatId);
                            } elseif (str_starts_with($textLower, '/note ')) {
                                $this->handleNoteCommand($chatId, $text);
                            } elseif (str_starts_with($textLower, '/task ')) {
                                $this->handleTaskOrRemind($chatId, $text, 'task');
                            } elseif (str_starts_with($textLower, '/remind ')) {
                                $this->handleTaskOrRemind($chatId, $text, 'remind');
                            } elseif ($textLower === '/list' || $textLower === '/hariini') {
                                $this->handleListCommand($chatId); // FITUR BARU
                            }
                        } 
                        // 2. HANDLE KLIK TOMBOL (CALLBACK QUERY)
                        elseif (isset($update['callback_query'])) {
                            $this->handleCallbackQuery($update['callback_query']);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error koneksi: " . $e->getMessage());
                sleep(5); 
            }
        }
    }

    private function replyWithId($chatId, $name)
    {
        $message = "Halo, <b>$name</b>! 👋\n\nID Telegram kamu: <code>$chatId</code>\n\nSilakan copy angka di atas dan paste ke halaman Profil di website FTMM-Nexusku.";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML']);
    }

    private function sendHelpMenu($chatId)
    {
        $msg = "🤖 <b>Menu Produktivitas Bot</b>\n\n";
        $msg .= "📝 <b>Catatan Cepat:</b>\n<code>/note [isi]</code>\n\n";
        $msg .= "✅ <b>Tugas Baru:</b>\n<code>/task [judul] @[waktu]</code>\n\n";
        $msg .= "⏰ <b>Pengingat:</b>\n<code>/remind [pesan] @[waktu]</code>\n\n";
        $msg .= "📋 <b>Jadwal Hari Ini:</b>\n<code>/list</code> atau <code>/hariini</code>\n\n";
        $msg .= "<i>Contoh Format Waktu yang didukung:</i>\n";
        $msg .= "- <code>@besok 15:00</code>\n- <code>@lusa</code>\n- <code>@jumat 09.30</code>\n- <code>@15 april 2026</code>\n- <code>@16:00</code> (hari ini)";

        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    private function handleNoteCommand($chatId, $text)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        $content = trim(substr($text, 6));
        if (empty($content)) return;

        $colors = ['#fef08a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#fed7aa', '#e9d5ff'];
        ProductivityNote::create(['user_id' => $user->id, 'content' => $content, 'bg_color' => $colors[array_rand($colors)]]);

        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "✅ <b>Catatan disimpan!</b>", 'parse_mode' => 'HTML']);
    }


    private function handleListCommand($chatId)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        // Cari tugas yang pending dan deadline-nya hari ini atau sebelumnya (terlambat)
        $tasks = ProductivityTask::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->where(function($q) {
                        $q->whereDate('deadline_at', '<=', Carbon::today())
                          ->orWhereNull('deadline_at'); // Termasuk yang tanpa tenggat
                    })
                    ->orderBy('deadline_at', 'asc')
                    ->get();

        if ($tasks->isEmpty()) {
            Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => "🎉 Tidak ada tugas mendesak untuk hari ini. Waktunya bersantai!"
            ]);
            return;
        }

        $msg = "📋 <b>Daftar Tugas Aktif & Mendesak:</b>\n\n";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);

        // Kirim tugas satu per satu dengan tombol Selesai di bawahnya
        foreach ($tasks as $task) {
            $dlText = $task->deadline_at ? Carbon::parse($task->deadline_at)->translatedFormat('d M, H:i') : 'Tanpa Tenggat';
            $taskMsg = "📌 <b>{$task->title}</b>\n⏳ Deadline: $dlText";

            // Bikin tombol Interaktif
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅ Tandai Selesai', 'callback_data' => "complete_task_{$task->id}"]
                    ]
                ]
            ];

            Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $taskMsg,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($keyboard) // Pasang tombol di sini
            ]);
        }
    }


    /**
     * Menangani aksi ketika tombol di Telegram diklik
     */
    private function handleCallbackQuery($callbackQuery)
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data']; // contoh isinya: complete_task_5

        // Jika tombol Selesai diklik
        if (str_starts_with($data, 'complete_task_')) {
            $taskId = str_replace('complete_task_', '', $data);
            
            $task = ProductivityTask::find($taskId);
            if ($task) {
                // Update ke database
                $task->update(['status' => 'completed']);

                // Hapus tombol dari pesan dan ubah teksnya jadi dicoret/selesai
                $newText = "✅ <s>{$task->title}</s>\n<i>Telah diselesaikan via Telegram!</i>";

                Http::post("{$this->baseUrl}/editMessageText", [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $newText,
                    'parse_mode' => 'HTML'
                ]);

                // Beri notif pop-up kecil di layar HP user
                Http::post("{$this->baseUrl}/answerCallbackQuery", [
                    'callback_query_id' => $callbackId,
                    'text' => "Mantap! Tugas selesai 🎉"
                ]);
            }
        }
    }

    /**
     * Handle untuk /task dan /remind dengan Smart Date Parsing
     */
    private function handleTaskOrRemind($chatId, $text, $type)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        $prefixLen = ($type === 'task') ? 6 : 8; // '/task ' atau '/remind '
        $rawInput = trim(substr($text, $prefixLen));
        if (empty($rawInput)) return;

        $title = $rawInput;
        $deadline = null;
        $replyMsgTime = "<b>tanpa tenggat waktu</b>.";

        // Jika menggunakan simbol '@' untuk set waktu
        if (str_contains($rawInput, '@')) {
            $parts = explode('@', $rawInput);
            $title = trim($parts[0]);
            $timeStr = strtolower(trim($parts[1]));

            $parsedDate = $this->parseSmartDate($timeStr);

            if ($parsedDate) {
                $deadline = $parsedDate->format('Y-m-d H:i:s');
                $replyMsgTime = "pada <b>" . $parsedDate->translatedFormat('l, d M Y - H:i') . " WIB</b>.";
            } else {
                $replyMsgTime = "<i>(Format waktu tidak dikenali, diset tanpa tenggat)</i>.";
            }
        }

        // Tentukan Tag dan Prioritas berdasarkan perintah
        $tag = ($type === 'remind') ? '⏰ Pengingat' : 'Dari Telegram';
        $priority = ($type === 'remind') ? 'high' : 'medium';
        $icon = ($type === 'remind') ? '⏰' : '✅';

        ProductivityTask::create([
            'user_id' => $user->id,
            'title' => $title,
            'priority' => $priority,
            'tag' => $tag, 
            'deadline_at' => $deadline,
        ]);

        $msg = "$icon <b>Berhasil ditambahkan!</b>\nDisimpan $replyMsgTime";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    /**
     * NLP Sederhana untuk membaca format hari/tanggal bahasa Indonesia
     */
    private function parseSmartDate($timeStr)
    {
        // Kamus terjemahan ke format English yang dipahami Carbon
        $translate = [
            'besok' => 'tomorrow', 'lusa' => '+2 days',
            'senin' => 'next monday', 'selasa' => 'next tuesday', 'rabu' => 'next wednesday',
            'kamis' => 'next thursday', 'jumat' => 'next friday', 'sabtu' => 'next saturday', 'minggu' => 'next sunday',
            'januari' => 'january', 'februari' => 'february', 'maret' => 'march', 'agustus' => 'august',
            'oktober' => 'october', 'desember' => 'december', 'mei' => 'may', 'agu' => 'aug', 'ags' => 'aug'
        ];

        // Default: Kalau tidak ada jam, set ke 23:59
        $timePart = '23:59';
        $datePart = $timeStr;

        // Ekstrak Jam (Cari format HH:MM atau HH.MM di akhir teks)
        if (preg_match('/([01]?[0-9]|2[0-3])[:.]([0-5][0-9])$/', $timeStr, $timeMatch)) {
            $timePart = $timeMatch[1] . ':' . $timeMatch[2]; // Paksa format pakai titik dua
            $datePart = trim(str_replace($timeMatch[0], '', $timeStr)); // Sisa teks adalah tanggal
        }

        // Jika ternyata tanggal kosong (user cuma ngetik jam, misal "@15:00")
        if (empty($datePart)) {
            $datePart = 'today';
        }

        // Translasi Bahasa Indonesia -> English
        $datePartEng = str_ireplace(array_keys($translate), array_values($translate), $datePart);

        try {
            // Biarkan Carbon yang melakukan keajaiban parsing
            return Carbon::parse("$datePartEng $timePart");
        } catch (\Exception $e) {
            return null; // Jika gagal dibaca sama sekali
        }
    }

    private function replyUnregistered($chatId)
    {
        $msg = "❌ Akun Telegram Anda belum ditautkan.\n\nMasukkan ID <code>$chatId</code> di web.";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }
}