<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AgendaFakultas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agenda_fakultas';

    protected $fillable = [
        'judul',
        'deskripsi',
        'kategori',
        'warna',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'is_all_day',
        'tampil_di_signage',
        'tampil_countdown',
        'urutan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_selesai'  => 'date',
        'is_all_day'       => 'boolean',
        'tampil_di_signage'=> 'boolean',
        'tampil_countdown' => 'boolean',
    ];

    // ── Relasi ──────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ──────────────────────────────────────────────────

    /** Hanya yang ditampilkan di signage & belum lewat */
    public function scopeUntukSignage($query)
    {
        return $query
            ->where('tampil_di_signage', true)
            ->where('tanggal_mulai', '>=', Carbon::today())
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('urutan', 'asc');
    }

    /** Hanya yang punya countdown aktif */
    public function scopeCountdown($query)
    {
        return $query
            ->where('tampil_countdown', true)
            ->where('tanggal_mulai', '>=', Carbon::today())
            ->orderBy('tanggal_mulai', 'asc');
    }

    // ── Accessors ───────────────────────────────────────────────

    /** Hitung sisa hari */
    public function getSisaHariAttribute(): int
    {
        return (int) Carbon::today()->diffInDays($this->tanggal_mulai, false);
    }

    /** Label sisa waktu */
    public function getSisaWaktuLabelAttribute(): string
    {
        $hari = $this->sisa_hari;
        if ($hari === 0) return 'Hari ini';
        if ($hari === 1) return 'Besok';
        return "{$hari} hari lagi";
    }

    /** Apakah event berlangsung hari ini */
    public function getIsOngoingAttribute(): bool
    {
        $today = Carbon::today();
        $end   = $this->tanggal_selesai ?? $this->tanggal_mulai;
        return $today->between($this->tanggal_mulai, $end);
    }
}