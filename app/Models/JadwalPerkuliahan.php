<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPerkuliahan extends Model
{
    use HasFactory;

    protected $table = 'jadwal_perkuliahan';

    protected $fillable = [
        'mata_kuliah',
        'ruangan_id',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
        'berlaku_mulai',
        'berlaku_sampai',
        'tipe',
        'program_studi',
    ];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }
}

