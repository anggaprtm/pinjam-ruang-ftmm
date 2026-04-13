<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratTugas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_tugas';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'hal_surat',
        'dasar_surat',
        'isi_tugas',
        'hari_tanggal_tugas',
        'waktu_tugas',
        'tanggal_tugas_raw',
        'tanggal_tugas_akhir_raw',
        'tempat_tugas',
        'pakaian',
        'keterangan',
        'pegawai_list',
        'jabatan_penandatangan',
        'nama_penandatangan',
        'nip_penandatangan',
    ];

    public function getNomorFinalAttribute(): string
    {
        return $this->nomor_surat ?: '-- Belum Bernomor --';
    }

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    // Helper: decode pegawai ke array
    public function getPegawaiListArrayAttribute(): array
    {
        return json_decode($this->pegawai_list, true) ?? [];
    }
}