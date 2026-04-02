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
        $now = Carbon::now();

        $config = DisplayConfig::where('location', $location)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_time')
                      ->orWhere('start_time', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_time')
                      ->orWhere('end_time', '>=', $now);
            })
            ->latest()
            ->first();

        if (!$config) {
            return response()->json([
                'mode' => 'dashboard'
            ]);
        }

        return response()->json([
            'mode' => $config->mode,
            'content_type' => $config->content_type,
            'content_value' => $config->content_value,
        ]);
    }
}