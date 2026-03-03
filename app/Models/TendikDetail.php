<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TendikDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nama_lengkap', 'nik_ktp', 'jenis_kelamin', 'tempat_lahir', 
        'tanggal_lahir', 'alamat', 'no_ponsel', 'npwp', 'nik', 'tmt', 
        'pangkat_golongan', 'nama_jabatan', 'sub_bagian', 'status_kepegawaian'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}