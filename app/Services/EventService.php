<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\Semester;
use App\Models\JadwalPerkuliahan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventService
{
    /**
     * Memeriksa apakah sebuah ruangan sudah dipesan pada rentang waktu tertentu,
     * termasuk untuk kegiatan berulang.
     *
     * @param array $requestData Data dari request.
     * @return Kegiatan|null Mengembalikan objek Kegiatan/JadwalPerkuliahan (dummy) jika bentrok, atau null jika tersedia.
     */
    public function isRoomTaken($requestData)
    {
        // Definisikan format tanggal yang digunakan di form
        $datetime_format = config('panel.date_format', 'd M Y') . ' ' . config('panel.time_format', 'H:i');
        $date_format = config('panel.date_format', 'd M Y');

        $ignoreId = $requestData['ignore_id'] ?? null;

        // Kumpulkan semua rentang waktu yang perlu diperiksa
        $waktuUntukDicek = [];
        try {
            $waktuMulai = Carbon::createFromFormat($datetime_format, $requestData['waktu_mulai']);
            $waktuSelesai = Carbon::createFromFormat($datetime_format, $requestData['waktu_selesai']);

            $recurringUntil = !empty($requestData['berulang_sampai'])
                ? Carbon::createFromFormat($date_format, $requestData['berulang_sampai'])->endOfDay()
                : $waktuSelesai->copy()->endOfDay();
        } catch (\Exception $e) {
            $raw = $requestData['waktu_mulai'] ?? null;
            Log::error('[EventService::isRoomTaken] Carbon parsing failed: ' . $e->getMessage(), [
                'input' => $raw,
            ]);
            // Lempar agar controller menangani sebagai validasi, bukan "room taken"
            throw new \InvalidArgumentException('Format tanggal/waktu tidak valid. Harap gunakan format yang sesuai.');
        }

        // Ambil tipe pengulangan dari request, default ke 'harian' jika tidak ada
        $tipeBerulang = $requestData['tipe_berulang'] ?? 'harian';

        while ($waktuMulai->lte($recurringUntil)) {
            $waktuUntukDicek[] = [
                'mulai' => $waktuMulai->copy(),
                'selesai' => $waktuSelesai->copy(),
            ];
            
            if ($tipeBerulang === 'mingguan') {
                $waktuMulai->addWeek();
                $waktuSelesai->addWeek();
            } else {
                $waktuMulai->addDay();
                $waktuSelesai->addDay();
            }
        }

        if (empty($waktuUntukDicek)) {
            return null;
        }

        // Cek bentrok dengan tabel KEGIATAN dalam SATU QUERY
        $kegiatanBentrok = Kegiatan::where('ruangan_id', $requestData['ruangan_id'])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->whereIn('status', ['disetujui', 'verifikasi_akademik', 'verifikasi_sarpras'])
            ->where(function (Builder $query) use ($waktuUntukDicek) {
                foreach ($waktuUntukDicek as $waktu) {
                    $query->orWhere(function (Builder $subQuery) use ($waktu) {
                        $subQuery->where('waktu_mulai', '<', $waktu['selesai'])
                                 ->where('waktu_selesai', '>', $waktu['mulai']);
                    });
                }
            })
            ->first();

        if ($kegiatanBentrok) {
            return $kegiatanBentrok;
        }

        // Cek bentrok dengan tabel JADWAL KULIAH dalam SATU QUERY
        $hariUnik = collect($waktuUntukDicek)->map(function ($waktu) {
            return $waktu['mulai']->locale('id')->isoFormat('dddd');
        })->unique()->values()->all();

        $jamMulai = Carbon::createFromFormat($datetime_format, $requestData['waktu_mulai'])->format('H:i:s');
        $jamSelesai = Carbon::createFromFormat($datetime_format, $requestData['waktu_selesai'])->format('H:i:s');

        $kuliahBentrok = JadwalPerkuliahan::where('ruangan_id', $requestData['ruangan_id'])
            ->whereIn('hari', $hariUnik)
            ->where('waktu_mulai', '<', $jamSelesai)
            ->where('waktu_selesai', '>', $jamMulai)
            ->where(function (Builder $query) use ($waktuUntukDicek) {
                foreach ($waktuUntukDicek as $waktu) {
                    $tanggal = $waktu['mulai']->toDateString();
                    $query->orWhere(function (Builder $dateQuery) use ($tanggal) {
                        $dateQuery->whereDate('berlaku_mulai', '<=', $tanggal)
                                  ->whereDate('berlaku_sampai', '>=', $tanggal);
                    });
                }
            })
            ->first();

        if ($kuliahBentrok) {
            return new Kegiatan([
                'nama_kegiatan' => 'Perkuliahan: ' . $kuliahBentrok->mata_kuliah
            ]);
        }

        return null;
    }

    /**
     * [FINAL & OPTIMAL] Membuat event tunggal atau berulang dengan efisien.
     *
     * @param array $requestData Data dari request.
     * @return void
     */
    public function createEvents(array $requestData): array
    {
        $datetime_format = config('panel.date_format', 'd M Y') . ' ' . config('panel.time_format', 'H:i');
        $date_format = config('panel.date_format', 'd M Y');

        $eventsToCreate = [];

        try {
            $waktuMulai = Carbon::createFromFormat($datetime_format, $requestData['waktu_mulai']);
            $waktuSelesai = Carbon::createFromFormat($datetime_format, $requestData['waktu_selesai']);

            $recurringUntil = !empty($requestData['berulang_sampai'])
                ? Carbon::createFromFormat($date_format, $requestData['berulang_sampai'])->endOfDay()
                : $waktuSelesai->copy()->endOfDay();

        } catch (\Exception $e) {
            Log::error('[createEvents] Carbon Parsing Failed: ' . $e->getMessage());
            return [];
        }
        
        $baseData = [
            'ruangan_id'    => $requestData['ruangan_id'],
            'nama_kegiatan' => $requestData['nama_kegiatan'],
            'jenis_kegiatan'     => $requestData['jenis_kegiatan'] ?? 'Lainnya',
            'poster'             => $requestData['poster'] ?? null,
            'dosen_pembimbing_1' => $requestData['dosen_pembimbing_1'] ?? null,
            'dosen_pembimbing_2' => $requestData['dosen_pembimbing_2'] ?? null,
            'dosen_penguji_1'    => $requestData['dosen_penguji_1'] ?? null,
            'dosen_penguji_2'    => $requestData['dosen_penguji_2'] ?? null,
            'deskripsi'     => $requestData['deskripsi'] ?? null,
            'user_id'       => $requestData['user_id'],
            'status'        => $requestData['status'],
            'nama_pic'      => $requestData['nama_pic'] ?? null,
            'nomor_telepon' => $requestData['nomor_telepon'] ?? null,
            'surat_izin'    => $requestData['surat_izin'] ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
        
        $tipeBerulang = $requestData['tipe_berulang'] ?? 'harian';

        while ($waktuMulai->lte($recurringUntil)) {
            $eventsToCreate[] = array_merge($baseData, [
                'waktu_mulai'   => $waktuMulai->format('Y-m-d H:i:s'),
                'waktu_selesai' => $waktuSelesai->format('Y-m-d H:i:s'),
            ]);

            if ($tipeBerulang === 'mingguan') {
                $waktuMulai->addWeek();
                $waktuSelesai->addWeek();
            } else {
                $waktuMulai->addDay();
                $waktuSelesai->addDay();
            }
        }

        $created = [];
        if (!empty($eventsToCreate)) {
            // create each event with Eloquent so model events and relations work
            foreach ($eventsToCreate as $data) {
                $created[] = Kegiatan::create($data);
            }
        }

        return $created; // return created models so caller can create history if desired
    }

    // =======================================================================
    // METHOD YANG HILANG DIKEMBALIKAN DENGAN OPTIMASI
    // =======================================================================

    /**
     * Memeriksa apakah jadwal kuliah baru bentrok dengan jadwal kuliah lain.
     *
     * @param array $requestData
     * @return JadwalPerkuliahan|null
     */
    public function isRoomTakenForLecture(array $requestData)
    {
        $jamMulai = $requestData['waktu_mulai']; 
        $jamSelesai = $requestData['waktu_selesai'];
        $semesterId = $requestData['semester_id']; // Wajib ada

        return JadwalPerkuliahan::where('ruangan_id', $requestData['ruangan_id'])
            ->where('semester_id', $semesterId) // Kunci utama: Cek di semester yang sama
            ->where('hari', $requestData['hari'])
            ->when(isset($requestData['id']), function ($query) use ($requestData) {
                return $query->where('id', '!=', $requestData['id']);
            })
            ->where(function ($query) use ($jamMulai, $jamSelesai) {
                $query->where('waktu_mulai', '<', $jamSelesai)
                      ->where('waktu_selesai', '>', $jamMulai);
            })
            ->first();
    }

    /**
     * Memeriksa apakah jadwal kuliah baru bentrok dengan kegiatan yang sudah ada.
     *
     * @param array $data
     * @return Kegiatan|null
     */
    public function isRoomTakenByKegiatan(array $data)
    {
        // 1. Cari tanggal spesifik kuliah berdasarkan rentang Semester
        $semester = Semester::find($data['semester_id']);

        if (!$semester) return null;

        $lectureDates = [];
        $startDate = $semester->tanggal_mulai; 
        $endDate = $semester->tanggal_selesai;
        $dayOfWeek = ucfirst(strtolower($data['hari'])); // Senin, Selasa...

        // Loop generating tanggal
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->locale('id')->isoFormat('dddd') === $dayOfWeek) {
                $lectureDates[] = $currentDate->toDateString();
            }
            $currentDate->addDay();
        }

        if (empty($lectureDates)) return null;

        // 2. Cek database Kegiatan
        $jamMulai = $data['waktu_mulai'];
        $jamSelesai = $data['waktu_selesai'];

        return Kegiatan::where('ruangan_id', $data['ruangan_id'])
            ->whereIn('status', ['disetujui', 'verifikasi_akademik', 'verifikasi_sarpras'])
            // Cek apakah tanggal kegiatan ada di dalam daftar tanggal kuliah
            ->whereIn(DB::raw('DATE(waktu_mulai)'), $lectureDates)
            // Cek irisan jam
            ->where(function ($query) use ($jamMulai, $jamSelesai) {
                $query->whereTime('waktu_mulai', '<', $jamSelesai)
                      ->whereTime('waktu_selesai', '>', $jamMulai);
            })
            ->first();
    }
}
