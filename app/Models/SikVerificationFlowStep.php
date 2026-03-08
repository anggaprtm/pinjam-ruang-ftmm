<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SikVerificationFlowStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'flow_id',
        'step_order',
        'role_target',
        'action_type',
        'label_step',
        'sla_days',
    ];

    public function flow()
    {
        return $this->belongsTo(SikVerificationFlow::class, 'flow_id');
    }
}
