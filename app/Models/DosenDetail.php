<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_lengkap_gelar',
        'nik',
        'nuptk',
        'nidn',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'no_ponsel',
        'npwp',
        'status_kepegawaian',
        'status_keaktifan',
        'homebase_prodi',
        'pangkat_golongan',
        'tgl_mulai_dosen',
        'jabatan_fungsional',
        'jabatan_struktural',
    ];

    // Relasi balik ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}