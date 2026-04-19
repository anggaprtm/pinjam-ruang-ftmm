<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityTaskAttachment extends Model
{
    protected $fillable = ['task_id', 'file_name', 'file_path'];

    public function task()
    {
        return $this->belongsTo(ProductivityTask::class, 'task_id');
    }
}
