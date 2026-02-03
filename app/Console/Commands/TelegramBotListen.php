<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $this->info("🤖 Bot sedang berjalan... Menunggu pesan /start");
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

                            // 2. LOGIC BALASAN
                            if ($text === '/start') {
                                $this->replyWithId($chatId, $firstName);
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
     * Fungsi kirim balasan
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
}