<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PeringatanKedisiplinanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pegawai;
    public $totalTerlambat;
    public $bulan;

    public function __construct($pegawai, $totalTerlambat, $bulan)
    {
        $this->pegawai = $pegawai;
        $this->totalTerlambat = $totalTerlambat;
        $this->bulan = $bulan;
    }

    public function build()
    {
        return $this->subject('Peringatan Kedisiplinan Kehadiran')
            ->from('kepegawaian@ftmm.unair.ac.id', 'Kepegawaian FTMM UNAIR')
            ->view('emails.peringatan_kedisiplinan');
    }
}
