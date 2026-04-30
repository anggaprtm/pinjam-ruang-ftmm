<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class DekanAgendaController extends Controller
{
    public function index(Request $request)
    {
        // week_offset dikirim dari frontend (0 = minggu ini, -1 = minggu lalu, dst.)
        $weekOffset = (int) $request->query('week_offset', 0);

        $cacheKey = "dekan_agenda_week_{$weekOffset}";

        $events = Cache::remember($cacheKey, 300, function () use ($weekOffset) {
            return $this->fetchFromGoogle($weekOffset);
        });

        return response()->json([
            'success'     => true,
            'date'        => Carbon::now('Asia/Jakarta')->toDateString(),
            'week_offset' => $weekOffset,
            'data'        => $events,
        ]);
    }

    private function fetchFromGoogle(int $weekOffset = 0): array
    {
        $calendarId = config('services.google_dekan.calendar_id');
        $now        = Carbon::now('Asia/Jakarta');

        // Hitung rentang Senin–Jumat untuk minggu yang diminta
        $monday = $now->copy()
            ->addWeeks($weekOffset)
            ->startOfWeek(Carbon::MONDAY)
            ->startOfDay();

        $friday = $monday->copy()
            ->addDays(4)
            ->endOfDay();

        try {
            $googleEvents = Event::get(
                $monday,
                $friday,
                [],
                $calendarId
            );
        } catch (\Exception $e) {
            Log::error('DekanAgenda fetch error: ' . $e->getMessage());
            return [];
        }

        $events = [];

        foreach ($googleEvents as $event) {
            $isAllDay = !$event->startDateTime;

            $startRaw = $event->startDateTime ?? $event->startDate;
            $endRaw   = $event->endDateTime   ?? $event->endDate;

            if (!$startRaw) continue;

            $start = Carbon::parse($startRaw)->setTimezone('Asia/Jakarta');
            $end   = Carbon::parse($endRaw)->setTimezone('Asia/Jakarta');

            // Skip event di luar hari kerja (Sabtu & Minggu)
            if (in_array($start->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                continue;
            }

            $events[] = [
                'id'          => $event->id,
                'title'       => $event->name ?? '(Tanpa Judul)',
                'description' => $event->description ?? null,
                'location'    => $event->location ?? null,
                'date'        => $start->toDateString(),          // ← FIX: field date per event
                'start_time'  => $isAllDay ? null : $start->format('H:i'),
                'colorId'     => $event->googleEvent->getColorId(),
                'end_time'    => $isAllDay ? null : $end->format('H:i'),
                'is_all_day'  => $isAllDay,
                'status'      => $this->resolveStatus($start, $end, $isAllDay),
            ];
        }

        // Urutkan: all-day dulu, lalu berdasarkan start_time ascending
        usort($events, function ($a, $b) {
            if ($a['is_all_day'] && !$b['is_all_day']) return -1;
            if (!$a['is_all_day'] && $b['is_all_day']) return 1;
            return strcmp($a['date'] . ($a['start_time'] ?? '00:00'), $b['date'] . ($b['start_time'] ?? '00:00'));
        });

        return $events;
    }

    private function resolveStatus(Carbon $start, Carbon $end, bool $isAllDay): string
    {
        if ($isAllDay) return 'all_day';

        $now = Carbon::now('Asia/Jakarta');

        if ($now->between($start, $end)) return 'ongoing';
        if ($now->lt($start))           return 'upcoming';

        return 'finished';
    }

    public function refresh(Request $request)
    {
        $weekOffset = (int) $request->query('week_offset', 0);
        $cacheKey   = "dekan_agenda_week_{$weekOffset}";

        Cache::forget($cacheKey);

        // Langsung fetch ulang dan return datanya sekalian
        $events = $this->fetchFromGoogle($weekOffset);
        Cache::put($cacheKey, $events, 300);

        return response()->json([
            'success'     => true,
            'message'     => 'Cache berhasil diperbarui',
            'date'        => Carbon::now('Asia/Jakarta')->toDateString(),
            'week_offset' => $weekOffset,
            'data'        => $events,
        ]);
    }
}