<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PermintaanKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permintaan_kegiatans';

    protected $guarded = ['id'];

    protected $dates = [
        'tanggal_kegiatan',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relasi ke Pemohon
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke PIC (Pegawai)
    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    // Relasi ke Kegiatan (Setelah di-approve)
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
    
    // Accessor untuk badge status (Opsional, buat view nanti)
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'proses' => 'info',
            'selesai' => 'success',
            'ditolak' => 'danger'
        ];
        return $colors[$this->status_permintaan] ?? 'secondary';
    }
}