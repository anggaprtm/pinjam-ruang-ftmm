<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kegiatan;
use App\Models\Ruangan;
use App\Models\PermintaanKegiatan;
use App\Models\RiwayatPerjalanan;
use App\Models\Mobil;
use App\Models\AsetFakultas;
use App\Models\AbsensiLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $now = Carbon::now('Asia/Jakarta');
        $hariIniDate = Carbon::today()->format('Y-m-d');

        // ==========================================
        // 1. STATISTIK SARPRAS & KEGIATAN
        // ==========================================
        $ruanganCount = Ruangan::count();
        $kegiatanMenungguCount = Kegiatan::whereNotIn('status', ['disetujui', 'ditolak'])->count();
        $kegiatanDisetujuiCount = Kegiatan::where('status', 'disetujui')->count();
        $kegiatanTotalCount = Kegiatan::count();

        // ==========================================
        // 2. STATISTIK ASET & PEMINJAMAN BARANG
        // ==========================================
        $totalAset = AsetFakultas::count();
        $asetRusak = AsetFakultas::whereIn('kondisi', ['Rusak Ringan', 'Rusak Berat'])->count();
        $barangDipinjam = DB::table('barang_kegiatan')->where('status', 'dipinjam')->sum('jumlah') ?? 0;

        // ==========================================
        // 3. STATISTIK ABSENSI HARI INI
        // ==========================================
        $hadirHariIni = AbsensiLog::where('tanggal', $hariIniDate)->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatHariIni = AbsensiLog::where('tanggal', $hariIniDate)->where('status', 'terlambat')->count();

        // ==========================================
        // 4. DATA WIDGET MONITORING (PANEL BAWAH)
        // ==========================================

        // A. STATUS DRIVER / MOBIL FAKULTAS
        $mobilFakultas = Mobil::where('status', '!=', 'maintenance')->first();
        $ongoingTrip = null;
        $isMobilOnDuty = false;
        $nextTrip = null;

        if ($mobilFakultas) {
            $ongoingTrip = RiwayatPerjalanan::with(['mobil', 'driver'])
                ->where('mobil_id', $mobilFakultas->id)
                ->where('status', 'berlangsung')
                ->orderBy('waktu_mulai', 'desc')
                ->first();

            $isMobilOnDuty = $ongoingTrip ? true : false;

            $nextTrip = RiwayatPerjalanan::with(['mobil', 'driver'])
                ->where('mobil_id', $mobilFakultas->id)
                ->whereIn('status', ['terjadwal', 'booking'])
                ->where('waktu_mulai', '>', $now)
                ->orderBy('waktu_mulai', 'asc')
                ->first();
        }

        $ongoingTrips = RiwayatPerjalanan::with(['mobil', 'driver'])
            ->where('status', 'berlangsung')
            ->orderBy('waktu_mulai', 'desc')
            ->get();

        $mobilReady = $isMobilOnDuty ? 0 : 1;

        // B. PERMINTAAN LAYANAN & APPROVAL
        $pendingPermintaan = PermintaanKegiatan::with(['user'])->where('status_permintaan', 'pending')->latest()->take(10)->get();
        $pendingApproval = Kegiatan::with(['ruangan', 'user'])->whereNotIn('status', ['disetujui', 'ditolak'])->orderBy('created_at', 'asc')->take(10)->get();

        // ==========================================
        // 5. JEJAK AKTIVITAS (TIMELINE)
        // ==========================================
        $timeline = collect();

       // Ambil Kegiatan Terbaru
        $recentKegiatans = Kegiatan::with(['user', 'ruangan'])->latest()->take(5)->get();
        foreach($recentKegiatans as $item) {
            $ruang = $item->ruangan->nama ?? 'Ruangan';
            $user = $item->user->name ?? 'User';
            $namaKegiatan = $item->nama_kegiatan ?? 'Tanpa Nama';

            // 1. Jika ini adalah hasil Sinkronisasi Otomatis Google Calendar
            if ($user === '[Admin] Sinkron Google Calendar') {
                $text  = "<b>Sistem</b> melakukan sinkronisasi Google Calendar penggunaan <b>{$ruang}</b> dengan kegiatan: <i>{$namaKegiatan}</i>.";
                $icon  = 'fas fa-sync-alt'; // Pakai ikon sync (putar)
                $color = 'bg-dark text-white'; // Pakai warna gelap agar beda dari user biasa
            } 
            // 2. Jika statusnya sudah disetujui (diinput manual oleh Admin)
            elseif ($item->status === 'disetujui') {
                $text  = "<b>Admin</b> menjadwalkan pemakaian <b>{$ruang}</b> untuk <b>{$user}</b> dengan kegiatan: <i>{$namaKegiatan}</i>.";
                $icon  = 'fas fa-calendar-check';
                $color = 'bg-success'; // Warna hijau
            } 
            // 3. Jika statusnya belum disetujui (User/Mahasiswa yang mengajukan)
            else {
                $text  = "<b>{$user}</b> mengajukan pemakaian <b>{$ruang}</b> untuk kegiatan: <i>{$namaKegiatan}</i>.";
                $icon  = 'fas fa-calendar-plus';
                $color = 'bg-primary'; // Warna biru
            }

            $timeline->push([
                'time'  => $item->created_at,
                'icon'  => $icon,
                'color' => $color,
                'text'  => $text,
            ]);
        }

        // Ambil Permintaan Layanan Terbaru
        $recentPermintaan = PermintaanKegiatan::with('user')->latest()->take(5)->get();
        foreach($recentPermintaan as $item) {
            $user = $item->user->name ?? 'User';
            $timeline->push([
                'time'  => $item->created_at,
                'icon'  => 'fas fa-concierge-bell',
                'color' => 'bg-warning text-dark',
                'text'  => "<b>{$user}</b> meminta layanan: {$item->nama_kegiatan}.",
            ]);
        }

        // Ambil Aset Terbaru
        $recentAset = AsetFakultas::latest()->take(5)->get();
        foreach($recentAset as $item) {
            $timeline->push([
                'time'  => $item->created_at,
                'icon'  => 'fas fa-box-open',
                'color' => 'bg-primary',
                'text'  => "Aset baru ditambahkan: <b>{$item->nama_barang}</b>.",
            ]);
        }

        // Ambil Perjalanan Mobil Terbaru
        $recentTrip = RiwayatPerjalanan::with('driver')->latest('created_at')->take(5)->get();
        foreach($recentTrip as $item) {
            $driver = $item->driver->name ?? 'Driver';
            $timeline->push([
                'time'  => $item->created_at,
                'icon'  => 'fas fa-car',
                'color' => 'bg-info text-white',
                'text'  => "<b>{$driver}</b> dijadwalkan ke <b>{$item->tujuan}</b>.",
            ]);
        }

        // Urutkan semua aktivitas dari yang paling baru, ambil 10 teratas
        $activities = $timeline->sortByDesc('time')->take(10);

        // ==========================================
        // 6. JADWAL HARI INI & BESOK
        // ==========================================
        $kegiatanHariIni = Kegiatan::with(['ruangan', 'user'])->whereDate('waktu_mulai', Carbon::today())->orderBy('waktu_mulai')->get();
        $kegiatanBesok = Kegiatan::with(['ruangan', 'user'])->whereDate('waktu_mulai', Carbon::tomorrow())->orderBy('waktu_mulai')->get();
        $jadwalHariIniText = $this->generateJadwalText($kegiatanHariIni, 'Hari Ini');
        $jadwalBesokText = $this->generateJadwalText($kegiatanBesok, 'Besok');

        return view('home', compact(
            'ruanganCount', 'kegiatanMenungguCount', 'kegiatanDisetujuiCount', 'kegiatanTotalCount',
            'totalAset', 'asetRusak', 'barangDipinjam', 'hadirHariIni', 'terlambatHariIni',
            'mobilFakultas', 'ongoingTrip', 'isMobilOnDuty', 'nextTrip', 'ongoingTrips', 'mobilReady',
            'pendingPermintaan', 'pendingApproval', 'activities',
            'kegiatanHariIni', 'kegiatanBesok', 'jadwalHariIniText', 'jadwalBesokText'
        ));
    }

    private function generateJadwalText($kegiatans, $title)
    {
        if ($kegiatans->isEmpty()) return "";
        $text = "*JADWAL PEMAKAIAN RUANG FTMM - " . strtoupper($title) . "*\n\n";
        $grouped = $kegiatans->groupBy(function ($item) { return $item->ruangan->nama ?? 'Lainnya'; });

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