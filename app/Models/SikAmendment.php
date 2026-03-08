<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SikAmendment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sik_application_id',
        'requested_by_user_id',
        'alasan_perubahan',
        'old_payload_json',
        'new_payload_json',
        'status_amendment',
        'effective_at',
    ];

    protected $casts = [
        'old_payload_json' => 'array',
        'new_payload_json' => 'array',
        'effective_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(SikApplication::class, 'sik_application_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
