<?php

namespace App\Imports;

use App\Models\JadwalPerkuliahan;
use App\Models\Ruangan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class JadwalPerkuliahanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['nama_ruangan'])) {
            \Log::warning('nama_ruangan tidak ditemukan', $row);
            return null; // atau throw error jika ingin hentikan
        }
        
        $ruangan = Ruangan::where('nama', $row['nama_ruangan'])->first();

        return new JadwalPerkuliahan([
            'ruangan_id'      => $ruangan ? $ruangan->id : null,
            'mata_kuliah'     => $row['mata_kuliah'],
            'hari'            => $row['hari'],
            'waktu_mulai'     => $this->parseExcelTime($row['waktu_mulai']), // Format: 'HH:MM'
            'waktu_selesai'   => $this->parseExcelTime($row['waktu_selesai']), // Format: 'HH:MM'
            'berlaku_mulai'   => Carbon::parse($row['berlaku_mulai'])->format('Y-m-d'),
            'berlaku_sampai'  => Carbon::parse($row['berlaku_sampai'])->format('Y-m-d'),
            'tipe'            => $row['tipe'] ?? null,
            'program_studi'   => $row['program_studi'] ?? null,
        ]);
    }

    private function parseExcelTime($value)
    {
        if (is_numeric($value)) {
            return Carbon::instance(Date::excelToDateTimeObject($value))->format('H:i');
        }

        // kalau string langsung, pastikan bisa diparse
        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return '00:00'; // fallback kalau format tidak dikenali
        }
    }
}

