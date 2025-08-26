@extends('layouts.admin')
@section('content')
<div class="content">
    {{-- Salam Pembuka & Waktu (Tampil untuk semua) --}}
    <div class="welcome-banner">
        <h4>Selamat Datang Kembali, {{ Auth::user()->name }}!</h4>
        <p class="mb-0">Berikut adalah ringkasan aktivitas peminjaman ruangan hari ini.</p>
        <div id="current-time" class="fs-5 mt-2"></div>
    </div>

    {{-- =============================================================== --}}
    {{-- == TAMPILAN UNTUK ADMIN (DASHBOARD STATISTIK) == --}}
    {{-- =============================================================== --}}
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

        {{-- Daftar Kegiatan Terdekat --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Kegiatan Terdekat (5 Berikutnya)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Kegiatan</th>
                                <th>Peminjam</th>
                                <th>Ruangan</th>
                                <th>Waktu Mulai</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kegiatanTerdekat as $kegiatan)
                                <tr>
                                    <td><strong>{{ $kegiatan->nama_kegiatan }}</strong></td>
                                    <td>{{ $kegiatan->user->name ?? '-' }}</td>
                                    <td><span class="badge-ruangan">{{ $kegiatan->ruangan->nama ?? '-' }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($kegiatan->waktu_mulai)->translatedFormat('l, d M Y, H:i') }}</td>
                                    <td class="text-center">
                                        @php
                                            $statusClass = str_replace('_', '-', $kegiatan->status);
                                            $statusText = ucwords(str_replace('_', ' ', $kegiatan->status));
                                        @endphp
                                        <span class="badge-status badge-status-{{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Tidak ada kegiatan yang akan datang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endcan

    {{-- =============================================================== --}}
    {{-- == TAMPILAN UNTUK USER BIASA (INFORMASI) == --}}
    {{-- =============================================================== --}}
    @can('info_access')
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Informasi Peminjaman Ruang</h5>
            </div>
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
