@extends('layouts.admin')
@section('content')
<div class="content">
    {{-- Salam Pembuka & Waktu --}}
    <div class="welcome-banner">
        <h4>Selamat Datang Kembali, {{ Auth::user()->name }}!</h4>
        <p class="mb-0">Berikut adalah ringkasan aktivitas peminjaman ruangan hari ini.</p>
        <div id="current-time" class="fs-5 mt-2"></div>
    </div>

    {{-- TAMPILAN UNTUK ADMIN (DASHBOARD STATISTIK) --}}
    @can('home_access')
        {{-- Kartu Statistik --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-ruangan"><i class="fas fa-door-open"></i></div>
                    <div class="info">
                        <div class="stat-number">{{ $ruanganCount ?? 0 }}</div>
                        <div class="stat-label">Total Ruangan</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-menunggu"><i class="fas fa-clock"></i></div>
                    <div class="info">
                        <div class="stat-number">{{ $kegiatanMenungguCount ?? 0 }}</div>
                        <div class="stat-label">Kegiatan Menunggu</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-disetujui"><i class="fas fa-check-circle"></i></div>
                    <div class="info">
                        <div class="stat-number">{{ $kegiatanDisetujuiCount ?? 0 }}</div>
                        <div class="stat-label">Kegiatan Disetujui</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-total"><i class="fas fa-list-alt"></i></div>
                    <div class="info">
                        <div class="stat-number">{{ $kegiatanTotalCount ?? 0 }}</div>
                        <div class="stat-label">Total Kegiatan</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Kegiatan dengan Tab --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="kegiatanTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="hari-ini-tab" data-bs-toggle="tab" data-bs-target="#hari-ini" type="button" role="tab">Kegiatan Hari Ini</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="besok-tab" data-bs-toggle="tab" data-bs-target="#besok" type="button" role="tab">Kegiatan Besok</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="kegiatanTabContent">
                    {{-- Konten Tab Hari Ini --}}
                    <div class="tab-pane fade show active" id="hari-ini" role="tabpanel">
                        @include('partials.kegiatanTable', ['kegiatans' => $kegiatanHariIni, 'empty_message' => 'Tidak ada kegiatan yang dijadwalkan untuk hari ini.'])
                    </div>
                    {{-- Konten Tab Besok --}}
                    <div class="tab-pane fade" id="besok" role="tabpanel">
                        @include('partials.kegiatanTable', ['kegiatans' => $kegiatanBesok, 'empty_message' => 'Tidak ada kegiatan yang dijadwalkan untuk besok.'])
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- TAMPILAN UNTUK USER BIASA (INFORMASI) --}}
    @can('info_access')
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h5 class="mb-0">Informasi Peminjaman Ruang</h5></div>
            <div class="card-body">
                <h2>Cara Melakukan Peminjaman Ruang</h2>
                <ol class="list-group list-group-numbered">
                    <li class="list-group-item">Klik "Cari Ruang" pada menu sidebar.</li>
                    <li class="list-group-item">Masukkan waktu mulai dan waktu selesai kegiatan.</li>
                    <li class="list-group-item">Ketikkan kapasitas ruang yang dibutuhkan.</li>
                    <li class="list-group-item">Kemudian klik "Cari". Ruang yang tersedia akan muncul di bawahnya.</li>
                    <li class="list-group-item">Pilih ruang yang tersedia, kemudian klik "Pinjam Ruang".</li>
                    <li class="list-group-item">Isi semua data yang diperlukan pada form peminjaman.</li>
                    <li class="list-group-item">Upload berkas Surat Peminjaman Ruang jika diperlukan.</li>
                    <li class="list-group-item">Klik tombol "Save" atau "Simpan".</li>
                    <li class="list-group-item">Kegiatan berhasil dibuat dan akan muncul di menu "Kegiatan" untuk diproses.</li>
                    <li class="list-group-item">Harap tunggu proses verifikasi dari pihak terkait.</li>
                    <li class="list-group-item">Ketika kegiatan telah disetujui, Anda dapat menggunakan ruangan sesuai jadwal.</li>
                </ol>
            </div>
        </div>
    @endcan
</div>
@endsection
