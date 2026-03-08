<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisOrmawa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_jenis',
        'kode',
        'is_active',
    ];

    public function ormawas()
    {
        return $this->hasMany(Ormawa::class, 'jenis_ormawa_id');
    }

    public function verificationFlows()
    {
        return $this->hasMany(SikVerificationFlow::class, 'jenis_ormawa_id');
    }
}
