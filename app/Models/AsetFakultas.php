<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsetFakultas extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'aset_fakultas';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'kode_barang',
        'tahun_aset',
        'nama_barang',
        'kondisi',
        'anggaran', // <- Tambahkan ini
        'merk',
        'deskripsi',
        'ruangan_id',
        'lokasi_text',
    ];

    const KONDISI_OPTIONS = [
        'Baik'         => 'Baik',
        'Rusak Ringan' => 'Rusak Ringan',
        'Rusak Berat'  => 'Rusak Berat',
    ];

    const ANGGARAN_OPTIONS = [
        'DAMAS' => 'DAMAS',
        'HIBAH' => 'HIBAH',
        'IKU'   => 'IKU',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    /**
     * Lokasi yang ditampilkan: nama ruangan (jika sudah di-link) atau teks lokasi asli dari Excel.
     */
    public function getLokasiLabelAttribute(): string
    {
        return $this->ruangan?->nama ?? $this->lokasi_text ?? '-';
    }
}
