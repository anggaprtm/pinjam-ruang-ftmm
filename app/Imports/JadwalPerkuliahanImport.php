<?php

namespace App\Imports;

use App\Models\JadwalPerkuliahan;
use App\Models\Ruangan;
use App\Models\Semester; // Import Model Semester
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

class JadwalPerkuliahanImport implements ToModel, WithHeadingRow
{
    private $semesterId;

    public function __construct()
    {
        // Ambil Semester Aktif sekali saja saat class diinisialisasi
        // Ini mencegah query berulang untuk setiap baris excel
        $activeSemester = Semester::active()->first();

        if (!$activeSemester) {
            // Opsional: Throw error agar user sadar belum ada semester aktif
            throw new \Exception("Gagal Import: Belum ada Semester Aktif yang diatur di sistem.");
        }

        $this->semesterId = $activeSemester->id;
    }

    public function model(array $row)
    {
        // Validasi dasar nama ruangan
        if (!isset($row['nama_ruangan'])) {
            return null; 
        }
        
        // Cari ruangan berdasarkan nama (Case insensitive handling opsional tapi disarankan)
        $ruangan = Ruangan::where('nama', $row['nama_ruangan'])->first();

        if (!$ruangan) {
            Log::warning('Import Jadwal: Ruangan tidak ditemukan', ['nama' => $row['nama_ruangan']]);
            // return null; // Uncomment jika ingin skip baris yang ruangannya tidak ketemu
        }

        return new JadwalPerkuliahan([
            'semester_id'     => $this->semesterId, // Inject ID Semester Aktif
            'ruangan_id'      => $ruangan ? $ruangan->id : null,
            'kode_matkul'     => $row['kode_matkul'],
            'mata_kuliah'     => $row['mata_kuliah'],
            'dosen'           => $row['dosen'] ?? null, // Sesuaikan dengan nama kolom di Excel
            'hari'            => $row['hari'],
            'waktu_mulai'     => $this->parseExcelTime($row['waktu_mulai']), 
            'waktu_selesai'   => $this->parseExcelTime($row['waktu_selesai']),
            'tipe'            => $row['tipe'] ?? 'Kuliah Reguler',
            'program_studi'   => $row['program_studi'] ?? null,
            // berlaku_mulai & berlaku_sampai SUDAH DIHAPUS
        ]);
    }

    private function parseExcelTime($value)
    {
        if (empty($value)) return '00:00';

        if (is_numeric($value)) {
            // Jika format Excel Time (float/serial)
            return Carbon::instance(Date::excelToDateTimeObject($value))->format('H:i');
        }

        // Jika string "08:00"
        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return '00:00'; 
        }
    }
}