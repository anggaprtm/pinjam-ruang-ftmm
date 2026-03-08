<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrmawaProgramPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ormawa_id',
        'tahun',
        'dibuat_oleh_user_id',
        'status_plan',
    ];

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class, 'ormawa_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_user_id');
    }

    public function programItems()
    {
        return $this->hasMany(OrmawaProgramItem::class, 'plan_id');
    }

    // Backward-compatible alias for existing controllers/views
    public function items()
    {
        return $this->programItems();
    }
}
