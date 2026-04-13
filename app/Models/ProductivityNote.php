<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityNote extends Model
{
    // Ganti $guarded menjadi $fillable seperti ini:
    protected $fillable = [
        'user_id', 
        'title', 
        'content', 
        'bg_color'
    ];
}