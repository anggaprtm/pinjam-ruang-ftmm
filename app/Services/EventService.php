<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\JadwalPerkuliahan;
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

            // 2. Cek bentrok dengan jadwal perkuliahan
            $hari = $waktu_mulai->locale('id')->isoFormat('dddd'); // e.g. "Senin"
            $jamMulai = $waktu_mulai->format('H:i:s');
            $jamSelesai = $waktu_selesai->format('H:i:s');
            $tanggal = $waktu_mulai->toDateString();

            $kuliahBentrok = JadwalPerkuliahan::where('ruangan_id', $requestData['ruangan_id'])
                ->where('hari', $hari)
                ->whereDate('berlaku_mulai', '<=', $tanggal)
                ->whereDate('berlaku_sampai', '>=', $tanggal)
                ->where(function ($query) use ($jamMulai, $jamSelesai) {
                    $query->where('waktu_mulai', '<', $jamSelesai)
                        ->where('waktu_selesai', '>', $jamMulai);
                })
                ->first();

            if ($kuliahBentrok) {
                // Bikin dummy object Kegiatan supaya error message tetap konsisten
                return new Kegiatan([
                    'nama_kegiatan' => 'Perkuliahan: ' . $kuliahBentrok->mata_kuliah
                ]);
            }
    
            $waktu_mulai->addWeek();
            $waktu_selesai->addWeek();
        } while ($waktu_selesai->lte($recurringUntil));
    
        return null; // Tidak ada bentrokan
        }
    
        public function isRoomTakenForLecture($requestData)
        {
            $tanggalCek = Carbon::parse($requestData['berlaku_mulai']);
            $hari = $requestData['hari']; // Contoh: "Senin"
            $jamMulai = $requestData['waktu_mulai']; // format 'H:i:s'
            $jamSelesai = $requestData['waktu_selesai'];

            // 1. Cek bentrok dengan jadwal perkuliahan lain
            $kuliahBentrok = JadwalPerkuliahan::where('ruangan_id', $requestData['ruangan_id'])
                ->where('hari', $hari)
                ->where(function ($query) use ($jamMulai, $jamSelesai) {
                    $query->where('waktu_mulai', '<', $jamSelesai)
                        ->where('waktu_selesai', '>', $jamMulai);
                })
                ->first();

            if ($kuliahBentrok) {
                return new JadwalPerkuliahan([
                    'mata_kuliah' => 'Kuliah lain: ' . $kuliahBentrok->mata_kuliah
                ]);
            }

            return null;
        }

        public function isRoomTakenByKegiatan($data)
        {
            $hari = strtolower($data['hari']); // contoh: "rabu"
            $ruangan_id = $data['ruangan_id'];
            $jamMulai = $data['waktu_mulai'];
            $jamSelesai = $data['waktu_selesai'];

            $berlakuMulai = Carbon::parse($data['berlaku_mulai']);
            $berlakuSampai = Carbon::parse($data['berlaku_sampai']);

            while ($berlakuMulai->lte($berlakuSampai)) {
                if ($berlakuMulai->locale('id')->isoFormat('dddd') === ucfirst($hari)) {
                    $tanggal = $berlakuMulai->toDateString();

                    $bentrok = \App\Models\Kegiatan::where('ruangan_id', $ruangan_id)
                        ->whereDate('waktu_mulai', '=', $tanggal)
                        ->where(function ($query) use ($jamMulai, $jamSelesai) {
                            $query->whereTime('waktu_mulai', '<', $jamSelesai)
                                ->whereTime('waktu_selesai', '>', $jamMulai);
                        })
                        ->first();

                    if ($bentrok) {
                        return $bentrok;
                    }
                }

                $berlakuMulai->addDay();
            }

            return null;
        }


}