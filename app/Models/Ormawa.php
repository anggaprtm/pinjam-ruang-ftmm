<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ormawa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'jenis_ormawa_id',
        'nama',
        'kode',
        'is_active',
    ];

    public function jenisOrmawa()
    {
        return $this->belongsTo(JenisOrmawa::class, 'jenis_ormawa_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'ormawa_user')->withTimestamps();
    }

    public function programPlans()
    {
        return $this->hasMany(OrmawaProgramPlan::class, 'ormawa_id');
    }

    public function sikApplications()
    {
        return $this->hasMany(SikApplication::class, 'ormawa_id');
    }
}
