<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SignageController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $today = Carbon::now();
        $todayName = $today->translatedFormat('l');

        // Ambil Filter URL
        $filterLantai = $request->query('lantai');
        $filterGedung = $request->query('gedung');

        // ==========================================
        // 1. QUERY JADWAL KULIAH
        // ==========================================
        $jadwalQuery = JadwalPerkuliahan::where('hari', $todayName)
            ->whereDate('berlaku_mulai', '<=', $today)
            ->whereDate('berlaku_sampai', '>=', $today)
            ->with(['ruangan']);

        // Filter Lantai & Gedung untuk Kuliah
        if ($filterLantai) {
            $jadwalQuery->whereHas('ruangan', function($q) use ($filterLantai) {
                $q->where('lantai', 'LIKE', "%{$filterLantai}%");
            });
        }
        if ($filterGedung) {
            $jadwalQuery->whereHas('ruangan', function($q) use ($filterGedung) {
                $q->where('gedung', 'LIKE', "%{$filterGedung}%");
            });
        }

        $jadwalKuliah = $jadwalQuery->orderBy('waktu_mulai')->get()->map(function ($jadwal) {
            return [
                'title' => $jadwal->mata_kuliah,
                'course_code' => $jadwal->kode_matkul ?? substr($jadwal->program_studi, 0, 2).'-'.$jadwal->id,
                'time' => Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i'),
                'room' => $jadwal->ruangan->nama ?? '-',
                'pic' => $jadwal->dosen ?? '-',
                'type' => 'kuliah',
            ];
        });

        // ==========================================
        // 2. QUERY KEGIATAN (EVENTS) - FIX FILTER DISINI
        // ==========================================
        $kegiatanQuery = Kegiatan::where('status', 'disetujui')
            ->whereDate('waktu_mulai', '>=', $today) // Hanya hari ini (atau >= today terserah policy)
            ->whereNotIn('jenis_kegiatan', ['Rapat', 'Seminar Proposal', 'Sidang Skripsi']) 
            ->with(['ruangan', 'user']);

        // --- FILTER LANTAI DIPASANG DISINI JUGA ---
        if ($filterLantai) {
            $kegiatanQuery->whereHas('ruangan', function($q) use ($filterLantai) {
                $q->where('lantai', 'LIKE', "%{$filterLantai}%");
            });
        }
        // --- FILTER GEDUNG DIPASANG DISINI JUGA ---
        if ($filterGedung) {
            $kegiatanQuery->whereHas('ruangan', function($q) use ($filterGedung) {
                $q->where('gedung', 'LIKE', "%{$filterGedung}%");
            });
        }

        $kegiatan = $kegiatanQuery->orderBy('waktu_mulai')->limit(5)->get()->map(function ($kegiatan) {
            $startTime = Carbon::parse($kegiatan->waktu_mulai);
            $endTime = Carbon::parse($kegiatan->waktu_selesai);
            $timeString = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');

            return [
                'id' => $kegiatan->id,
                'title' => $kegiatan->nama_kegiatan,
                'time' => $timeString,
                'location' => $kegiatan->ruangan->nama ?? 'TBA',
                'speaker' => $kegiatan->user->name ?? $kegiatan->nama_pic,
                
                // Data tambahan untuk UI Events Panel Baru
                'date_day' => $startTime->format('d'),       // Tanggal (20)
                'date_month' => $startTime->translatedFormat('M'), // Bulan (Okt)
                'category' => $kegiatan->jenis_kegiatan,
                // Gambar placeholder unik berdasarkan ID kegiatan
                'image' => $kegiatan->poster ? asset('storage/'.$kegiatan->poster) : '...',
                'type' => 'kegiatan',
            ];
        });

        $sidangQuery = Kegiatan::where('status', 'disetujui')
            ->whereDate('waktu_mulai', $today)
            ->whereIn('jenis_kegiatan', ['Rapat', 'Seminar Proposal', 'Sidang Skripsi'])
            ->with(['ruangan', 'user']);

        // Apply Filter Lantai/Gedung ke $sidangQuery juga!
        if ($filterLantai) {
            $sidangQuery->whereHas('ruangan', function($q) use ($filterLantai) {
                $q->where('lantai', 'LIKE', "%{$filterLantai}%");
            });
        }
        if ($filterGedung) {
            $sidangQuery->whereHas('ruangan', function($q) use ($filterGedung) {
                $q->where('gedung', 'LIKE', "%{$filterGedung}%");
            });
        }

        $sidangRapat = $sidangQuery->orderBy('waktu_mulai')->get()->map(function ($item) use ($today) {
            $start = Carbon::parse($item->waktu_mulai);
            $end = Carbon::parse($item->waktu_selesai);
            
            // Logika Status Sederhana
            $status = 'Reserved';
            if ($today->between($start, $end)) {
                $status = 'Occupied';
            } elseif ($today->gt($end)) {
                $status = 'Finished';
            }

            // Gabungkan Dosen jadi Array string biar gampang di frontend
            $pembimbing = array_filter([$item->dosen_pembimbing_1, $item->dosen_pembimbing_2]);
            $penguji = array_filter([$item->dosen_penguji_1, $item->dosen_penguji_2]);

            return [
                'id' => $item->id,
                'room' => $item->ruangan->nama ?? 'TBA',
                'title' => $item->nama_kegiatan, // Misal: "Sidang Skripsi: Budi"
                'time' => $start->format('H:i') . ' - ' . $end->format('H:i'),
                'status' => $status,
                'jenis' => $item->jenis_kegiatan, // Penting buat pembeda UI
                'pic' => $item->nama_pic,         // Nama Mahasiswa / Penanggung Jawab
                
                // Data Dosen (Dipisah koma)
                'pembimbing' => !empty($pembimbing) ? implode(', ', $pembimbing) : null,
                'penguji' => !empty($penguji) ? implode(', ', $penguji) : null,
            ];
        });

        return response()->json([
            'jadwal_kuliah_hari_ini' => $jadwalKuliah,
            'kegiatan_mendatang' => $kegiatan, // Panel Tengah
            'sidang_rapat' => $sidangRapat,    // Panel Kanan (Data Baru)
        ]);
    }

    public function getCars()
    {
        // Ambil data mobil beserta relasi trip yang sedang berlangsung
        $cars = \App\Models\Mobil::with(['tripBerlangsung.driver'])
            ->orderBy('nama_mobil', 'asc')
            ->get()
            ->map(function($car) {
                return [
                    'id' => $car->id,
                    'nama' => $car->nama_mobil,
                    'plat' => $car->plat_nomor,
                    'status' => $car->status, // tersedia, dipakai, maintenance
                    
                    // Detail Trip (hanya jika sedang dipakai)
                    'detail_trip' => $car->tripBerlangsung ? [
                        'driver' => $car->tripBerlangsung->driver->name ?? 'Driver',
                        'tujuan' => $car->tripBerlangsung->tujuan,
                        'keperluan' => $car->tripBerlangsung->keperluan,
                        'mulai' => \Carbon\Carbon::parse($car->tripBerlangsung->waktu_mulai)->format('H:i'),
                    ] : null
                ];
            });

        return response()->json($cars);
    }

    public function getPendingRequests()
    {
        $requests = \App\Models\PermintaanKegiatan::with(['user', 'picUser'])
            ->where('status_permintaan', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($item) {
                // Logic Label Jenis Layanan
                $types = [];
                if ($item->request_ruang) $types[] = 'Ruang';
                if ($item->request_konsumsi) $types[] = 'Konsumsi';

                return [
                    'id' => $item->id,
                    'kegiatan' => $item->nama_kegiatan,     // Judul tetap penting biar tau konteks
                    'pemohon' => $item->user->name,         // <--- PEMOHON
                    'jumlah_peserta' => $item->jumlah_peserta, // <--- KEBUTUHAN PESERTA
                    'waktu' => \Carbon\Carbon::parse($item->created_at)->diffForHumans(),
                    'jenis_layanan' => implode(' & ', $types),
                ];
            });

        return response()->json($requests);
    }

    /**
     * API Khusus untuk Vertical Signage TV
     */
    public function getVerticalData(Request $request)
    {
        Carbon::setLocale('id');
        $today = Carbon::now();

        // Ambil parameter filter (bisa diset dari URL ?room=... atau default di code)
        // Default cari yang mengandung "Lt. 10" (sesuai request)
        $filterRoom = $request->input('room', 'Lt. 10'); 
        
        $query = Kegiatan::with(['ruangan', 'user'])
            ->where('status', 'disetujui')
            ->whereDate('waktu_mulai', '>=', $today) // Ambil hari ini ke depan
            ->where('jenis_kegiatan', 'Rapat'); // KHUSUS RAPAT

        // Filter Ruangan
        if ($filterRoom) {
            $query->whereHas('ruangan', function($q) use ($filterRoom) {
                $q->where('nama', 'LIKE', "%{$filterRoom}%");
            });
        }

        $data = $query->orderBy('waktu_mulai', 'asc')
            ->limit(8) // Ambil 10 terdekat
            ->get()
            ->map(function ($item) use ($today) {
                $start = Carbon::parse($item->waktu_mulai);
                $end = Carbon::parse($item->waktu_selesai);
                
                // Logic Status (Occupied / Reserved)
                $status = 'Reserved';
                // Jika hari ini DAN jam sekarang masuk range
                if ($start->isSameDay(Carbon::now()) && Carbon::now()->between($start, $end)) {
                    $status = 'Occupied'; 
                } 

                // Logic Label Tanggal
                $isToday = $start->isSameDay(Carbon::now());
                $isTomorrow = $start->isSameDay(Carbon::now()->addDay());
                
                if ($isToday) {
                    $dateLabel = "HARI INI";
                    $dateFlag = "today";
                } elseif ($isTomorrow) {
                    $dateLabel = "BESOK • " . $start->translatedFormat('d F');
                    $dateFlag = "tomorrow";
                } else {
                    $dateLabel = $start->translatedFormat('l, d F'); 
                    $dateFlag = "future";
                }

                // Nama Peminjam / PIC
                $picName = $item->nama_pic ?: ($item->user->name ?? '-');

                return [
                    'id' => $item->id,
                    'title' => $item->nama_kegiatan,
                    'room' => $item->ruangan->nama ?? 'TBA',
                    'time' => $start->format('H:i') . ' - ' . $end->format('H:i'),
                    'status' => $status,
                    'pic' => $picName,
                    'date_label' => $dateLabel,
                    'date_flag' => $dateFlag,
                ];
            });

        return response()->json($data);
    }
}
