<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SikApplication extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ISSUED = 'issued';

    protected $fillable = [
        'program_item_id',
        'ormawa_id',
        'flow_id',
        'judul_final_kegiatan',
        'timeline_mulai_final',
        'timeline_selesai_final',
        'rencana_tempat',
        'proposal_path',
        'surat_permohonan_path',
        'status_sik',
        'submitted_at',
        'issued_at',
        'issued_by_user_id',
        'nomor_sik_eoffice',
        'catatan_terakhir',
    ];

    protected $casts = [
        'timeline_mulai_final' => 'date',
        'timeline_selesai_final' => 'date',
        'submitted_at' => 'datetime',
        'issued_at' => 'datetime',
    ];

    public function programItem()
    {
        return $this->belongsTo(OrmawaProgramItem::class, 'program_item_id');
    }

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class, 'ormawa_id');
    }

    public function flow()
    {
        return $this->belongsTo(SikVerificationFlow::class, 'flow_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function steps()
    {
        return $this->hasMany(SikApplicationStep::class, 'sik_application_id')->orderBy('step_order');
    }

    public function histories()
    {
        return $this->hasMany(SikHistory::class, 'sik_application_id');
    }

    public function amendments()
    {
        return $this->hasMany(SikAmendment::class, 'sik_application_id');
    }

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class, 'sik_application_id');
    }

    public function scopeIssued($query)
    {
        return $query->where('status_sik', self::STATUS_ISSUED);
    }
}
