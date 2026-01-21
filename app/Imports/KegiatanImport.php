<?php

namespace App\Imports;

use App\Models\Kegiatan;
use Maatwebsite\Excel\Concerns\ToModel;

class KegiatanImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Kegiatan([
            //
        ]);
    }
}
