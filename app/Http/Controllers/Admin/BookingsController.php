<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Services\EventService;
use App\Services\SikApplicationService;
use Illuminate\Http\Request;
use App\Mail\KegiatanNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\Kegiatan;
use App\Models\SikApplication;
use App\Services\TelegramService;
use Carbon\Carbon;


class BookingsController extends Controller
{
    protected $eventService;
    protected $sikService;

    public function __construct(EventService $eventService, SikApplicationService $sikService)
    {
        $this->eventService = $eventService;
        $this->sikService = $sikService;
    }

    public function cariRuang(Request $request)
    {
        // Ganti nama variabel agar konsisten
        $ruangan = collect(); // Mulai dengan koleksi kosong

        // Cek apakah ada filter waktu dan kapasitas yang diisi
        if ($request->filled(['waktu_mulai', 'waktu_selesai', 'kapasitas'])) {
            // --- LOGIKA PENCARIAN (FILTER) ---
            $request->validate([
                'waktu_mulai' => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'waktu_selesai' => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format') . '|after:waktu_mulai',
                'kapasitas' => 'required|integer|min:1',
            ]);

            // Gunakan logika filter Anda yang sudah ada
            $ruangan = Ruangan::where('kapasitas', '>=', $request->input('kapasitas'))
                ->where('is_active', true)
                ->get()
                ->filter(function ($r) use ($request) {
                    $requestData = [
                        'ruangan_id'      => $r->id,
                        'waktu_mulai'     => $request->input('waktu_mulai'),
                        'waktu_selesai'   => $request->input('waktu_selesai'),
                        'tipe_berulang'   => $request->input('tipe_berulang'),
                        'berulang_sampai' => $request->input('berulang_sampai'),
                    ];
                    return !$this->eventService->isRoomTaken($requestData);
                });
        } else {
            $ruangan = Ruangan::where('is_active', true)->orderBy('nama', 'asc')->get();
        }

        return view('admin.bookings.cari', ['ruangan' => $ruangan]);
    }

    // Method bookRuang Anda tidak perlu diubah
    public function bookRuang(Request $request, TelegramService $telegram)
    {
        $user = auth()->user();
        $request->merge([ 'user_id' => auth()->id() ]);

        $rules = [
            'nama_kegiatan'   => 'required',
            'jenis_kegiatan'  => 'required|string',
            'ruangan_id'      => 'required',
            'waktu_mulai'     => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            'waktu_selesai'   => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format') . '|after:waktu_mulai',
            'nama_pic'      => ['required', 'string'],
            'nomor_telepon' => [
                                    'required',
                                    'string',
                                    'regex:/^0[0-9]+$/',
                                    'min:9',
                                    'max:15',
                                ],
            'sik_application_id' => ['nullable', 'integer', 'exists:sik_applications,id'],
            'override_reason' => ['nullable', 'string', 'max:1000'],

        ];

        if (!$user->isAdmin()) {
            // Operator Ormawa wajib memakai SIK terbit sebagai tiket peminjaman.
            if ($user->ormawas()->exists()) {
                $rules['sik_application_id'] = ['required', 'integer', 'exists:sik_applications,id'];
            } else {
                // fallback untuk user non-ormawa legacy
                $rules['surat_izin'] = 'required|file|mimes:pdf|max:2048';
            }
        }

        $request->validate($rules);

        if ($user->isAdmin()
            && $request->input('jenis_kegiatan') === 'Kegiatan Ormawa'
            && empty($request->input('sik_application_id'))
            && empty($request->input('override_reason'))) {
            return redirect()->back()->withInput($request->input())->withErrors('Alasan override wajib diisi untuk kegiatan ormawa tanpa SIK.');
        }

        if ($this->eventService->isRoomTaken($request->all())) {
            return redirect()->back()->withInput($request->input())->withErrors('Ruangan ini tidak tersedia pada waktu tersebut.');
        }
        
        $suratIzinPath = null;
        if ($request->hasFile('surat_izin')) {
            $suratIzinPath = $request->file('surat_izin')->store('surat_izin', 'public');
        }

        $sikId = $request->input('sik_application_id');
        if (!$user->isAdmin() && $sikId) {
            $sik = SikApplication::findOrFail($sikId);

            $start = Carbon::createFromFormat(
                config('panel.date_format') . ' ' . config('panel.time_format'),
                $request->input('waktu_mulai')
            );
            $end = Carbon::createFromFormat(
                config('panel.date_format') . ' ' . config('panel.time_format'),
                $request->input('waktu_selesai')
            );

            [$canUse, $message] = $this->sikService->canBeUsedForBooking($sik, $user, $start, $end);
            if (! $canUse) {
                return redirect()->back()->withInput($request->input())->withErrors($message);
            }
        }

        $data = $request->all();
        $data['surat_izin'] = $suratIzinPath; 
        $data['status'] = $user->isAdmin() ? 'disetujui' : 'belum_disetujui';

        if ($user->isAdmin() && $request->input('jenis_kegiatan') === 'Kegiatan Ormawa' && empty($request->input('sik_application_id'))) {
            $data['is_admin_override_sik'] = true;
            $data['override_reason'] = $request->input('override_reason');
        }

        $kegiatan = Kegiatan::create($data);

        // Buat history untuk entri yang baru dibuat agar riwayat tampil di halaman show
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
                       "No. Whatsapp: <b>{$request->nomor_telepon}</b>\n".
                       "Kegiatan: {$kegiatan->nama_kegiatan}\n" .
                       "Ruang: " . ($kegiatan->ruangan->nama ?? 'Unknown') . "\n" .
                       "Waktu: " . \Carbon\Carbon::parse($kegiatan->waktu_mulai)->format('d M Y H:i') . "\n\n" .
                       "Mohon segera dicek di Aplikasi Layanan Sarpras (PinjamRuang).";

                $telegram->sendMessage($adminGroupId, $msg);
            }
        } catch (\Exception $e) {
            \Log::error("Gagal kirim notif telegram: " . $e->getMessage());
        }

        if ($kegiatan->is_admin_override_sik) {
            try {
                $kemahasiswaanGroup = env('TELEGRAM_KEMAHASISWAAN_GROUP_ID');
                if ($kemahasiswaanGroup) {
                    $msgOverride = "⚠️ <b>ADMIN OVERRIDE SIK</b>\n\n" .
                        "Kegiatan: <b>{$kegiatan->nama_kegiatan}</b>\n" .
                        "Pemohon: <b>{$user->name}</b>\n" .
                        "Jenis: {$kegiatan->jenis_kegiatan}\n" .
                        "Alasan: <i>{$kegiatan->override_reason}</i>\n\n" .
                        "Mohon kontrol Kemahasiswaan.";
                    $telegram->sendMessage($kemahasiswaanGroup, $msgOverride);
                }
            } catch (\Exception $e) {
                \Log::error("Gagal kirim notif override SIK: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.kegiatan.index')->with('success','Proses book ruang berhasil dibuat.');
    }
}
