<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Services\EventService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LandingController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $ruangan = collect(); 

        if ($request->filled(['waktu_mulai', 'waktu_selesai', 'kapasitas'])) {
            
            // 1. Validasi Input: Format 'Y-m-d H:i' (TANPA DETIK)
            $request->validate([
                'waktu_mulai'   => 'required|date_format:Y-m-d H:i',
                'waktu_selesai' => 'required|date_format:Y-m-d H:i|after:waktu_mulai',
                'kapasitas'     => 'required|integer|min:1',
            ]);

            // 2. Format Tujuan untuk EventService
            $serviceFormat = config('panel.date_format', 'd M Y') . ' ' . config('panel.time_format', 'H:i');

            // 3. Konversi: Input (Y-m-d H:i) -> Service Format
            try {
                // Parse menggunakan format TANPA DETIK
                $startConverted = Carbon::createFromFormat('Y-m-d H:i', $request->input('waktu_mulai'))
                                    ->format($serviceFormat);
                
                $endConverted   = Carbon::createFromFormat('Y-m-d H:i', $request->input('waktu_selesai'))
                                    ->format($serviceFormat);
            } catch (\Exception $e) {
                return back()->withErrors(['waktu_mulai' => 'Format tanggal salah.']);
            }

            // 4. Query Ruangan & Filter (Sama seperti sebelumnya)
            $allRooms = Ruangan::where('kapasitas', '>=', $request->input('kapasitas'))
                ->where('is_active', true)
                ->get();

            $ruangan = $allRooms->filter(function ($r) use ($request, $startConverted, $endConverted) {
                $requestDataForService = [
                    'ruangan_id'      => $r->id,
                    'waktu_mulai'     => $startConverted,
                    'waktu_selesai'   => $endConverted,
                    'tipe_berulang'   => $request->input('tipe_berulang') ?? null,
                    'berulang_sampai' => $request->input('berulang_sampai') ?? null,
                ];
                return !$this->eventService->isRoomTaken($requestDataForService);
            });

        } else {
            $ruangan = Ruangan::where('is_active', true)->orderBy('nama', 'asc')->get();
        }

        return view('landing', ['ruangan' => $ruangan]);
    }
}