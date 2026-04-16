<?php

namespace App\Http\Controllers;

use App\Models\CentralTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookTicketController extends Controller
{
    public function receiveTicket(Request $request)
    {
        // 1. Verifikasi Token Keamanan (Harus sama dengan yang di TickTrack)
        $token = $request->bearerToken();
        if ($token !== 'rahasia-kita-nexus-123') {
            Log::warning('Webhook Ditolak: Token tidak valid atau tidak ada.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Simpan atau Update Data
        try {
            // Kita pakai updateOrCreate biar aman. 
            // Kalau ID tiket sudah ada, dia bakal update datanya. Kalau belum ada, dia bikin baru.
            // Ini sangat berguna nanti saat kita implementasi sinkronisasi "update status".
            $ticket = CentralTicket::updateOrCreate(
                ['original_ticket_id' => $request->original_ticket_id], // Kunci pencarian
                [
                    'code' => $request->code,
                    'reporter_name' => $request->reporter_name,
                    'reporter_email' => $request->reporter_email,
                    'is_guest' => $request->is_guest,
                    'title' => $request->title,
                    'category' => $request->category,
                    'description' => $request->description,
                    'priority' => $request->priority,
                    'status' => $request->status,
                    'attachment_url' => $request->attachment_url,
                    'created_at' => $request->created_at, // Biar jam pembuatannya sinkron
                ]
            );

            Log::info("Webhook Sukses: Tiket {$ticket->code} berhasil disimpan ke database Nexus.");

            return response()->json([
                'message' => 'Data berhasil diterima dan disimpan oleh Nexus',
            ], 200);

        } catch (\Exception $e) {
            Log::error("Webhook Gagal Simpan DB Nexus: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data di Nexus'], 500);
        }
    }
}