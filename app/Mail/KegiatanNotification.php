<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KegiatanNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $kegiatan;

    public function __construct($kegiatan)
    {
        $this->kegiatan = $kegiatan;
    }

    public function build()
    {
        return $this->subject('Notifikasi Pengajuan Kegiatan Baru')
                    ->view('emails.kegiatan_notification')
                    ->with([
                        'namaKegiatan' => $this->kegiatan->nama_kegiatan,
                        'ruangan' => $this->kegiatan->ruangan->nama,
                        'waktuMulai' => $this->kegiatan->waktu_mulai,
                        'waktuSelesai' => $this->kegiatan->waktu_selesai,
                        'pemohon' => $this->kegiatan->user->name,
                    ]);
    }
}
