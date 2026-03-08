<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SikApplicationStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sik_application_id',
        'step_order',
        'role_target',
        'status_step',
        'acted_by_user_id',
        'acted_at',
        'notes',
        'due_at',
        'sla_days',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(SikApplication::class, 'sik_application_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }
}
