<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPerkuliahan extends Model
{
    use HasFactory;

    protected $table = 'jadwal_perkuliahan';

    protected $fillable = [
        'kode_matkul',
        'mata_kuliah',
        'ruangan_id',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
        'berlaku_mulai',
        'berlaku_sampai',
        'tipe',
        'program_studi',
        'dosen',
        'semester_id',
    ];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}

