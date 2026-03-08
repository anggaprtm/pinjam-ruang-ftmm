<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrmawaProgramItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_id',
        'kode_proker',
        'nama_rencana',
        'timeline_mulai_rencana',
        'timeline_selesai_rencana',
        'deskripsi_rencana',
        'status_item',
    ];

    public function plan()
    {
        return $this->belongsTo(OrmawaProgramPlan::class, 'plan_id');
    }

    public function sikApplication()
    {
        return $this->hasOne(SikApplication::class, 'program_item_id');
    }
}
