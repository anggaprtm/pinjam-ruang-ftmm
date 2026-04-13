<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ProductivityNote;
use App\Models\ProductivityTask;
use Carbon\Carbon;

class TelegramBotListen extends Command
{
    /**
     * Nama command untuk dijalankan di terminal
     */
    protected $signature = 'telegram:listen';

    /**
     * Deskripsi command
     */
    protected $description = 'Menjalankan bot Telegram listener (Long Polling)';

    /**
     * Token dan URL
     */
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
        $this->info("🤖 Bot sedang berjalan... Menunggu perintah /start, /note, atau /task");
        $this->info("Tekan Ctrl+C untuk berhenti.");

        $offset = 0; // Penanda pesan terakhir yang dibaca

        // Looping selamanya (sampai dimatikan manual)
        while (true) {
            try {
                // 1. Minta update ke Telegram (timeout 30 detik biar hemat request)
                $response = Http::timeout(40)->get("{$this->baseUrl}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 30 
                ]);

                if ($response->successful()) {
                    $result = $response->json('result');

                    foreach ($result as $update) {
                        // Update offset biar pesan ini gak dibaca ulang
                        $offset = $update['update_id'] + 1;

                        // Cek apakah ini pesan teks biasa
                        if (isset($update['message']['text'])) {
                            $chatId = $update['message']['chat']['id'];
                            $text = $update['message']['text'];
                            $firstName = $update['message']['from']['first_name'] ?? 'User';

                            $this->info("Pesan dari $firstName: $text");

                            // 2. LOGIC BALASAN 2 ARAH
                            $textLower = strtolower($text);

                            if ($textLower === '/start') {
                                $this->replyWithId($chatId, $firstName);
                            } elseif ($textLower === '/help' || $textLower === '/menu') {
                                $this->sendHelpMenu($chatId);
                            } elseif (str_starts_with($textLower, '/note ')) {
                                $this->handleNoteCommand($chatId, $text);
                            } elseif (str_starts_with($textLower, '/task ')) {
                                $this->handleTaskCommand($chatId, $text);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error koneksi: " . $e->getMessage());
                sleep(5); // Tunggu bentar kalau error koneksi
            }
        }
    }

    /**
     * Fungsi kirim balasan ID
     */
    private function replyWithId($chatId, $name)
    {
        $message = "Halo, <b>$name</b>! 👋\n\n" .
                   "ID Telegram kamu adalah: <code>$chatId</code>\n\n" .
                   "Silakan copy angka di atas dan paste ke halaman Profil di website. (Abaikan ini, karena sistem notifikasi akan aktif otomatis)";

        Http::post("{$this->baseUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);

        $this->info("--> Membalas ID ke $name ($chatId)");
    }

    /**
     * Fungsi kirim menu bantuan
     */
    private function sendHelpMenu($chatId)
    {
        $msg = "🤖 <b>Menu Produktivitas Bot</b>\n\n";
        $msg .= "Kirim perintah berikut untuk menambahkan data langsung ke Dashboard web:\n\n";
        $msg .= "📝 <b>Catatan Cepat:</b>\n<code>/note [isi catatan]</code>\n<i>Contoh: /note Jangan lupa print surat tugas besok pagi</i>\n\n";
        $msg .= "✅ <b>Tugas Baru (Deadline Hari Ini):</b>\n<code>/task [judul tugas]</code>\n<i>Contoh: /task Setup server e-learning</i>";

        Http::post("{$this->baseUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);
    }

    /**
     * Fungsi simpan Note dari Telegram
     */
    private function handleNoteCommand($chatId, $text)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) {
            $this->replyUnregistered($chatId);
            return;
        }

        // Ambil text setelah kata '/note ' (6 karakter)
        $content = trim(substr($text, 6));
        if (empty($content)) return;

        $colors = ['#fef08a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#fed7aa', '#e9d5ff'];
        $bgColor = $colors[array_rand($colors)];

        ProductivityNote::create([
            'user_id' => $user->id,
            'content' => $content,
            'bg_color' => $bgColor,
        ]);

        $msg = "✅ <b>Catatan berhasil disimpan!</b>\nSilakan cek dinding catatan di Dashboard Anda.";
        Http::post("{$this->baseUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);
        
        $this->info("--> Catatan disimpan untuk {$user->name}");
    }

    /**
     * Fungsi simpan Task dari Telegram dengan Parsing Waktu
     */
    private function handleTaskCommand($chatId, $text)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) {
            $this->replyUnregistered($chatId);
            return;
        }

        // Ambil text setelah kata '/task '
        $rawInput = trim(substr($text, 6));
        if (empty($rawInput)) return;

        $title = $rawInput;
        $deadline = null;
        $replyMsgTime = "<b>tanpa tenggat waktu</b> (opsional).";

        // Logic Parsing Sintaks Waktu (menggunakan @)
        if (str_contains($rawInput, '@')) {
            $parts = explode('@', $rawInput);
            $title = trim($parts[0]);
            $timeStr = strtolower(trim($parts[1])); 

            $parsedDate = Carbon::today();
            
            // Cek apakah ada kata "besok"
            if (str_starts_with($timeStr, 'besok')) {
                $parsedDate = Carbon::tomorrow();
                $timeStr = trim(str_replace('besok', '', $timeStr));
            }

            // Cek format jam HH:MM atau HH.MM (contoh: 16:00 atau 09.30)
            if (preg_match('/^([01]?[0-9]|2[0-3])[:.]([0-5][0-9])/', $timeStr, $matches)) {
                $parsedDate->setHour((int)$matches[1])->setMinute((int)$matches[2]);
            } else {
                // Default jika tidak ada jam (misal hanya "@besok"), set ke akhir hari
                $parsedDate->setHour(23)->setMinute(59);
            }

            $deadline = $parsedDate->format('Y-m-d H:i:s');
            $replyMsgTime = "dengan tenggat waktu <b>" . $parsedDate->format('d M Y, H:i') . " WIB</b>.";
        }

        ProductivityTask::create([
            'user_id' => $user->id,
            'title' => $title,
            'priority' => 'medium',
            'tag' => 'Dari Telegram', 
            'deadline_at' => $deadline, // Sekarang dinamis / bisa null
        ]);

        $msg = "✅ <b>Tugas berhasil ditambahkan!</b>\nDisimpan $replyMsgTime";
        Http::post("{$this->baseUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);
        
        $this->info("--> Tugas disimpan untuk {$user->name}");
    }

    /**
     * Fungsi tolak akses jika Telegram ID belum terdaftar di Web
     */
    private function replyUnregistered($chatId)
    {
        $msg = "❌ Akun Telegram Anda belum ditautkan ke sistem.\n\nSilakan masukkan ID Telegram <code>$chatId</code> di menu Profil pada Dashboard web terlebih dahulu.";
        Http::post("{$this->baseUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);
    }
}