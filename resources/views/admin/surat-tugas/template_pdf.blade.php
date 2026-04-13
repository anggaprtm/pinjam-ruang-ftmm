<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas</title>
    <style>
        @if(isset($is_pdf) && $is_pdf)
            @page { size: A4 portrait; margin: 1cm 1.5cm 1.5cm 1.5cm; }
            body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.4; color: #000; background: white; }
            .surat-wrapper { display: block; width: 100%; }
        @else
            /* STYLING KHUSUS UNTUK PREVIEW HTML (Browser) */
            body { margin: 0; padding: 0; background-color: transparent; }
            .surat-wrapper {
                font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.4; color: #000;
                background: white; 
                width: 210mm; 
                min-height: 297mm; 
                padding: 1cm 1.5cm 1.5cm 1.5cm; 
                box-sizing: border-box;
                box-shadow: 0 4px 15px rgba(0,0,0,0.5);
                position: relative;
                border: none; /* Hapus sisa border */
                margin-top: 0; 
            }
            
            /* Penanda Halaman 2 (Lampiran) di ruang kosong antar kertas */
            .page-break::before { 
                content: '✂️ HALAMAN 2 (LAMPIRAN)'; 
                position: absolute; 
                top: -22px; 
                left: 50%; 
                transform: translateX(-50%); 
                color: #292929; 
                font-size: 10pt; 
                font-family: sans-serif;
                font-weight: bold;
                letter-spacing: 1px;
                background: transparent; /* Pastikan tidak ada warna abu-abu */
                border: none;
                padding: 0;
            }
        @endif

        /* KOP SURAT */
        .header-container { position: relative; border-bottom: 3px solid #000; padding-bottom: 8px; margin-bottom: 20px; }
        .logo { position: absolute; left: 0; top: 0; width: 75px; height: auto; }
        .kop-text { margin-left: 90px; text-align: center; }
        .kop-univ { font-size: 13pt; }
        .kop-fakultas { font-size: 15pt; font-weight: bold; }
        .kop-alamat { font-size: 9pt; }

        /* JUDUL SURAT */
        .judul-surat { text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 18px 0 4px 0; letter-spacing: 1px; }
        .nomor-surat { text-align: center; font-size: 11pt; margin-bottom: 18px; }

        /* TABEL INFO & ISI */
        table { width: 100%; border-collapse: collapse; border: 0; }
        td { vertical-align: top; padding: 2px 0; }
        .label-col { width: 20%; white-space: nowrap; }
        .colon-col { width: 3%; text-align: center; }

        /* TABEL LAMPIRAN PEGAWAI */
        .table-pegawai { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 11pt; }
        .table-pegawai th, .table-pegawai td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .table-pegawai th { text-align: center; font-weight: bold; }
        .col-no { width: 5%; text-align: center; }
        .col-nama { width: 35%; }
        .col-nip { width: 25%; }
        .col-jabatan { width: 35%; }

        /* TTD */
        .signature-table { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .ttd-image { width: 260px; height: auto; display: block; margin-top: -90px; margin-bottom: -40px; margin-left: -20px; position: relative; z-index: 5; opacity: 0.9; }

        /* LAMPIRAN HEADER */
        .lampiran-header { text-align: left; margin-bottom: 20px; line-height: 1.5; font-size: 12pt; }
        p { margin: 6px 0; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

{{-- HALAMAN 1: SURAT TUGAS --}}
<div class="surat-wrapper">
    {{-- KOP SURAT --}}
    <div class="header-container">
        @php
            $path = 'assets/img/logo_unair.png';
            $src  = (isset($is_pdf) && $is_pdf) ? public_path($path) : asset($path);
        @endphp
        <img src="{{ $src }}" class="logo" alt="Logo UNAIR">
        <div class="kop-text">
            <div class="kop-univ">UNIVERSITAS AIRLANGGA</div>
            <div class="kop-fakultas">FAKULTAS TEKNOLOGI MAJU DAN MULTIDISIPLIN</div>
            <div class="kop-alamat">
                Gedung Nano Kampus C Mulyorejo Surabaya 60115 Telp. (031) 59182123<br>
                Laman: https://ftmm.unair.ac.id &nbsp;|&nbsp; e-mail: info@ftmm.unair.ac.id
            </div>
        </div>
    </div>

    {{-- JUDUL --}}
    <div class="judul-surat">SURAT TUGAS</div>
    <div class="nomor-surat">Nomor: {{ $data['nomor_surat'] }}</div>

    <p>Yang bertanda tangan di bawah ini:</p>
    <table style="margin-left: 20px; width: 95%; margin-bottom: 15px;">
        <tr>
            <td class="label-col">Nama</td>
            <td class="colon-col">:</td>
            <td>{{ $data['nama_penandatangan'] }}</td>
        </tr>
        <tr>
            <td class="label-col">NIP</td>
            <td class="colon-col">:</td>
            <td>{{ $data['nip_penandatangan'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Jabatan</td>
            <td class="colon-col">:</td>
            <td>{{ $data['jabatan_penandatangan'] }} Fakultas Teknologi Maju dan Multidisiplin</td>
        </tr>
    </table>

    <p style="margin-top: 10px; line-height: 1.5;">
        Menugaskan kepada daftar nama terlampir untuk mengikuti <strong>{{ $data['isi_tugas'] }}</strong> Universitas Airlangga pada :
    </p>

    <table style="margin-left: 20px; width: 95%; margin-bottom: 15px;">
        <tr>
            <td class="label-col">Hari, tanggal</td>
            <td class="colon-col">:</td>
            <td>{{ $data['hari_tanggal_tugas'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Waktu</td>
            <td class="colon-col">:</td>
            <td>{{ $data['waktu_tugas'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Tempat</td>
            <td class="colon-col">:</td>
            <td>{{ $data['tempat_tugas'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Acara</td>
            <td class="colon-col">:</td>
            <td>{{ $data['isi_tugas'] }}</td>
        </tr>
        @if(!empty($data['pakaian']))
        <tr>
            <td class="label-col">Pakaian</td>
            <td class="colon-col">:</td>
            <td>{{ $data['pakaian'] }}</td>
        </tr>
        @endif
        @if(!empty($data['keterangan']))
        <tr>
            <td class="label-col">Keterangan</td>
            <td class="colon-col">:</td>
            <td style="text-align: justify;">{{ $data['keterangan'] }}</td>
        </tr>
        @endif
    </table>

    <p>Demikian atas perhatian dan kerjasamanya, kami sampaikan terima kasih.</p>

    {{-- TTD DEKAN --}}
    <table class="signature-table">
        <tr>
            <td width="45%"></td>
            <td width="55%">
                {{ \Carbon\Carbon::parse($data['tanggal_surat'])->translatedFormat('j F Y') }}<br>
                {{ $data['jabatan_penandatangan'] }},
                <br><br><br><br><br>
                @if(!empty($data['ttd_image']))
                    @php
                        $pathTTD = 'assets/img/ttd/' . $data['ttd_image'];
                        $srcTTD  = (isset($is_pdf) && $is_pdf) ? public_path($pathTTD) : asset($pathTTD);
                    @endphp
                    <img src="{{ $srcTTD }}" class="ttd-image" alt="TTD">
                @endif
                <div class="signer-name">{{ $data['nama_penandatangan'] }}</div>
                <div>NIP {{ $data['nip_penandatangan'] }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- HALAMAN 2: LAMPIRAN (Di-Force Pindah Halaman) --}}
<div class="surat-wrapper page-break">
    <div class="lampiran-header">
        Lampiran Surat Tugas {{ $data['isi_tugas'] }}<br>
        Nomor &nbsp;&nbsp;&nbsp;: {{ $data['nomor_surat'] }}<br>
        Tanggal &nbsp;: {{ \Carbon\Carbon::parse($data['tanggal_surat'])->translatedFormat('d F Y') }}
    </div>

    <table class="table-pegawai">
        <thead>
            <tr>
                <th class="col-no">NO</th>
                <th class="col-nama">NAMA</th>
                <th class="col-nip">NIP / NIK</th>
                <th class="col-jabatan">JABATAN / TUGAS</th>
            </tr>
        </thead>
        <tbody>
            @php $pegawaiList = $data['pegawai_list'] ?? []; @endphp
            @foreach($pegawaiList as $idx => $p)
            <tr>
                <td class="col-no">{{ $idx + 1 }}</td>
                <td class="col-nama">{{ $p['nama'] }}</td>
                <td class="col-nip">{{ !empty($p['nip']) ? $p['nip'] : '-' }}</td>
                <td class="col-jabatan">{{ !empty($p['jabatan']) ? $p['jabatan'] : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>