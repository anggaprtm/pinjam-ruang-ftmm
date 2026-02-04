<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratUndangan extends Model
{
    use HasFactory;

    // Nama tabel (opsional jika mengikuti standar Laravel plural, tapi aman ditulis)
    protected $table = 'surat_undangans';

    // Daftar kolom yang boleh diisi via create()
    protected $fillable = [
        'nomor_surat',
        'hal_surat',
        'tanggal_surat',
        'tujuan_surat',
        
        // Detail Acara
        'hari_tanggal_acara',
        'waktu_acara',
        'tempat_acara',
        'agenda_acara',
        'dresscode',

        // Penandatangan
        'jabatan_penandatangan',
        'nama_penandatangan',
        'nip_penandatangan',
    ];

    // Opsional: Agar tanggal_surat otomatis jadi objek Carbon saat diambil
    protected $casts = [
        'tanggal_surat' => 'date',
    ];
}