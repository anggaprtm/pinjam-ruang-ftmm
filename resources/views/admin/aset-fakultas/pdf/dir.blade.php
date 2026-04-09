<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Inventaris Ruangan - {{ $ruangan->nama }}</title>
    <style>
        /* PORTRAIT */
        @page { size: A4 portrait; margin: 1.5cm 1.5cm 2cm 1.5cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.3; color: #000; }
        
        /* --- KOP SURAT --- */
        .kop-surat { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; position: relative; }
        .kop-surat img { width: 80px; height: 80px; position: absolute; top: 0; left: 0; }
        .kop-text { margin-left: 90px; text-align: center; }
        .kop-text h1 { font-size: 14pt; font-weight: normal; margin: 0; }
        .kop-text h2 { font-size: 14pt; font-weight: bold; margin: 0; }
        .kop-text p { font-size: 9pt; margin: 2px 0; }
        
        /* --- JUDUL DOKUMEN --- */
        .judul-dokumen { text-align: center; margin-bottom: 15px; }
        .judul-dokumen h3 { font-size: 12pt; font-weight: bold; text-decoration: underline; margin: 0; }
        
        /* --- INFO RUANGAN --- */
        .info-ruangan { width: 100%; margin-bottom: 10px; border-collapse: collapse; font-size: 10pt; font-weight: bold; }
        .info-ruangan td { padding: 2px 0; vertical-align: top; }
        .label-info { width: 180px; }
        .colon-info { width: 15px; text-align: center; }
        
        /* --- TABEL ASET --- */
        .table-aset { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 15px; table-layout: fixed; }
        .table-aset th, .table-aset td { border: 1px solid #000; padding: 5px 3px; vertical-align: middle; word-wrap: break-word; }
        .table-aset th { font-weight: bold; text-align: center; }
        
        /* Lebar Kolom Portrait */
        .col-no { width: 5%; text-align: center; }
        .col-nama { width: 22%; }
        .col-kode { width: 18%; font-size: 8pt; }
        .col-merk { width: 16%; font-style: italic; }
        .col-thn { width: 7%; text-align: center; }
        .col-jml { width: 8%; text-align: center; font-weight: bold; }
        .col-ang { width: 11%; text-align: center; }
        .col-ket { width: 13%; }

        /* --- CATATAN & TANDA TANGAN --- */
        .catatan { font-size: 9pt; font-style: italic; margin-bottom: 25px; line-height: 1.4; }
        
        .ttd-container { width: 100%; margin-top: 15px; page-break-inside: avoid; }
        .ttd-table { width: 100%; border: none; font-size: 10pt; }
        .ttd-cell { width: 50%; vertical-align: top; }
        .ttd-kiri { text-align: center; }
        .ttd-kanan { text-align: center; }
        .jabatan { margin-bottom: 65px; }
        .nama-ttd { font-weight: bold; text-decoration: underline; margin-bottom: 2px; }
    </style>
</head>
<body>

    <div class="kop-surat">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo UNAIR">
        <div class="kop-text">
            <h1>UNIVERSITAS AIRLANGGA</h1>
            <h2>FAKULTAS TEKNOLOGI MAJU DAN MULTIDISIPLIN</h2>
            <p>Gedung Kuliah Bersama Kampus C Mulyorejo Surabaya 60115 Telp. 0881036000830</p>
            <p>Laman: https://ftmm.unair.ac.id, e-mail: info@ftmm.unair.ac.id</p>
        </div>
    </div>

    <div class="judul-dokumen">
        <h3>DAFTAR INVENTARIS RUANGAN</h3>
    </div>

    <table class="info-ruangan">
        <tr>
            <td class="label-info">UNIT PENGELOLA BARANG (UPB)</td>
            <td class="colon-info"></td>
            <td></td>
        </tr>
        <tr>
            <td class="label-info">KODE RUANGAN / LOKASI</td>
            <td class="colon-info">:</td>
            <td>-/{{ $ruangan->nama }}</td>
        </tr>
        <tr>
            <td class="label-info">TIPE RUANGAN / LOKASI</td>
            <td class="colon-info">:</td>
            <td>Ruangan Tertutup / Lantai {{ $ruangan->lantai ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-info">GEDUNG</td>
            <td class="colon-info">:</td>
            <td>Gedung Nano</td>
        </tr>
    </table>

    <table class="table-aset">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama">Nama Barang</th>
                <th class="col-kode">Kode Barang</th>
                <th class="col-merk">Merk/Type</th>
                <th class="col-thn">Tahun</th>
                <th class="col-jml">Jumlah</th>
                <th class="col-ang">Anggaran</th>
                <th class="col-ket">Ket</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asets as $i => $aset)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-nama">{{ $aset->nama_barang }}</td>
                    <td class="col-kode">{{ $aset->kode_barang }}</td>
                    <td class="col-merk">{{ $aset->merk ?? '-' }}</td>
                    <td class="col-thn">{{ $aset->tahun_aset ?? '-' }}</td>
                    <td class="col-jml">{{ $aset->jumlah }} Unit</td>
                    <td class="col-ang">{{ $aset->anggaran ?? 'DAMAS' }}</td>
                    <td class="col-ket">{{ $aset->kondisi }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:15px; font-style:italic;">Tidak ada aset di ruangan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="catatan">
        Tidak dibenarkan memindahkan barang – barang yang ada pada daftar ini tanpa sepengetahuan Penanggung Jawab Unit Akuntansi Kuasa Pengguna Barang (UAKPB) dan Penanggung Jawab ruangan ini.
    </div>

    <div class="ttd-container">
        <div style="margin-bottom: 10px;">
            Surabaya, {{ \Carbon\Carbon::parse($tanggalTtd)->translatedFormat('d F Y') }}
        </div>
        <table class="ttd-table">
            <tr>
                <td class="ttd-cell ttd-kiri">
                    <div class="jabatan">Penanggung Jawab UAKPB</div>
                    <div class="nama-ttd">Boedi Rahardjo, S.Sos.</div>
                    <div>NIP. 196907301990031002</div>
                </td>
                <td class="ttd-cell ttd-kanan">
                    <div class="jabatan">Penanggung Jawab Ruangan</div>
                    <div class="nama-ttd">{{ $ruangan->penanggung_jawab ?? '____________________' }}</div>
                    <div>NIK. {{ $ruangan->nik_pj ?? '____________________' }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>