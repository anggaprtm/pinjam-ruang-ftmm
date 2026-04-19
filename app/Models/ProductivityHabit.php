<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityHabit extends Model
{
    protected $fillable = [
        'user_id', 
        'name', 
        'icon', 
        'reminder_time'
    ];

    public function logs()
    {
        return $this->hasMany(ProductivityHabitLog::class, 'habit_id');
    }
}