<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalWfh extends Model
{
    protected $table = 'jadwal_wfh';
    protected $guarded = [];

    // Ubah relasi menjadi jamak (belongsToMany)
    public function users()
    {
        return $this->belongsToMany(User::class, 'jadwal_wfh_user');
    }
}