<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        // Hanya Admin saja yang boleh mengakses
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Preset handling: preset=this_month | all_time | custom (start/end)
        $preset = $request->query('preset');
        $rawStart = $request->query('start');
        $rawEnd = $request->query('end');

        if ($preset === 'this_month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        } elseif ($preset === 'all_time') {
            $start = null;
            $end = null;
        } else {
            $end = $rawEnd ? Carbon::parse($rawEnd) : Carbon::today();
            $start = $rawStart ? Carbon::parse($rawStart) : $end->copy()->subDays(29);
        }

        // Pastikan waktu penuh (day range) jika ada
        $startDateTime = $start ? $start->copy()->startOfDay()->toDateTimeString() : null;
        $endDateTime = $end ? $end->copy()->endOfDay()->toDateTimeString() : null;

        // Top Ruangan (by jumlah kegiatan dalam rentang)
        $roomsCacheKey = 'stats:topRooms:' . ($startDateTime ?? 'all') . ':' . ($endDateTime ?? 'all');
        $topRooms = Cache::remember($roomsCacheKey, now()->addMinutes(5), function () use ($startDateTime, $endDateTime) {
            $q = DB::table('kegiatan')
                ->join('ruangan', 'kegiatan.ruangan_id', '=', 'ruangan.id')
                ->select('ruangan.id as ruangan_id', 'ruangan.nama', DB::raw('count(*) as total'))
                ->groupBy('ruangan.id', 'ruangan.nama')
                ->orderByDesc('total')
                ->limit(10);

            if ($startDateTime && $endDateTime) {
                $q->whereBetween('kegiatan.waktu_mulai', [$startDateTime, $endDateTime]);
            }

            return $q->get();
        });

        // Top Pengguna (by jumlah kegiatan dalam rentang)
        $usersCacheKey = 'stats:topUsers:' . ($startDateTime ?? 'all') . ':' . ($endDateTime ?? 'all');
        $topUsers = Cache::remember($usersCacheKey, now()->addMinutes(5), function () use ($startDateTime, $endDateTime) {
            $q = DB::table('kegiatan')
                ->join('users', 'kegiatan.user_id', '=', 'users.id')
                ->select('users.id as user_id', 'users.name', DB::raw('count(*) as total'))
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->limit(10);

            if ($startDateTime && $endDateTime) {
                $q->whereBetween('kegiatan.waktu_mulai', [$startDateTime, $endDateTime]);
            }

            return $q->get();
        });

        // Summary counts
        $totalRooms = Cache::remember('stats:totalRooms', now()->addHours(1), function () {
            return DB::table('ruangan')->count();
        });

        $bookingsCacheKey = 'stats:totalBookings:' . ($startDateTime ?? 'all') . ':' . ($endDateTime ?? 'all');
        $totalBookings = Cache::remember($bookingsCacheKey, now()->addMinutes(5), function () use ($startDateTime, $endDateTime) {
            $q = DB::table('kegiatan');
            if ($startDateTime && $endDateTime) {
                $q->whereBetween('waktu_mulai', [$startDateTime, $endDateTime]);
            }
            return $q->count();
        });

        // Trend peminjaman per hari
        $trendCacheKey = 'stats:trendDaily:' . ($startDateTime ?? 'all') . ':' . ($endDateTime ?? 'all');

        $trendDaily = Cache::remember($trendCacheKey, now()->addMinutes(5), function () use ($startDateTime, $endDateTime) {
            $q = DB::table('kegiatan')
                ->select(DB::raw("DATE(waktu_mulai) as tanggal"), DB::raw("COUNT(*) as total"))
                ->groupBy(DB::raw("DATE(waktu_mulai)"))
                ->orderBy(DB::raw("DATE(waktu_mulai)"), "asc");

            if ($startDateTime && $endDateTime) {
                $q->whereBetween('waktu_mulai', [$startDateTime, $endDateTime]);
            }

            return $q->get();
        });

        $trendLabels = $trendDaily->pluck('tanggal')->map(function ($d) {
            return Carbon::parse($d)->format('d M');
        })->toArray();

        $trendData = $trendDaily->pluck('total')->toArray();


        // Prepare data for charts
        $topRoomsLabels = $topRooms->pluck('nama')->toArray();
        $topRoomsData = $topRooms->pluck('total')->toArray();

        $topUsersLabels = $topUsers->pluck('name')->toArray();
        $topUsersData = $topUsers->pluck('total')->toArray();

        return view('statistics', compact(
            'topRooms', 'topUsers',
            'topRoomsLabels', 'topRoomsData',
            'topUsersLabels', 'topUsersData',
            'start', 'end',
            'totalRooms', 'totalBookings',
            'trendLabels', 'trendData',
        ));
    }

    public function exportExcel(Request $request)
    {
        // ambil filter sama kayak index
        $preset = $request->query('preset');
        $rawStart = $request->query('start');
        $rawEnd = $request->query('end');

        if ($preset === 'this_month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        } elseif ($preset === 'all_time') {
            $start = null;
            $end = null;
        } else {
            $end = $rawEnd ? Carbon::parse($rawEnd) : Carbon::today();
            $start = $rawStart ? Carbon::parse($rawStart) : $end->copy()->subDays(29);
        }

        $startDateTime = $start ? $start->copy()->startOfDay()->toDateTimeString() : null;
        $endDateTime = $end ? $end->copy()->endOfDay()->toDateTimeString() : null;

        $rows = DB::table('kegiatan')
            ->join('ruangan', 'kegiatan.ruangan_id', '=', 'ruangan.id')
            ->join('users', 'kegiatan.user_id', '=', 'users.id')
            ->select(
                'kegiatan.nama_kegiatan',
                'ruangan.nama as ruangan',
                'users.name as pemohon',
                'kegiatan.waktu_mulai',
                'kegiatan.waktu_selesai',
                'kegiatan.status'
            )
            ->when($startDateTime && $endDateTime, function($q) use ($startDateTime, $endDateTime) {
                $q->whereBetween('kegiatan.waktu_mulai', [$startDateTime, $endDateTime]);
            })
            ->orderBy('kegiatan.waktu_mulai', 'asc')
            ->get();

        $filename = 'statistik-peminjaman-'.now()->format('Ymd_His').'.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Nama Kegiatan', 'Ruangan', 'Pemohon', 'Waktu Mulai', 'Waktu Selesai', 'Status']);

            foreach ($rows as $r) {
                fputcsv($file, [
                    $r->nama_kegiatan,
                    $r->ruangan,
                    $r->pemohon,
                    $r->waktu_mulai,
                    $r->waktu_selesai,
                    $r->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        // NOTE:
        // Untuk export PDF yang rapi, idealnya pakai barryvdh/laravel-dompdf.
        // Kalau belum install, aku bisa bantu step installnya.

        abort(404, 'Export PDF belum diaktifkan. Install dompdf dulu.');
    }

}
