<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\Kegiatan;
use App\Services\TelegramService;
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

    public function bookRuang(Request $request, TelegramService $telegram)
    {
        $request->merge(['user_id' => auth()->id()]);

        $rules = [
            'nama_kegiatan'   => 'required|string',
            'jenis_kegiatan'  => 'required|in:Kegiatan Ormawa,Seminar Proposal,Sidang Skripsi,Rapat,Lomba,PHL,Kuliah Tamu,Lainnya',
            'ruangan_id'      => 'required|exists:ruangan,id',
            'waktu_mulai'     => 'required|date_format:Y-m-d H:i',
            'waktu_selesai'   => 'required|date_format:Y-m-d H:i|after:waktu_mulai',
            'nama_pic'        => ['required', 'string'],
            'nomor_telepon'   => ['required', 'string', 'regex:/^0[0-9]+$/', 'min:9', 'max:15'],
            'deskripsi'       => 'nullable|string',
        ];

        if (!auth()->user()->isAdmin()) {
            $rules['surat_izin'] = 'required|file|mimes:pdf|max:2048';
        }

        $validated = $request->validate($rules);

        $serviceFormat = config('panel.date_format', 'd M Y') . ' ' . config('panel.time_format', 'H:i');
        $startConverted = Carbon::createFromFormat('Y-m-d H:i', $validated['waktu_mulai'])->format($serviceFormat);
        $endConverted = Carbon::createFromFormat('Y-m-d H:i', $validated['waktu_selesai'])->format($serviceFormat);

        if ($this->eventService->isRoomTaken([
            'ruangan_id' => $validated['ruangan_id'],
            'waktu_mulai' => $startConverted,
            'waktu_selesai' => $endConverted,
        ])) {
            return redirect()->back()->withInput($request->input())->withErrors('Ruangan ini tidak tersedia pada waktu tersebut.');
        }

        $suratIzinPath = null;
        if ($request->hasFile('surat_izin')) {
            $suratIzinPath = $request->file('surat_izin')->store('surat_izin', 'public');
        }

        $data = $validated;
        $data['user_id'] = auth()->id();
        $data['surat_izin'] = $suratIzinPath;
        $data['waktu_mulai'] = $startConverted;
        $data['waktu_selesai'] = $endConverted;
        $data['status'] = auth()->user()->isAdmin() ? 'disetujui' : 'belum_disetujui';

        $kegiatan = Kegiatan::create($data);

        \App\Models\KegiatanHistory::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'note' => null,
            'meta' => json_encode(['borrower_id' => $kegiatan->user_id]),
            'created_at' => $kegiatan->created_at,
        ]);

        try {
            $adminGroupId = env('TELEGRAM_ADMIN_GROUP_ID');
            $namaOrmawa = auth()->user()->name;
            if ($adminGroupId) {
                $msg = "🆕 <b>Permohonan Kegiatan Baru</b>\n\n" .
                    "Oleh: <b>{$request->nama_pic}</b> ({$namaOrmawa})\n" .
                    "No. Whatsapp: <b>{$request->nomor_telepon}</b>\n" .
                    "Kegiatan: {$kegiatan->nama_kegiatan}\n" .
                    "Ruang: " . ($kegiatan->ruangan->nama ?? 'Unknown') . "\n" .
                    "Waktu: " . Carbon::parse($kegiatan->waktu_mulai)->format('d M Y H:i') . "\n\n" .
                    "Mohon segera dicek di Aplikasi Layanan Sarpras (PinjamRuang).";

                $telegram->sendMessage($adminGroupId, $msg);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal kirim notif telegram (landing): ' . $e->getMessage());
        }

        return redirect()->route('landing')->with('success', 'Proses book ruang berhasil dibuat.');
    }
}
