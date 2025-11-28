<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Kegiatan extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'kegiatan';

    protected $dates = [
        'waktu_mulai',
        'waktu_selesai',
        'revisi_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'ruangan_id',
        'nama_kegiatan',
        'waktu_mulai',
        'waktu_selesai',
        'deskripsi',
        'user_id',
        'status',
        'nama_pic',
        'nomor_telepon',
        'surat_izin',
        'revisi_by',
        'revisi_at',
        'revisi_level',
        'revisi_notes',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    protected $casts = [
        'status' => 'string',
    ];
    

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function getWaktuMulaiAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setWaktuMulaiAttribute($value)
    {
        $this->attributes['waktu_mulai'] = $value 
        ? Carbon::parse(trim($value))->format('Y-m-d H:i:s') 
        : null;
    }

    public function getWaktuSelesaiAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setWaktuSelesaiAttribute($value)
    {
        $this->attributes['waktu_selesai'] = $value 
        ? Carbon::parse(trim($value))->format('Y-m-d H:i:s') 
        : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function revisiBy()
    {
        return $this->belongsTo(User::class, 'revisi_by');
    }

    public function histories()
    {
        return $this->hasMany(\App\Models\KegiatanHistory::class, 'kegiatan_id')->orderBy('created_at');
    }

    /**
     * Clear cached pending kegiatan count when model changes
     */
    protected static function booted()
    {
        static::created(function ($model) {
            Cache::forget('pending_kegiatan_count');
        });

        static::updated(function ($model) {
            Cache::forget('pending_kegiatan_count');
        });

        static::deleted(function ($model) {
            Cache::forget('pending_kegiatan_count');
        });

        static::restored(function ($model) {
            Cache::forget('pending_kegiatan_count');
        });
    }
}
