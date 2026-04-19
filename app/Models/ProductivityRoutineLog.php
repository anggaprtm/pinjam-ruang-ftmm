<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityRoutineLog extends Model
{
    protected $fillable = [
        'routine_task_id', 
        'month', 
        'completed_at', 
        'proof_file_path', 
        'notes', 
        'status'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(ProductivityRoutineTask::class, 'routine_task_id');
    }
}