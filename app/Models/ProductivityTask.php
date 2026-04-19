<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityTask extends Model
{
    protected $fillable = [
        'user_id',
        'assigned_by', // <-- Tambahkan ini
        'title',
        'tag',
        'description',
        'priority',
        'status',
        'deadline_at',
        'recurrence',
        'is_archived',
        'remind_morning',
        'remind_h_minus_1',
        'is_reminded_h_1'
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    // Relasi untuk mengetahui siapa pemilik tugas ini
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // Relasi untuk mengetahui siapa yang mendelegasikan tugas ini
    public function assigner()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by');
    }

    // Relasi ke Sub-Tasks
    public function subTasks()
    {
        return $this->hasMany(ProductivitySubTask::class, 'task_id');
    }

    // Relasi ke Attachments (Lampiran)
    public function attachments()
    {
        return $this->hasMany(ProductivityTaskAttachment::class, 'task_id');
    }
}