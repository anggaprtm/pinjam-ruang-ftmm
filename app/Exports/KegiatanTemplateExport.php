<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KegiatanTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        // Wajib sama persis dengan yang diharapkan KegiatanImport
        return [
            'judul_kegiatan',
            'nama_pic',
            'jenis_kegiatan',
            'ruangan',
            'tanggal',
            'jam_mulai',
            'jam_selesai',
            'pembimbing_1',
            'pembimbing_2',
            'penguji_1',
            'penguji_2'
        ];
    }

    public function collection()
    {
        // Kita kasih 1 baris contoh data biar user gak bingung formatnya
        return collect([
            [
                'Sidang Skripsi: Budi Santoso', // judul_kegiatan
                'Budi Santoso',                 // nama_pic
                'Sidang Skripsi',               // jenis_kegiatan
                'GC-6.02',                      // ruangan (Harus sama dgn DB)
                '2026-01-25',                   // tanggal (YYYY-MM-DD)
                '08:00',                        // jam_mulai
                '10:00',                        // jam_selesai
                'Prof. Dr. A',                  // pembimbing_1
                'Dr. B, M.T.',                  // pembimbing_2
                'Dr. C, M.Sc.',                 // penguji_1
                'Dr. D, Ph.D.'                  // penguji_2
            ],
            [
                'Rapat Dosen Dept. TI',
                'Sekretaris Dept',
                'Rapat',
                'R. Rapat 1',
                '2026-01-26',
                '13:00',
                '15:00',
                null, // Kosongkan dosen jika bukan sidang
                null,
                null,
                null
            ]
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Bikin baris Header jadi Bold biar tegas
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}