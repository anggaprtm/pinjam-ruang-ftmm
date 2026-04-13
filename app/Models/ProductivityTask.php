<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityTask extends Model
{
    protected $fillable = [
        'user_id', 
        'title', 
        'tag', 
        'description', 
        'priority', 
        'status', 
        'deadline_at',
        'remind_morning',
        'remind_h_minus_1',
        'is_reminded_h_1'
    ];
}