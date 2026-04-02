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
        'is_active'
    ];
}
