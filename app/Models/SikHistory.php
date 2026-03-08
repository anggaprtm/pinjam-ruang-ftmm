<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'sik_application_id',
        'actor_user_id',
        'event',
        'payload_json',
        'created_at',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(SikApplication::class, 'sik_application_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
