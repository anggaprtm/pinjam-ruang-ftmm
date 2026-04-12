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
        // 1. Setup Format & Locale
        Carbon::setLocale('id'); // Pastikan locale ID agar hari (Senin, Selasa) cocok
        $datetime_format = config('panel.date_format', 'd M Y') . ' ' . config('panel.time_format', 'H:i');
        $date_format = config('panel.date_format', 'd M Y');

        $ignoreId = $requestData['ignore_id'] ?? null;
        $waktuUntukDicek = [];

        // 2. Parsing Input Tanggal
       try {
            $waktuMulai = Carbon::parse($requestData['waktu_mulai']);
            $waktuSelesai = Carbon::parse($requestData['waktu_selesai']);

            $recurringUntil = !empty($requestData['berulang_sampai'])
                ? Carbon::parse($requestData['berulang_sampai'])->endOfDay()
                : $waktuSelesai->copy()->endOfDay();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Format tanggal/waktu tidak valid.');
        }

        // 3. Generate Loop Tanggal (Harian/Mingguan)
        // Loop ini membuat array semua slot waktu yang harus diperiksa
        $tipeBerulang = $requestData['tipe_berulang'] ?? 'harian';
        $currentMulai = $waktuMulai->copy();
        $currentSelesai = $waktuSelesai->copy();

        while ($currentMulai->lte($recurringUntil)) {
            $waktuUntukDicek[] = [
                'mulai'   => $currentMulai->copy(),
                'selesai' => $currentSelesai->copy(),
                'tanggal' => $currentMulai->format('Y-m-d'), // Simpan format tanggal untuk query semester
                'hari'    => $currentMulai->isoFormat('dddd'), // Simpan nama hari (Senin, dst)
                'jam_start' => $currentMulai->format('H:i:s'),
                'jam_end'   => $currentSelesai->format('H:i:s'),
            ];

            if ($tipeBerulang === 'mingguan') {
                $currentMulai->addWeek();
                $currentSelesai->addWeek();
            } else {
                // Default: anggap harian atau sekali saja (loop sekali)
                // Jika logic 'sekali' maka recurringUntil biasanya sama dengan waktuSelesai, jadi loop stop otomatis.
                // Jika harian:
                $currentMulai->addDay();
                $currentSelesai->addDay();
            }
        }

        if (empty($waktuUntukDicek)) {
            return null;
        }

        // ---------------------------------------------------------
        // A. CEK BENTROK KEGIATAN (Event Lain)
        // ---------------------------------------------------------
        $kegiatanBentrok = Kegiatan::where('ruangan_id', $requestData['ruangan_id'])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->whereIn('status', ['disetujui', 'verifikasi_akademik', 'verifikasi_sarpras'])
            ->where(function (Builder $query) use ($waktuUntukDicek) {
                foreach ($waktuUntukDicek as $slot) {
                    $query->orWhere(function (Builder $subQuery) use ($slot) {
                        $subQuery->where('waktu_mulai', '<', $slot['selesai'])
                                ->where('waktu_selesai', '>', $slot['mulai']);
                    });
                }
            })
            ->first();

        if ($kegiatanBentrok) {
            return $kegiatanBentrok;
        }

        // ---------------------------------------------------------
        // B. CEK BENTROK JADWAL PERKULIAHAN (LOGIC BARU)
        // ---------------------------------------------------------
        // Kita cek apakah ada Jadwal Kuliah yang:
        // 1. Harinya sama (Senin/Selasa..)
        // 2. Jamnya tabrakan
        // 3. Semesternya AKTIF pada tanggal yang diminta
        
        $kuliahBentrok = JadwalPerkuliahan::where('ruangan_id', $requestData['ruangan_id'])
            ->where(function (Builder $query) use ($waktuUntukDicek) {
                foreach ($waktuUntukDicek as $slot) {
                    $query->orWhere(function ($q) use ($slot) {
                        $q->where('hari', $slot['hari']) // Cek Hari (misal: Senin)
                        ->where('waktu_mulai', '<', $slot['jam_end'])   // Cek Jam Overlap
                        ->where('waktu_selesai', '>', $slot['jam_start'])
                        // Cek apakah Semester dari jadwal ini mencakup tanggal yang direquest
                        ->whereHas('semester', function($qSemester) use ($slot) {
                            $qSemester->whereDate('tanggal_mulai', '<=', $slot['tanggal'])
                                        ->whereDate('tanggal_selesai', '>=', $slot['tanggal']);
                        });
                    });
                }
            })
            ->with(['semester']) // Eager load untuk info tambahan
            ->first();

        if ($kuliahBentrok) {
            // Return sebagai objek Kegiatan agar format error di frontend konsisten
            return new Kegiatan([
                'nama_kegiatan' => '[KULIAH] ' . $kuliahBentrok->mata_kuliah . ' (' . ($kuliahBentrok->semester->nama ?? '') . ')'
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
            $waktuMulai = Carbon::parse($requestData['waktu_mulai']);
            $waktuSelesai = Carbon::parse($requestData['waktu_selesai']);

            $recurringUntil = !empty($requestData['berulang_sampai'])
                ? Carbon::parse($requestData['berulang_sampai'])->endOfDay()
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

    /**
     * Mencari ruangan alternatif yang kosong pada rentang waktu tertentu,
     * dengan kapasitas minimal sama dengan ruangan yang diminta.
     *
     * @param array $requestData
     * @param int $minKapasitas
     * @return \Illuminate\Support\Collection
     */
    public function getSuggestedRooms(array $requestData, int $minKapasitas = 0): \Illuminate\Support\Collection
    {
        $waktuMulai  = \Carbon\Carbon::parse($requestData['waktu_mulai']);
        $waktuSelesai = \Carbon\Carbon::parse($requestData['waktu_selesai']);
        $ruanganDiminta = $requestData['ruangan_id'];

        // Ambil semua ruangan dengan kapasitas >= yang diminta, kecuali ruangan yg diminta
        $kandidat = \App\Models\Ruangan::where('id', '!=', $ruanganDiminta)
            ->where('kapasitas', '>=', $minKapasitas)
            ->where(function ($query) {
                $query->where('nama', 'like', 'GC-7%')
                    ->orWhere('nama', 'like', 'GC-6%');
            })
            ->get();

        $ruanganKosong = $kandidat->filter(function ($ruangan) use ($requestData, $waktuMulai, $waktuSelesai) {
            // Cek bentrok kegiatan
            $bentrokKegiatan = Kegiatan::where('ruangan_id', $ruangan->id)
                ->whereIn('status', ['disetujui', 'verifikasi_akademik', 'verifikasi_sarpras'])
                ->where('waktu_mulai', '<', $waktuSelesai)
                ->where('waktu_selesai', '>', $waktuMulai)
                ->exists();

            if ($bentrokKegiatan) return false;

            // Cek bentrok jadwal perkuliahan
            $namaHari = $waktuMulai->locale('id')->isoFormat('dddd');
            $bentrokKuliah = \App\Models\JadwalPerkuliahan::where('ruangan_id', $ruangan->id)
                ->where('hari', $namaHari)
                ->where('waktu_mulai', '<', $waktuSelesai->format('H:i:s'))
                ->where('waktu_selesai', '>', $waktuMulai->format('H:i:s'))
                ->whereHas('semester', function ($q) use ($waktuMulai) {
                    $q->whereDate('tanggal_mulai', '<=', $waktuMulai->toDateString())
                    ->whereDate('tanggal_selesai', '>=', $waktuMulai->toDateString());
                })
                ->exists();

            return !$bentrokKuliah;
        });

        return $ruanganKosong->values();
    }
}
