<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityRoutineTask extends Model
{
    protected $fillable = [
        'assigned_by', 
        'user_id', 
        'title', 
        'target_months', 
        'year', 
        'is_active'
    ];

    // Otomatis convert JSON dari database menjadi Array di PHP
    protected $casts = [
        'target_months' => 'array', 
        'is_active' => 'boolean',
    ];

    // Relasi ke pembuat tugas (Atasan)
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Relasi ke pelaksana tugas (Staf)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke bukti log pelaporan
    public function logs()
    {
        return $this->hasMany(ProductivityRoutineLog::class, 'routine_task_id');
    }
}