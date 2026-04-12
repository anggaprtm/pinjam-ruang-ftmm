<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LemburKegiatanPegawai extends Model
{
    use HasFactory;

    protected $table = 'lembur_kegiatan_pegawai';

    protected $fillable = [
        'lembur_kegiatan_id',
        'user_id',
        'peran',
        'status_validasi',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    public function kegiatan()
    {
        return $this->belongsTo(LemburKegiatan::class, 'lembur_kegiatan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accessor: ambil data presensi yang berkaitan ─────────

    /**
     * Ambil AbsensiLog pegawai ini di tanggal kegiatannya.
     * Berguna untuk ditampilkan di view detail tanpa query tambahan
     * (jika sudah eager load dengan load(['absensiLog'])).
     */
    public function absensiLog()
    {
        return $this->hasOneThrough(
            AbsensiLog::class,
            User::class,
            'id',           // FK di users
            'user_id',      // FK di absensi_logs
            'user_id',      // Local key di pivot (user_id)
            'id'            // Local key di users
        );
        // Catatan: hasOneThrough di sini tidak bisa filter by tanggal secara langsung.
        // Gunakan query manual di controller untuk filter by tanggal.
        // Ini hanya disediakan sebagai shortcut relasi dasar.
    }
}