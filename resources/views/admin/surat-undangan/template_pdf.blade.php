<!DOCTYPE html>
<html>
<head>
    <title>Undangan</title>
    <style>
        /* =========================================
           1. FONT & GLOBAL (Berlaku untuk PDF & Preview)
           ========================================= */
        @font-face {
            font-family: 'Times New Roman';
            font-weight: normal;
            font-style: normal;
            src: local("Times New Roman");
        }

        /* Helper Tables & Lists */
        table { width: 100%; border-collapse: collapse; border: 0; }
        td { vertical-align: top; padding: 2px 0; }
        ol { padding-left: 20px; margin: 0; }
        li { margin-bottom: 2px; }

        /* =========================================
           2. LOGIC TAMPILAN (PDF vs PREVIEW)
           ========================================= */
        
        @if(isset($is_pdf) && $is_pdf)
            /* --- MODE PDF (DOMPDF) --- */
            @page { 
                margin: 1cm 1.5cm 1cm 1.5cm; /* Margin Kertas Global */
            }
            
            body { 
                font-family: 'Times New Roman', serif; 
                font-size: 12pt;
                line-height: 1.3;
                background: white;
            }

            /* Hapus batasan wrapper di PDF, biarkan mengalir alami */
            .surat-wrapper {
                display: block;
                width: 100%;
                position: relative;
            }

            /* CRITICAL FIX: Page break yang lebih kuat untuk DOMPDF */
            .page-break {
                page-break-after: always;
                page-break-inside: avoid;
                clear: both;
                display: block;
                height: 0;
                margin: 0;
                padding: 0;
            }

        @else
            /* --- MODE PREVIEW (LAYAR) --- */
            .surat-wrapper {
                font-family: 'Times New Roman', serif; 
                font-size: 12pt;
                line-height: 1.3;
                color: #000;
                background: white;
                width: 210mm;
                min-height: 297mm;
                padding: 1cm 1.5cm 1cm 1.5cm; /* Padding visual sama dengan margin PDF */
                box-shadow: 0 0 10px rgba(0,0,0,0.5);
                margin-bottom: 30px; 
                box-sizing: border-box;
                position: relative;
            }

            /* Visualisasi Garis Putus-putus di Layar */
            .page-break {
                border-top: 4px dashed #aaa;
                margin: 30px 0;
                height: 40px;
                background: #f0f0f0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #666;
                font-family: sans-serif;
                font-weight: bold;
                font-size: 12px;
            }
            .page-break::after {
                content: "--- BATAS HALAMAN (PAGE BREAK) ---";
            }
        @endif

        /* =========================================
           3. KOMPONEN SURAT
           ========================================= */
        
        /* HEADER / KOP */
        .header-container { 
            position: relative; 
            border-bottom: 2px solid #000; 
            padding-bottom: 10px; 
            margin-bottom: 25px; 
        }
        .logo { 
            position: absolute; left: 0; top: 0px; 
            width: 75px; height: auto; 
        }
        .kop-text { margin-left: 90px; text-align: center; }
        .kop-univ { font-size: 14pt; }
        .kop-fakultas { font-size: 16pt; font-weight: bold; }
        .kop-alamat { font-size: 10pt; font-weight: normal; }

        /* TANDA TANGAN */
        .signature-table { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .signer-name {
            white-space: nowrap;
            position: relative; z-index: 10;
        }
        .ttd-image {
            width: 260px; height: auto;
            display: block;
            margin-top: -90px; margin-bottom: -40px; margin-left: -20px;
            position: relative; z-index: 5; opacity: 0.9;
        }

        /* LAMPIRAN TABLE */
        .lampiran-title {
            text-align: center; font-weight: bold; 
            text-decoration: underline; margin-bottom: 20px; font-size: 14pt;
        }
        .table-lampiran { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-lampiran th, .table-lampiran td {
            border: 1px solid #000; padding: 6px; text-align: left; vertical-align: middle;
        }
        .table-lampiran th { background-color: #eee; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

    {{-- =========================================
         HALAMAN 1: SURAT UTAMA
         ========================================= --}}
    <div class="surat-wrapper">
        
        {{-- KOP SURAT --}}
        <div class="header-container">
            @php
                $path = 'assets/img/logo_unair.png'; 
                $src = (isset($is_pdf) && $is_pdf) ? public_path($path) : asset($path);
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

        {{-- INFO SURAT --}}
        <table>
            <tr>
                <td width="12%">Nomor</td><td width="2%">:</td>
                <td width="50%">{{ $data['nomor_surat'] }}</td>
                <td align="right">{{ \Carbon\Carbon::parse($data['tanggal_surat'])->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Hal</td><td>:</td>
                <td colspan="2">{{ $data['hal_surat'] ?? 'Undangan' }}</td>
            </tr>
        </table>
        <br>

        {{-- TUJUAN --}}
        <div>
            Yth.<br>
            @if(isset($data['use_lampiran']) && $data['use_lampiran'])
                Daftar Terlampir
            @else
                @if(count($data['tujuan_surat_list']) > 1)
                    <ol>
                        @foreach($data['tujuan_surat_list'] as $tujuan) <li>{{ $tujuan }}</li> @endforeach
                    </ol>
                @else
                    {{ $data['tujuan_surat_list'][0] ?? '-' }}
                @endif
            @endif
            <div style="margin-top: 5px;">
                Fakultas Teknologi Maju dan Multidisiplin<br>Universitas Airlangga
            </div>
        </div>
        <br>

        <p style="margin-bottom:0;">Bersama ini kami mengundang Saudara untuk hadir pada:</p>
        <table style="margin-left: 30px; width: 90%; margin-top:0;">
            <tr><td width="25%">hari, Tanggal</td><td width="2%">:</td><td>{{ $data['hari_tanggal_acara'] }}</td></tr>
            <tr><td>pukul</td><td>:</td><td>{{ $data['waktu_acara'] }}</td></tr>
            <tr><td>tempat</td><td>:</td><td>{{ $data['tempat_acara'] }}</td></tr>
            <tr><td>agenda</td><td>:</td><td>{{ $data['agenda_acara'] }}</td></tr>
            @if(!empty($data['dresscode']))
            <tr><td><i>dresscode</i></td><td>:</td><td>{{ $data['dresscode'] }}</td></tr>
            @endif
        </table>
        <br>
        <p>Demikian surat undangan ini kami sampaikan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.</p>

        {{-- TTD --}}
        <table class="signature-table">
            <tr>
                <td width="55%"></td>
                <td width="45%">
                    {{ $data['jabatan_penandatangan'] ?? 'Dekan' }},
                    <br><br><br><br><br>
                    @if(!empty($data['ttd_image']))
                        @php
                            $pathTTD = 'assets/img/ttd/' . $data['ttd_image'];
                            $srcTTD = (isset($is_pdf) && $is_pdf) ? public_path($pathTTD) : asset($pathTTD);
                        @endphp
                        <img src="{{ $srcTTD }}" class="ttd-image" alt="TTD">
                    @endif
                    <div class="signer-name">{{ $data['nama_penandatangan'] }}</div>
                    <div>NIP. {{ $data['nip_penandatangan'] }}</div>
                </td>
            </tr>
        </table>
    </div> 
    {{-- END HALAMAN 1 --}}


    {{-- =========================================
         HALAMAN 2: LAMPIRAN (JIKA ADA)
         ========================================= --}}
    
    @if(isset($data['use_lampiran']) && $data['use_lampiran'] && !empty($data['lampiran_table']))
        
        {{-- CRITICAL FIX: Pindahkan page-break KE ATAS wrapper baru --}}
        <div class="page-break"></div>

        <div class="surat-wrapper">
            {{-- JUDUL LAMPIRAN --}}
            <div class="lampiran-title">LAMPIRAN DAFTAR UNDANGAN</div>
            
            {{-- Header Kecil --}}
            <table>
                <tr><td width="15%">Nomor</td><td>: {{ $data['nomor_surat'] }}</td></tr>
                <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($data['tanggal_surat'])->translatedFormat('d F Y') }}</td></tr>
                <tr><td>Perihal</td><td>: {{ $data['hal_surat'] ?? 'Undangan' }}</td></tr>
            </table>
            
            {{-- TABEL DATA --}}
            <table class="table-lampiran">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="40%">Nama</th>
                        <th width="30%">NIP</th>
                        <th width="25%">Jabatan / Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['lampiran_table'] as $index => $row)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $row['nama'] }}</td>
                        <td>{{ $row['nip'] }}</td>
                        <td>{{ $row['jabatan'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>
</html>