<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kegiatan;
use App\Models\Ruangan;
use App\Models\PermintaanKegiatan;
use App\Models\RiwayatPerjalanan;
use App\Models\Mobil;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // ==========================================
        // 0. SETTING WAKTU
        // ==========================================
        $now = Carbon::now('Asia/Jakarta');

        // ==========================================
        // 1. DATA STATISTIK UTAMA
        // ==========================================
        $ruanganCount = Ruangan::count();
        $kegiatanMenungguCount = Kegiatan::where('status', 'belum_disetujui')->count();
        $kegiatanDisetujuiCount = Kegiatan::where('status', 'disetujui')->count();
        $kegiatanTotalCount = Kegiatan::count();

        // ==========================================
        // 2. DATA WIDGET MONITORING
        // ==========================================

        // ------------------------------------------
        // A. STATUS DRIVER / MOBIL FAKULTAS (1 mobil)
        // ------------------------------------------
        // Ambil 1 mobil fakultas pertama (yang tidak maintenance)
        $mobilFakultas = Mobil::where('status', '!=', 'maintenance')->first();

        $ongoingTrip = null;
        $isMobilOnDuty = false;
        $nextTrip = null;

        if ($mobilFakultas) {

            // Trip yang sedang berlangsung untuk mobil ini
            $ongoingTrip = RiwayatPerjalanan::with(['mobil', 'driver'])
                ->where('mobil_id', $mobilFakultas->id)
                ->where('status', 'berlangsung')
                ->orderBy('waktu_mulai', 'desc')
                ->first();

            $isMobilOnDuty = $ongoingTrip ? true : false;

            // Jadwal terdekat (untuk tampilan "ada booking ke depan")
            $nextTrip = RiwayatPerjalanan::with(['mobil', 'driver'])
                ->where('mobil_id', $mobilFakultas->id)
                ->whereIn('status', ['terjadwal', 'booking'])
                ->where('waktu_mulai', '>', $now)
                ->orderBy('waktu_mulai', 'asc')
                ->first();
        }

        // Data lama (kalau masih ingin list perjalanan ongoing banyak)
        // tapi untuk kasus kamu mobil cuma 1, ini optional
        $ongoingTrips = RiwayatPerjalanan::with(['mobil', 'driver'])
            ->where('status', 'berlangsung')
            ->orderBy('waktu_mulai', 'desc')
            ->get();

        // Untuk badge kecil (legacy)
        // Karena mobil cuma 1, kita bikin mobilReady bernilai 1 kalau standby, 0 kalau on duty
        $mobilReady = $isMobilOnDuty ? 0 : 1;

        // ------------------------------------------
        // B. PERMINTAAN LAYANAN (Pending)
        // ------------------------------------------
        $pendingPermintaan = PermintaanKegiatan::with(['user'])
            ->where('status_permintaan', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // ------------------------------------------
        // C. APPROVAL RUANG (belum disetujui)
        // ------------------------------------------
        $pendingApproval = Kegiatan::with(['ruangan', 'user'])
            ->where('status', 'belum_disetujui')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        // ==========================================
        // 3. JADWAL HARI INI & BESOK
        // ==========================================
        $kegiatanHariIni = Kegiatan::with(['ruangan', 'user'])
            ->whereDate('waktu_mulai', Carbon::today())
            ->orderBy('waktu_mulai')
            ->get();

        $kegiatanBesok = Kegiatan::with(['ruangan', 'user'])
            ->whereDate('waktu_mulai', Carbon::tomorrow())
            ->orderBy('waktu_mulai')
            ->get();

        $jadwalHariIniText = $this->generateJadwalText($kegiatanHariIni, 'Hari Ini');
        $jadwalBesokText = $this->generateJadwalText($kegiatanBesok, 'Besok');

        return view('home', compact(
            'ruanganCount',
            'kegiatanMenungguCount',
            'kegiatanDisetujuiCount',
            'kegiatanTotalCount',

            // Driver card
            'mobilFakultas',
            'ongoingTrip',
            'isMobilOnDuty',
            'nextTrip',

            // legacy
            'ongoingTrips',
            'mobilReady',

            // widgets
            'pendingPermintaan',
            'pendingApproval',

            // jadwal
            'kegiatanHariIni',
            'kegiatanBesok',
            'jadwalHariIniText',
            'jadwalBesokText'
        ));
    }

    private function generateJadwalText($kegiatans, $title)
    {
        if ($kegiatans->isEmpty()) return "";

        $text = "*JADWAL PEMAKAIAN RUANG FTMM - " . strtoupper($title) . "*\n\n";

        $grouped = $kegiatans->groupBy(function ($item) {
            return $item->ruangan->nama ?? 'Lainnya';
        });

        foreach ($grouped as $ruang => $items) {
            $text .= "*$ruang*\n";
            foreach ($items as $k) {
                $jam = Carbon::parse($k->waktu_mulai)->format('H:i') . '-' . Carbon::parse($k->waktu_selesai)->format('H:i');
                $peminjam = $k->user ? $k->user->name : ($k->nama_pic ?? 'Tanpa Nama');
                $text .= "- $jam: {$k->nama_kegiatan} ({$peminjam})\n";
            }
            $text .= "\n";
        }

        return $text;
    }
}
