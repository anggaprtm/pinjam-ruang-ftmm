<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CalendarViewController extends Controller
{
    /**
     * Fetch national holidays from a public API and return as JSON.
     * The response is cached for 24 hours to improve performance and avoid hitting the API too frequently.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHolidays(Request $request)
    {
        $cacheKey = "holidays_indonesia_all"; // Changed cache key to reflect all holidays

        // Cache the result for 24 hours (86400 seconds)
        $holidays = Cache::remember($cacheKey, 86400, function () {
            try {
                // This API is simple and does not require an API key.
                // It provides a list of all holidays in Indonesia.
                $response = Http::get("https://api-harilibur.vercel.app/api");

                if ($response->successful()) {
                    $apiHolidays = $response->json();
                    
                    // Return all holidays from the API, not just national ones.
                    // The frontend will handle the coloring based on the 'is_national_holiday' flag.
                    return ['holidays' => $apiHolidays];
                }
                
                // Return null if the API call was not successful
                return null;

            } catch (\Exception $e) {
                // Return null in case of any exception (e.g., connection error)
                return null;
            }
        });

        // If holidays were successfully fetched (from cache or API), return them
        if ($holidays) {
            return response()->json($holidays);
        }

        // If fetching fails, return an error response
        return response()->json(['error' => 'Gagal mengambil data hari libur.'], 500);
    }
}

