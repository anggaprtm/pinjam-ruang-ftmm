<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\AbsensiLog;
use Carbon\Carbon;

class LogSentEmail
{
    public function handle(MessageSent $event)
    {
        $message = $event->message;
        $headers = $message->getHeaders();

        // 1. Pastikan ini adalah email Peringatan Kedisiplinan
        if ($headers->has('X-Mail-Type') && $headers->get('X-Mail-Type')->getBodyAsString() === 'SP-Kedisiplinan') {
            
            // 2. Ambil User ID dari header
            if ($headers->has('X-User-Id')) {
                $userId = $headers->get('X-User-Id')->getBodyAsString();
                $tanggalHariIni = Carbon::now()->format('Y-m-d');

                // 3. Cari log absensi hari ini untuk user tersebut
                $log = AbsensiLog::where('user_id', $userId)
                                 ->where('tanggal', $tanggalHariIni)
                                 ->first();

                if ($log) {
                    $history = $log->notif_history ?? [];
                    
                    // 4. Update status menjadi SENT (terkirim aktual)
                    $history['email_telat_2x_sent'] = Carbon::now()->format('H:i');
                    
                    $log->notif_history = $history;
                    $log->save();
                }
            }
        }
    }
}