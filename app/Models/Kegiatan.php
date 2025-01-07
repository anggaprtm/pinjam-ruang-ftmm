<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kegiatan extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'kegiatans';

    protected $dates = [
        'waktu_mulai',
        'waktu_selesai',
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
        'custom_user_name',
        'surat_izin',
        'created_at',
        'updated_at',
        'deleted_at',
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
}