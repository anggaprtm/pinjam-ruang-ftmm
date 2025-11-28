<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanHistory extends Model
{
    public $table = 'kegiatan_histories';

    public $timestamps = false; // we use created_at explicitly

    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'action',
        'note',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
