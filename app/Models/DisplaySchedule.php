<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisplaySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_config_id',
        'start_time',
        'end_time',
        'mode'
    ];

    public function config()
    {
        return $this->belongsTo(DisplayConfig::class, 'display_config_id');
    }
}