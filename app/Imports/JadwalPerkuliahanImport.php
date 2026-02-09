<?php

namespace App\Imports;

use App\Models\JadwalPerkuliahan;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Services\EventService; // Import Service
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

class JadwalPerkuliahanImport implements ToModel, WithHeadingRow
{
    private $semesterId;
    private $eventService; // Tambahkan properti

    // Inject EventService lewat Constructor
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;

        $activeSemester = Semester::active()->first();
        if (!$activeSemester) {
            throw new \Exception("Gagal Import: Belum ada Semester Aktif.");
        }
        $this->semesterId = $activeSemester->id;
    }

    public function model(array $row)
    {
        if (!isset($row['nama_ruangan'])) return null;

        $ruangan = Ruangan::where('nama', $row['nama_ruangan'])->first();
        if (!$ruangan) {
            // Opsional: Throw error biar user tau ruangannya salah
            throw new \Exception("Ruangan tidak ditemukan: " . $row['nama_ruangan']);
        }

        // 1. SIAPKAN DATA UNTUK CEK BENTROK
        // Format array harus sama persis dengan yang diharapkan EventService
        $waktuMulai = $this->parseExcelTime($row['waktu_mulai']);
        $waktuSelesai = $this->parseExcelTime($row['waktu_selesai']);
        
        $checkData = [
            'ruangan_id'    => $ruangan->id,
            'hari'          => $row['hari'],
            'waktu_mulai'   => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'semester_id'   => $this->semesterId,
            // 'id' tidak dikirim karena ini create baru (tidak exclude diri sendiri)
        ];

        // 2. LAKUKAN PENGECEKAN KE SERVICE
        
        // A. Cek Tabrakan dengan Jadwal Kuliah Lain
        $bentrokKuliah = $this->eventService->isRoomTakenForLecture($checkData);
        if ($bentrokKuliah) {
            // HAPUS TANDA PETIK SATU (') DI SEKITAR VARIABEL
            throw new \Exception("GAGAL IMPORT (BENTROK KULIAH): Mata Kuliah [{$row['mata_kuliah']}] bentrok dengan [{$bentrokKuliah->mata_kuliah}] di Ruang {$row['nama_ruangan']} pada hari {$row['hari']} pukul {$waktuMulai}-{$waktuSelesai}.");
        }

        // B. Cek Tabrakan dengan Kegiatan/Event
        $bentrokKegiatan = $this->eventService->isRoomTakenByKegiatan($checkData);
        if ($bentrokKegiatan) {
            throw new \Exception("GAGAL IMPORT (BENTROK KEGIATAN): Mata Kuliah [{$row['mata_kuliah']}] bentrok dengan Kegiatan [{$bentrokKegiatan->nama_kegiatan}].");
        }

        // 3. JIKA AMAN, LANJUT SIMPAN
        return new JadwalPerkuliahan([
            'semester_id'     => $this->semesterId,
            'ruangan_id'      => $ruangan->id,
            'kode_matkul'     => $row['kode_matkul'],
            'mata_kuliah'     => $row['mata_kuliah'],
            'dosen'           => $row['dosen'] ?? null,
            'hari'            => $row['hari'],
            'waktu_mulai'     => $waktuMulai,
            'waktu_selesai'   => $waktuSelesai,
            'tipe'            => $row['tipe'] ?? 'Kuliah Reguler',
            'program_studi'   => $row['kelas'] ?? $row['program_studi'] ?? 'Umum',
        ]);
    }

    private function parseExcelTime($value)
    {
        if (empty($value)) return '00:00';
        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value))->format('H:i');
        }
        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return '00:00'; 
        }
    }
}