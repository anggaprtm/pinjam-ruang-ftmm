<!DOCTYPE html>
<html>
<head>
    <title>Undangan</title>
    <style>
        /* CSS UNTUK PDF GENERATOR (DOMPDF) */
        @page { margin: 0; } /* Margin diatur di body/wrapper */
        
        /* WRAPPER CLASS: KUNCI AGAR FONT TIDAK BOCOR
           Kita ganti selector 'body' menjadi '.surat-wrapper' 
        */
        .surat-wrapper {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
            padding: 15mm 20mm 15mm 20mm; /* Margin A4 standar */
            background: white;
        }

        /* Khusus saat mode PDF (DomPDF butuh width 100%) */
        @if(isset($is_pdf) && $is_pdf)
            body { margin: 0; padding: 0; }
            .surat-wrapper { width: 100%; }
        @endif

        /* KOP SURAT */
        .header-container { position: relative; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 25px; min-height: 100px; }
        .logo { position: absolute; left: 0; top: 5px; width: 75px; height: auto; }
        .kop-text { margin-left: 90px; text-align: center; }
        .kop-univ { font-size: 14pt; }
        .kop-fakultas { font-size: 16pt; font-weight: bold; }
        .kop-alamat { font-size: 10pt; font-weight: normal; }

        /* TABEL RAJA */
        table { width: 100%; border-collapse: collapse; border: 0; }
        td { vertical-align: top; padding: 2px 0; }
        
        /* LIST TUJUAN (OL) */
        ol { padding-left: 20px; margin: 0; }
        li { margin-bottom: 2px; }

        /* TANDA TANGAN */
        .signature-wrapper {
            float: right;
            width: 45%; 
            margin-top: 30px;
            margin-right: 20mm;
            text-align: left;
        }
        .signer-name {
            white-space: nowrap; 
        }
    </style>
</head>
<body>

    {{-- WRAPPER UTAMA --}}
    <div class="surat-wrapper">

        {{-- KOP SURAT --}}
        <div class="header-container">
            @php
                $path = 'assets/img/logo_unair.png'; 
                if (isset($is_pdf) && $is_pdf) {
                    $src = public_path($path);
                } else {
                    $src = asset($path);
                }
            @endphp
            
            <img src="{{ $src }}" class="logo" alt="Logo">

            <div class="kop-text">
                <span class="kop-univ">UNIVERSITAS AIRLANGGA</span><br>
                <span class="kop-fakultas">FAKULTAS TEKNOLOGI MAJU DAN MULTIDISIPLIN</span><br>
                <span class="kop-alamat">
                    Gedung Nano Kampus C Mulyorejo Surabaya 60115 Telp. (031) 59182123<br>
                    Laman: https://ftmm.unair.ac.id, e-mail: info@ftmm.unair.ac.id
                </span>
            </div>
        </div>

        {{-- NOMOR & TANGGAL --}}
        <table>
            <tr>
                <td width="12%">Nomor</td>
                <td width="2%">:</td>
                <td width="50%">{{ $data['nomor_surat'] }}</td>
                <td align="right">
                    {{ \Carbon\Carbon::parse($data['tanggal_surat'])->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td>Hal</td>
                <td>:</td>
                <td colspan="2">{{ $data['hal_surat'] ?? 'Undangan' }}</td>
            </tr>
        </table>

        <br>

        {{-- TUJUAN --}}
        <div>
            Yth.<br>
            @if(count($data['tujuan_surat_list']) > 1)
                <ol>
                    @foreach($data['tujuan_surat_list'] as $tujuan)
                        <li>{{ $tujuan }}</li>
                    @endforeach
                </ol>
            @else
                {{ $data['tujuan_surat_list'][0] ?? '-' }}
            @endif
            
            <div style="margin-top: 5px;">
                Fakultas Teknologi Maju dan Multidisiplin<br>
                Universitas Airlangga
            </div>
        </div>

        <br>

        {{-- ISI SURAT --}}
        <p>Bersama ini kami mengundang Saudara untuk hadir pada:</p>

        <table style="margin-left: 30px; width: 90%; ">
            <tr>
                <td width="25%">hari, tanggal</td>
                <td width="2%">:</td>
                <td>{{ $data['hari_tanggal_acara'] }}</td>
            </tr>
            <tr>
                <td>pukul</td>
                <td>:</td>
                <td>{{ $data['waktu_acara'] }}</td>
            </tr>
            <tr>
                <td>tempat</td>
                <td>:</td>
                <td>{{ $data['tempat_acara'] }}</td>
            </tr>
            <tr>
                <td>agenda</td>
                <td>:</td>
                <td>{{ $data['agenda_acara'] }}</td>
            </tr>
            @if(!empty($data['dresscode']))
            <tr>
                <td>Pakaian</td>
                <td>:</td>
                <td>{{ $data['dresscode'] }}</td>
            </tr>
            @endif
        </table>
        <br>
        <p>
            Demikian surat undangan ini kami sampaikan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.
        </p>

        {{-- TANDA TANGAN --}}
        <div class="signature-wrapper">
            {{ $data['jabatan_penandatangan'] ?? 'Dekan' }},
            <br><br><br><br>
            <div class="signer-name">{{ $data['nama_penandatangan'] }}</div>
            <div>NIP. {{ $data['nip_penandatangan'] }}</div>
        </div>

    </div> {{-- End Wrapper --}}
</body>
</html>