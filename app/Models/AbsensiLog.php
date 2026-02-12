<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiLog extends Model
{
    use HasFactory;
    
    protected $casts = [
        'notif_history' => 'array', // Penting biar bisa langsung diakses sbg array di PHP
    ];
    protected $fillable = ['user_id', 'tanggal', 'jam_masuk', 'jam_keluar', 'status', 'keterangan', 'notif_history'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
