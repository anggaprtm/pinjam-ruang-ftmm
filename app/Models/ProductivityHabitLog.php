<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityHabitLog extends Model
{
    protected $fillable = [
        'habit_id', 
        'tanggal', 
        'is_completed'
    ];
}