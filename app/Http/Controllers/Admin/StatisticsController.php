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
            'totalRooms', 'totalBookings'
        ));
    }
}
