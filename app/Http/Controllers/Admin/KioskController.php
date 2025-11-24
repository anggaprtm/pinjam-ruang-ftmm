<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kegiatan;
use App\Models\JadwalPerkuliahan;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\Carbon;

class KioskController extends Controller
{
    /**
     * Show kiosk view (fullscreen dashboard for TV).
     */
    public function index()
    {
        // dashboard stats (reuse HomeController logic)
        $ruanganCount = Ruangan::count();
        $kegiatanMenungguCount = Kegiatan::whereIn('status', ['belum_disetujui', 'verifikasi_sarpras', 'verifikasi_akademik'])->count();
        $kegiatanDisetujuiCount = Kegiatan::where('status', 'disetujui')->count();
        $kegiatanTotalCount = Kegiatan::count();

        // Today's events: combine Kegiatan (today) + JadwalPerkuliahan occurrences that fall today
        $today = Carbon::today();
        $kegiatanHariIni = Kegiatan::with(['user','ruangan'])
            ->whereDate('waktu_mulai', $today)
            ->orderBy('waktu_mulai')
            ->get()
            ->map(function($k){
                return [
                    'id' => 'keg-'.$k->id,
                    'title' => $k->nama_kegiatan,
                    'start' => $k->getAttributes()['waktu_mulai'] ?? null,
                    'end' => $k->getAttributes()['waktu_selesai'] ?? null,
                    'ruangan' => $k->ruangan->nama ?? null,
                    'peminjam' => $k->nama_pic ?: ($k->user->name ?? null),
                    'type' => 'kegiatan'
                ];
            });

        // Jadwal perkuliahan occurrences on this weekday
        $jadwalList = JadwalPerkuliahan::with('ruangan')->get();
        $jadwalHariIni = collect([]);
        // Map common Indonesian day names to ISO day numbers (Mon=1 .. Sun=7)
        $dayMap = [
            'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 7,
            // common variations
            'jumat' => 5, 'jumat' => 5, 'jumat' => 5,
        ];
        $todayIso = $today->dayOfWeekIso; // 1..7

        foreach ($jadwalList as $jadwal) {
            $startDate = Carbon::parse($jadwal->berlaku_mulai);
            $endDate = Carbon::parse($jadwal->berlaku_sampai);
            if ($today->lt($startDate) || $today->gt($endDate)) continue;

            $jadwalHariRaw = strtolower(trim((string) $jadwal->hari));
            $jadwalDayIso = $dayMap[$jadwalHariRaw] ?? null;
            if (!$jadwalDayIso) {
                // try to interpret english names
                $englishMap = ['monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6,'sunday'=>7];
                $jadwalDayIso = $englishMap[strtolower($jadwal->hari)] ?? null;
            }
            if (!$jadwalDayIso || $jadwalDayIso !== $todayIso) continue;

            $startDateTime = Carbon::parse($today->toDateString() . ' ' . $jadwal->waktu_mulai);
            $endDateTime = Carbon::parse($today->toDateString() . ' ' . $jadwal->waktu_selesai);
            $jadwalHariIni->push([
                'id' => 'kul-'.$jadwal->id,
                'title' => $jadwal->mata_kuliah ?: 'Perkuliahan',
                'start' => $startDateTime->toDateTimeString(),
                'end' => $endDateTime->toDateTimeString(),
                'ruangan' => $jadwal->ruangan->nama ?? null,
                'peminjam' => $jadwal->program_studi ?? 'Perkuliahan',
                'type' => 'perkuliahan'
            ]);
        }

        $todayEvents = $kegiatanHariIni->concat($jadwalHariIni)->sortBy('start')->values();

        return view('admin.kiosk', compact(
            'ruanganCount', 'kegiatanMenungguCount', 'kegiatanDisetujuiCount', 'kegiatanTotalCount', 'todayEvents'
        ));
    }

    /**
     * Return approved events as JSON for kiosk polling.
     */
    public function events(Request $request)
    {
        // Ambil hanya kegiatan yang disetujui (status = 'disetujui' atau 'approved')
        $statusValues = ['disetujui', 'approved'];
        $events = [];

        // Respect optional start/end params from FullCalendar (ISO dates)
        $rangeStart = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : null;
        $rangeEnd = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : null;

        // Kegiatan (approved)
        $kegiatanQuery = Kegiatan::whereIn('status', $statusValues)->with('ruangan', 'user')->orderBy('waktu_mulai');
        if ($rangeStart && $rangeEnd) {
            // limit to events that overlap the requested range
            $kegiatanQuery->where(function($q) use ($rangeStart, $rangeEnd) {
                $q->whereBetween('waktu_mulai', [$rangeStart, $rangeEnd])
                  ->orWhereBetween('waktu_selesai', [$rangeStart, $rangeEnd])
                  ->orWhere(function($q2) use ($rangeStart, $rangeEnd) {
                      $q2->where('waktu_mulai', '<', $rangeStart)->where('waktu_selesai', '>', $rangeEnd);
                  });
            });
        }

        $kegiatanQuery->get()->each(function($k) use (&$events) {
            $rawStart = $k->getAttributes()['waktu_mulai'] ?? null;
            $rawEnd = $k->getAttributes()['waktu_selesai'] ?? null;
            $peminjamDisplay = $k->nama_pic ?: ($k->user ? $k->user->name : null);
            $color = $this->getUserColor($k->user_id ?? null);

            $events[] = [
                'id' => $k->id,
                'title' => $k->nama_kegiatan,
                'start' => $rawStart ? Carbon::parse($rawStart)->toIso8601String() : null,
                'end' => $rawEnd ? Carbon::parse($rawEnd)->toIso8601String() : null,
                'color' => $color,
                'extendedProps' => [
                    'ruangan_nama' => $k->ruangan ? ($k->ruangan->nama ?? $k->ruangan->nama_ruangan ?? null) : null,
                    'user_name' => $k->user ? $k->user->name : null,
                    'deskripsi' => $k->deskripsi,
                    'nama_pic' => $k->nama_pic,
                    'nomor_telepon' => $k->nomor_telepon,
                    'type' => 'kegiatan'
                ],
            ];
        });

        // Return only Kegiatan (non-perkuliahan) for the kiosk calendar
        return response()->json(['data' => $events]);
    }

    private function getUserColor($userId)
    {
        $colors = [
            1 => '#1E90FF', 2 => '#9e0142', 3 => '#095259', 4 => '#f46d43',
            5 => '#fdae61', 6 => '#fee08b', 7 => '#000000', 8 => '#abdda4',
            9 => '#66c2a5', 10 => '#3288bd', 11 => '#5e4fa2', 12 => '#a4c2f4',
            13 => '#b2b200', 14 => '#00ff00', 15 => '#d5a6bd', 16 => '#ea9999',
        ];
        return $colors[$userId] ?? '#741847';
    }
}
