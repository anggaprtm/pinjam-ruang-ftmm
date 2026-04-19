<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityTaskComment extends Model
{
    protected $fillable = ['task_id', 'user_id', 'comment'];

    public function task()
    {
        return $this->belongsTo(ProductivityTask::class, 'task_id');
    }

    // Untuk menampilkan nama siapa yang komen
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
