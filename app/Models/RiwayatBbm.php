<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RiwayatBbm extends Model
{
    protected $fillable = ['tanggal', 'km_odometer', 'biaya'];
    protected $casts = ['tanggal' => 'datetime'];
}