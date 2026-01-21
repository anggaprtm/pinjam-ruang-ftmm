<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiwayatPerjalanan extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'riwayat_perjalanans';

    protected $dates = [
        'waktu_mulai',
        'waktu_selesai',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_id',
        'mobil_id',
        'tujuan',
        'keperluan',
        'waktu_mulai',
        'waktu_selesai',
        'status',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // --- RELASI ---

    public function mobil()
    {
        return $this->belongsTo(Mobil::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // --- MUTATORS & ACCESSORS (COPY DARI KEGIATAN.PHP) ---

    // 1. Waktu Mulai
    // Get: Tampilkan sesuai config panel (biar sama kayak Kegiatan)
    public function getWaktuMulaiAttribute($value)
    {
        return $value 
            ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) 
            : null;
    }

    // Set: Simpan ke DB format standar Y-m-d H:i:s
    public function setWaktuMulaiAttribute($value)
    {
        $this->attributes['waktu_mulai'] = $value 
            ? Carbon::parse(trim($value))->format('Y-m-d H:i:s') 
            : null;
    }

    // 2. Waktu Selesai
    public function getWaktuSelesaiAttribute($value)
    {
        return $value 
            ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) 
            : null;
    }

    public function setWaktuSelesaiAttribute($value)
    {
        $this->attributes['waktu_selesai'] = $value 
            ? Carbon::parse(trim($value))->format('Y-m-d H:i:s') 
            : null;
    }
}