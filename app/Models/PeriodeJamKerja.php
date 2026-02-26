<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeJamKerja extends Model
{
    use HasFactory;

    protected $table = 'periode_jam_kerjas';

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_masuk',
        'jam_pulang_senin_kamis',
        'jam_pulang_jumat',
    ];
}