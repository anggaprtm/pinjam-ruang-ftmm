<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CentralTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CentralTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = CentralTicket::query();

        // Logic Filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%')
                  ->orWhere('reporter_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $tickets = $query->paginate(10)->withQueryString();

        // Statistik untuk Stat Cards
        $stats = [
            'total'   => CentralTicket::count(),
            'aktif'   => CentralTicket::whereIn('status', ['open', 'onprogress'])->count(),
            'selesai' => CentralTicket::where('status', 'resolved')->count(),
            'ditolak' => CentralTicket::where('status', 'rejected')->count(),
        ];

        return view('admin.central-ticket.index', compact('tickets', 'stats'));
    }

    public function storeReply(Request $request, $id)
    {
        // Validasi input dari form
        $request->validate([
            'content' => 'required|string',
        ]);

        $ticket = CentralTicket::findOrFail($id);
        $adminNexus = auth()->user();

        try {
            // 1. Tembak API TickTrack (Ganti URL ini dengan IP lokal TickTrack ATAU Ngrok TickTrack)
            $response = Http::withHeaders([
                'X-Nexus-Token' => 'rahasia-kita-nexus-123'
            ])->post("http://10.16.10.19/api/external/ticket/{$ticket->code}/reply", [ // Sesuaikan URL TickTrack-mu
                'replier_name' => $adminNexus->name,
                'content'      => $request->content,
                'status'       => $request->status ?? $ticket->status,
            ]);

            // 2. Cek apakah TickTrack menerima dengan status 200 OK
            if ($response->successful()) {
                // Simpan juga di DB Nexus sebagai riwayat percakapan
                $ticket->replies()->create([
                    'replier_name' => $adminNexus->name,
                    'replier_role' => 'admin_nexus',
                    'content'      => $request->content,
                ]);

                // Update status di tiket Nexus lokal
                if ($request->has('status')) {
                    $ticket->update(['status' => $request->status]);
                }

                return back()->with('success', 'Balasan berhasil dikirim dan disinkronkan!');
            } 
            
            // JARING PENGAMAN: Kalau TickTrack nolak (misal error 500)
            return back()->with('error', 'Gagal sinkron. TickTrack merespon dengan error: ' . $response->status());

        } catch (\Exception $e) {
            // JARING PENGAMAN: Kalau server TickTrack mati / URL salah
            return back()->with('error', 'Koneksi ke TickTrack terputus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $ticket = CentralTicket::findOrFail($id);
        return view('admin.central-ticket.show', compact('ticket'));
    }
}