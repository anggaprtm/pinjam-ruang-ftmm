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

    protected $fillable = [
        'user_id',
        'pic_user_id',
        'nama_kegiatan',
        'jenis_kegiatan',
        'tanggal_kegiatan',
        'waktu_mulai',
        'waktu_selesai',
        'jumlah_peserta',
        'deskripsi',
        'request_konsumsi',
        'waktu_konsumsi',
        'catatan_konsumsi',
        'request_ruang',
        'lampiran',
        'status_permintaan',
        'status_ruang',
        'status_konsumsi',
        'kegiatan_id',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
        'waktu_mulai' => 'datetime:H:i',
        'waktu_selesai' => 'datetime:H:i',
    ];


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
