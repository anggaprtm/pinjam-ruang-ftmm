<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DisplayConfig;
use Carbon\Carbon;

class DisplayConfigController extends Controller
{
    public function show($location)
    {
        $config = DisplayConfig::with(['contents', 'schedules'])
            ->where('location', $location)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return response()->json(['mode' => 'dashboard']);
        }

        $now = Carbon::now()->format('H:i:s');

        // 🔥 CEK SCHEDULE
        $activeSchedule = $config->schedules
            ->first(function ($s) use ($now) {
                return $now >= $s->start_time && $now <= $s->end_time;
            });

        $mode = $activeSchedule ? $activeSchedule->mode : $config->mode;

        $defaultVisibility = [
            'lectures' => true,
            'events' => true,
            'meetings' => true,
            'agenda' => true,
            'cars' => true,
            'pending_requests' => true,
        ];

        return response()->json([
            'mode' => $mode,
            'contents' => $config->contents,
            'content_type' => $config->content_type,
            'content_value' => $config->content_value,
            'panel_visibility' => array_merge($defaultVisibility, $config->panel_visibility ?? []),
            'running_text' => $config->running_text,
        ]);
    }
}