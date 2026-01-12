<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_barang',
        'deskripsi',
        'stok',
        'foto',
    ];

    public function kegiatans()
    {
        return $this->belongsToMany(Kegiatan::class, 'barang_kegiatan')
                    ->withPivot('jumlah', 'status')
                    ->withTimestamps();
    }
}
