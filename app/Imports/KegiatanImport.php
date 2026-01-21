<?php

namespace App\Imports;

use App\Models\Kegiatan;
use App\Models\Ruangan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KegiatanImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // 1. Cari ID Ruangan berdasarkan Nama (Pastikan nama di Excel sama persis dgn Database)
        $ruangan = Ruangan::where('nama', $row['ruangan'])->first();
        
        if (!$ruangan) {
            // Kalau ruangan tidak ditemukan, bisa return null (skip) atau throw error
            // Disini kita skip saja baris ini
            return null; 
        }

        // 2. Format Waktu (Gabungkan Tanggal + Jam)
        // Asumsi format Excel tanggal: '2026-01-25' dan jam: '13:00'
        try {
            // Excel kadang membaca tanggal sebagai serial number, kita paksa parsing
            $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal']);
            $formatTanggal = $tanggal->format('Y-m-d');
        } catch (\Exception $e) {
            // Fallback kalau inputnya string biasa '2026-01-25'
            $formatTanggal = $row['tanggal'];
        }

        $waktuMulai = Carbon::parse($formatTanggal . ' ' . $row['jam_mulai']);
        $waktuSelesai = Carbon::parse($formatTanggal . ' ' . $row['jam_selesai']);

        // 3. Tentukan Jenis Kegiatan otomatis (bisa dari Excel atau default)
        $jenis = $row['jenis_kegiatan'] ?? 'Sidang Skripsi'; 

        return new Kegiatan([
            'nama_kegiatan'      => $row['judul_kegiatan'], // Misal: "Sidang Skripsi: [Nama Mhs]"
            'deskripsi'          => 'Diimport via Excel',
            'jenis_kegiatan'     => $jenis,
            
            // Mapping Kolom Dosen
            'dosen_pembimbing_1' => $row['pembimbing_1'] ?? null,
            'dosen_pembimbing_2' => $row['pembimbing_2'] ?? null,
            'dosen_penguji_1'    => $row['penguji_1'] ?? null,
            'dosen_penguji_2'    => $row['penguji_2'] ?? null,

            'ruangan_id'         => $ruangan->id,
            'waktu_mulai'        => $waktuMulai,
            'waktu_selesai'      => $waktuSelesai,
            
            // Data Peserta / Mahasiswa
            'nama_pic'           => $row['nama_pic'], 
            'nomor_telepon'      => $row['no_hp'] ?? '080000000000', // Default dummy
            
            // Default System
            'user_id'            => auth()->id(), // Dianggap admin yg input
            'status'             => 'disetujui',  // Langsung setujui karena import admin
        ]);
    }

    public function rules(): array
    {
        return [
            'judul_kegiatan' => 'required',
            'ruangan'        => 'required',
            'tanggal'        => 'required',
            'jam_mulai'      => 'required',
            'jam_selesai'    => 'required',
            'nama_mahasiswa' => 'required',
        ];
    }
}