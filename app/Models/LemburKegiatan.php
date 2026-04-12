<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LemburKegiatan extends Model
{
    use HasFactory;

    protected $table = 'lembur_kegiatan';

    protected $fillable = [
        'tanggal',
        'nama_kegiatan',
        'deskripsi',
        'file_surat_tugas',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    /** Admin yang membuat kegiatan ini */
    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /** Pivot: semua entry assignment pegawai */
    public function pegawaiAssignments()
    {
        return $this->hasMany(LemburKegiatanPegawai::class, 'lembur_kegiatan_id');
    }

    /** Shortcut: langsung ke User via pivot */
    public function pegawais()
    {
        return $this->belongsToMany(User::class, 'lembur_kegiatan_pegawai', 'lembur_kegiatan_id', 'user_id')
                    ->withPivot(['peran', 'status_validasi'])
                    ->withTimestamps();
    }

    // ─── Accessors ────────────────────────────────────────────

    /** Jumlah pegawai yang validasinya 'valid' */
    public function getTotalValidAttribute(): int
    {
        return $this->pegawaiAssignments()->where('status_validasi', 'valid')->count();
    }

    /** Jumlah total pegawai yang diassign */
    public function getTotalAssignAttribute(): int
    {
        return $this->pegawaiAssignments()->count();
    }
}