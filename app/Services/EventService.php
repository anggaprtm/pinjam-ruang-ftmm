<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\Ruangan;
use Carbon\Carbon;

class EventService
{
    public function createRecurringEvents($requestData)
    {
        $recurringUntil                 = Carbon::parse($requestData['berulang_sampai'])->setTime(23, 59, 59);
        $requestData['waktu_mulai']     = Carbon::parse($requestData['waktu_mulai'])->addWeek();
        $requestData['waktu_selesai']   = Carbon::parse($requestData['waktu_selesai'])->addWeek();

        while ($requestData['waktu_selesai']->lte($recurringUntil)) {
            $this->createEvent($requestData);
            $requestData['waktu_mulai']->addWeek();
            $requestData['waktu_selesai']->addWeek();
        }
    }

    public function createEvent($requestData)
    {
        $requestData['waktu_mulai']     = $requestData['waktu_mulai']->format('Y-m-d H:i');
        $requestData['waktu_selesai']   = $requestData['waktu_selesai']->format('Y-m-d H:i');

        return Kegiatan::create($requestData);
    }

    public function isRoomTaken($requestData)
    {
        $recurringUntil = Carbon::parse($requestData['berulang_sampai'])->setTime(23, 59, 59);
        $waktu_mulai    = Carbon::parse($requestData['waktu_mulai']);
        $waktu_selesai  = Carbon::parse($requestData['waktu_selesai']);
    
        do {
            $kegiatanBentrok = Kegiatan::where('ruangan_id', $requestData['ruangan_id'])
                ->where(function ($query) use ($waktu_mulai, $waktu_selesai) {
                    $query->whereBetween('waktu_mulai', [$waktu_mulai, $waktu_selesai])
                          ->orWhereBetween('waktu_selesai', [$waktu_mulai, $waktu_selesai])
                          ->orWhere(function ($query) use ($waktu_mulai, $waktu_selesai) {
                              $query->where('waktu_mulai', '<=', $waktu_mulai)
                                    ->where('waktu_selesai', '>=', $waktu_selesai);
                          });
                })
                ->first();
    
            if ($kegiatanBentrok) {
                return $kegiatanBentrok; // Mengembalikan kegiatan yang bentrok
            }
    
            $waktu_mulai->addWeek();
            $waktu_selesai->addWeek();
        } while ($waktu_selesai->lte($recurringUntil));
    
        return null; // Tidak ada bentrokan
        }
    // {
    //     $recurringUntil = Carbon::parse($requestData['berulang_sampai'])->setTime(23, 59, 59);
    //     $waktu_mulai    = Carbon::parse($requestData['waktu_mulai']);
    //     $waktu_selesai  = Carbon::parse($requestData['waktu_selesai']);
    //     $kegiatan      = Kegiatan::where('ruangan_id', $requestData['ruangan_id'])->get();

    //     do {
    //         if (
    //             Kegiatan::where('ruangan_id', $requestData['ruangan_id'])
    //             ->where(function ($query) use ($waktu_mulai, $waktu_selesai) {
    //                 $query->whereBetween('waktu_mulai', [$waktu_mulai, $waktu_selesai])
    //                       ->orWhereBetween('waktu_selesai', [$waktu_mulai, $waktu_selesai])
    //                       ->orWhere(function ($query) use ($waktu_mulai, $waktu_selesai) {
    //                           $query->where('waktu_mulai', '<=', $waktu_mulai)
    //                                 ->where('waktu_selesai', '>=', $waktu_selesai);
    //                       });
    //             })
    //             ->exists()
    //             // $kegiatan->where('waktu_mulai', '<', $waktu_mulai)->where('waktu_selesai', '>', $waktu_mulai)->count() ||
    //             // $kegiatan->where('waktu_mulai', '<', $waktu_selesai)->where('waktu_selesai', '>', $waktu_selesai)->count() ||
    //             // $kegiatan->where('waktu_mulai', '<', $waktu_mulai)->where('waktu_selesai', '>', $waktu_selesai)->count()
    //         ) {
    //             return true;
    //         }

    //         $waktu_mulai->addWeek();
    //         $waktu_selesai->addWeek();
    //     } while ($waktu_selesai->lte($recurringUntil));

    //     return false;
    // }
}