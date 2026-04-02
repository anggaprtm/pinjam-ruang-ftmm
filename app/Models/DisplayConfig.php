<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplayConfig extends Model
{
    protected $fillable = [
        'location',
        'mode',
        'content_type',
        'content_value',
        'start_time',
        'end_time',
        'image_path',
        'is_active'
    ];

    public function contents()
    {
        return $this->hasMany(DisplayContent::class)->orderBy('order');
    }
    public function schedules()
    {
        return $this->hasMany(DisplaySchedule::class);
    }
}
