<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplayContent extends Model
{
    protected $fillable = [
        'display_config_id',
        'type',
        'value',
        'image_path',
        'duration',
        'order'
    ];
}
