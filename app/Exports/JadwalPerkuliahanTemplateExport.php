<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class JadwalPerkuliahanTemplateExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'nama_ruangan',
            'mata_kuliah',
            'hari',
            'waktu_mulai',
            'waktu_selesai',
            'berlaku_mulai',
            'berlaku_sampai',
            'tipe',
            'program_studi',
        ];
    }
}