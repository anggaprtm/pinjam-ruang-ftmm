<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    protected $guarded = ['id'];

    // Cek apakah mobil sedang jalan
    public function perjalananAktif()
    {
        return $this->hasOne(RiwayatPerjalanan::class)
                    ->where('status', 'berlangsung')
                    ->latest();
    }

    public function tripBerlangsung()
    {
        // Ambil satu perjalanan terakhir yang statusnya 'berlangsung'
        return $this->hasOne(RiwayatPerjalanan::class)->where('status', 'berlangsung')->latest();
    }
}