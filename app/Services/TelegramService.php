<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Kirim pesan teks ke chat_id tertentu
     */
    public function sendMessage($chatId, $message)
    {
        if (empty($this->token) || empty($chatId)) {
            return; // Skip jika tidak ada config
        }

        try {
            $response = Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML', // Biar bisa bold/italic
            ]);

            if ($response->failed()) {
                Log::error('Telegram Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Telegram Exception: ' . $e->getMessage());
        }
    }
}