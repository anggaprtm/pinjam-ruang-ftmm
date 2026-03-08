<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SikVerificationFlow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'jenis_ormawa_id',
        'nama_flow',
        'is_active',
    ];

    public function jenisOrmawa()
    {
        return $this->belongsTo(JenisOrmawa::class, 'jenis_ormawa_id');
    }

    public function steps()
    {
        return $this->hasMany(SikVerificationFlowStep::class, 'flow_id')->orderBy('step_order');
    }
}
