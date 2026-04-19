<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivitySubTask extends Model
{
    protected $fillable = ['task_id', 'title', 'is_completed'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(ProductivityTask::class, 'task_id');
    }
}