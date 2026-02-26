<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiLog extends Model
{
    use HasFactory;
    
    protected $casts = [
        'notif_history' => 'array',
    ];
    
    // Tambahkan batas_jam_masuk dan batas_jam_keluar di sini
    protected $fillable = [
        'user_id', 'tanggal', 'jam_masuk', 'jam_keluar', 
        'batas_jam_masuk', 'batas_jam_keluar', 
        'status', 'keterangan', 'notif_history'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}