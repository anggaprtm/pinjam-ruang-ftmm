<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>Yth. {{ $pegawai->name ?? 'Bapak/Ibu' }},</p>

    <p>
        Berdasarkan hasil evaluasi kehadiran bulan <strong>{{ $bulan }}</strong>,
        tercatat bahwa Saudara telah mengalami keterlambatan sebanyak
        <strong>{{ $totalTerlambat }} kali</strong>.
    </p>

    <p>
        Sehubungan dengan hal tersebut, kami mengingatkan agar dapat meningkatkan
        kedisiplinan kehadiran sesuai dengan ketentuan yang berlaku.
    </p>

    <p>
        Apabila terdapat kendala atau klarifikasi, silakan menghubungi unit terkait.
    </p>

    <p>
        Demikian disampaikan, atas perhatian Saudara kami ucapkan terima kasih.
    </p>

    <br>
    <p>
        Hormat kami,<br>
        <strong>Kepegawaian Fakultas Teknologi Maju dan Multidisplin</strong><br>
        Universitas Airlangga
    </p>
</body>
</html>
