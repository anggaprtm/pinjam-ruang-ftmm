@extends('layouts.admin')

@section('styles')
@parent
<style>
    /* CSS Khusus Timeline */
    .timeline {
        border-left: 2px solid #e9ecef;
        padding-left: 1.5rem;
        position: relative;
        list-style: none;
        margin-bottom: 0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-icon {
        position: absolute;
        left: -2.15rem;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.85rem;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }
    .timeline-date {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    .timeline-content {
        font-size: 0.9rem;
        color: #333;
    }
    
    /* CSS Quick Actions */
    .quick-action-btn {
        transition: all 0.2s ease;
        border: none;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
    }

    /* --- CUSTOM WARNA TAB APPROVAL --- */
    .nav-pills .nav-link.active, 
    .nav-pills .show > .nav-link {
        background-color: #741847 !important; 
        color: #ffffff !important;
    }
    .nav-pills .nav-link {
        color: #6c757d; 
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
        color: #741847; 
    }

    /* =========================================
       MEDIA QUERIES KHUSUS MOBILE (RESPONSIVE)
       ========================================= */
    @media (max-width: 767.98px) {
        /* Bikin tombol pintasan full-width di HP */
        .quick-actions-wrapper {
            flex-direction: column;
        }
        .quick-action-btn {
            width: 100%;
            text-align: left;
            padding: 0.75rem 1rem !important;
            display: flex;
            align-items: center;
        }
        .quick-action-btn i {
            width: 25px;
            text-align: center;
            margin-right: 10px !important;
        }

        /* OVERRIDE STAT CARD ASLI KHUSUS UNTUK HP */
        .stat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 15px 10px !important;
            position: relative;
        }
        
        /* Mereset icon-container bawaan admin template agar ke tengah di HP */
        .stat-card .icon-container {
            position: relative !important;
            width: 45px !important;
            height: 45px !important;
            font-size: 1.1rem !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            border-radius: 50% !important;
            margin-right: 0px !important;
            margin-bottom: 8px;
            right: auto !important; 
            top: auto !important;
            float: none !important;
        }

        .stat-card .info {
            width: 100%;
            padding: 0 !important;
        }
        .stat-label {
            font-size: 0.75rem !important;
            line-height: 1.2;
            white-space: normal !important;
        }
        .stat-number {
            font-size: 1.25rem !important;
            margin-bottom: 2px;
        }

        /* Sesuaikan ukuran teks pada informasi header */
        .card-header h6, .card-header h5 {
            font-size: 1rem !important;
        }
        
        /* Tombol copy jadwal dibikin berjejer rapi di HP */
        .jadwal-btn-group {
            width: 100%;
            justify-content: space-between;
        }
        .jadwal-btn-group .btn {
            flex: 1;
            font-size: 0.75rem;
            padding: 0.4rem;
        }
    }
</style>
@endsection

@section('content')
<div class="content dashboard-home">

    {{-- WELCOME BANNER --}}
    <div class="welcome-banner mb-3 p-3 p-md-4 rounded shadow-sm bg-primary" style="background: linear-gradient(135deg, #741847, #a9246a);">
        <h4 class="fw-bold text-white mb-1">
            Selamat Datang, {{ Auth::user()->name }}!
        </h4>

        @can('home_access')
            <p class="mb-0 text-white-50 small d-none d-md-block">
                Dashboard monitoring layanan Fakultas Teknologi Maju dan Multidisiplin.
            </p>
        @endcan

        <div id="current-time" class="mt-2 text-white fw-semibold small"></div>
    </div>

    @can('home_access')

        {{-- QUICK ACTIONS (PINTASAN CEPAT) --}}
        <div class="mb-4">
            <div class="text-muted small fw-bold text-uppercase mb-2 d-flex align-items-center">
                <i class="fas fa-bolt text-warning me-2"></i> Pintasan Cepat
            </div>
            <div class="d-flex quick-actions-wrapper gap-2">
                <a href="{{ route('admin.permintaan-kegiatan.create') }}" class="btn btn-warning quick-action-btn shadow-sm rounded-pill px-3 fw-bold">
                    <i class="fas fa-concierge-bell me-1"></i> Buat Permintaan
                </a>
                <a href="{{ route('admin.kegiatan.create') }}" class="btn quick-action-btn shadow-sm rounded-pill px-3 fw-bold" style="background-color: #20c997; color: white;">
                    <i class="fas fa-door-open me-1"></i> Jadwalkan Ruang
                </a>
                <a href="{{ route('admin.aset-fakultas.create') }}" class="btn btn-primary quick-action-btn shadow-sm rounded-pill px-3 fw-bold">
                    <i class="fas fa-box-open me-1"></i> Tambah Aset
                </a>
                <a href="{{ route('admin.riwayat-perjalanan.create') }}" class="btn btn-danger quick-action-btn shadow-sm rounded-pill px-3 fw-bold">
                    <i class="fas fa-car me-1"></i> Jadwal Mobil Dinas
                </a>
                <a href="{{ route('admin.display-config.index') }}" class="btn quick-action-btn shadow-sm rounded-pill px-3 fw-bold" style="background-color: #20a2c9; color: white;">
                    <i class="fas fa-cog me-1"></i> Config Display
                </a>
            </div>
        </div>

        {{-- STATISTIK COMMAND CENTER (8 KOTAK) --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container bg-info text-white"><i class="fas fa-door-open"></i></div>
                    <div class="info">
                        <div class="stat-number text-info">{{ $kegiatanMenungguCount ?? 0 }}</div>
                        <div class="stat-label">Menunggu Verifikasi Ruang</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container bg-success text-white"><i class="fas fa-check-circle"></i></div>
                    <div class="info">
                        <div class="stat-number text-success">{{ $kegiatanDisetujuiCount ?? 0 }}</div>
                        <div class="stat-label">Kegiatan Disetujui</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container bg-warning text-dark"><i class="fas fa-concierge-bell"></i></div>
                    <div class="info">
                        <div class="stat-number text-warning">{{ $pendingPermintaan->count() ?? 0 }}</div>
                        <div class="stat-label">Permintaan Layanan Baru</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container bg-secondary text-white"><i class="fas fa-dolly"></i></div>
                    <div class="info">
                        <div class="stat-number text-secondary">{{ $barangDipinjam ?? 0 }}</div>
                        <div class="stat-label">Barang Sedang Dipinjam</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container bg-primary text-white"><i class="fas fa-boxes"></i></div>
                    <div class="info">
                        <div class="stat-number text-primary">{{ number_format($totalAset ?? 0) }}</div>
                        <div class="stat-label">Total Aset Fakultas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container bg-danger text-white"><i class="fas fa-tools"></i></div>
                    <div class="info">
                        <div class="stat-number text-danger">{{ number_format($asetRusak ?? 0) }}</div>
                        <div class="stat-label">Aset Kondisi Rusak</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container text-white" style="background-color: #20c997;"><i class="fas fa-user-check"></i></div>
                    <div class="info">
                        <div class="stat-number" style="color: #20c997;">{{ $hadirHariIni ?? 0 }}</div>
                        <div class="stat-label">Pegawai Hadir Hari Ini</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm h-100">
                    <div class="icon-container text-white" style="background-color: #fd7e14;"><i class="fas fa-user-clock"></i></div>
                    <div class="info">
                        <div class="stat-number" style="color: #fd7e14;">{{ $terlambatHariIni ?? 0 }}</div>
                        <div class="stat-label">Pegawai Terlambat</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL INFORMASI (3 KOLOM BAWAH) --}}
        <div class="row g-3 mb-4">

            {{-- 1. STATUS DRIVER --}}
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white border-0 d-flex align-items-center flex-wrap gap-2" style="min-height: 65px;">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="fas fa-car-side text-primary me-2"></i> Status Driver
                        </h6>
                        <div class="ms-auto flex-shrink-0">
                            @if(($isMobilOnDuty ?? false) === true)
                                <span class="badge rounded-pill bg-danger px-3 py-2">ON DUTY</span>
                            @else
                                <span class="badge rounded-pill bg-success px-3 py-2">Standby</span>
                            @endif
                        </div>
                    </div>

                    @if(!($isMobilOnDuty ?? false) && !empty($nextTrip))
                        <div class="px-3 pt-3">
                            <div class="alert alert-warning py-2 mb-0 small d-flex align-items-start gap-2">
                                <i class="fas fa-clock mt-1"></i>
                                <div>
                                    <div class="fw-bold">Ada jadwal terdekat</div>
                                    <div class="text-muted">
                                        {{ \Carbon\Carbon::parse($nextTrip->getRawOriginal('waktu_mulai'))->format('d M Y, H:i') }}
                                        - {{ \Carbon\Carbon::parse($nextTrip->getRawOriginal('waktu_selesai'))->format('H:i') }}
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $nextTrip->tujuan ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="list-group list-group-flush flex-grow-1">
                        @if(($isMobilOnDuty ?? false) && !empty($ongoingTrip))
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="fw-bold text-primary" style="font-size: 1.05rem;">{{ $ongoingTrip->mobil->nama_mobil ?? '-' }}</div>
                                    @if(!empty($ongoingTrip->mobil->plat_nomor))
                                        <span class="badge bg-dark">{{ $ongoingTrip->mobil->plat_nomor }}</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size: .92rem;">
                                    <div class="mb-1"><i class="fas fa-user me-2"></i> <span class="fw-semibold text-dark">Driver:</span> {{ $ongoingTrip->driver->name ?? '-' }}</div>
                                    <div class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> <span class="fw-semibold text-dark">Tujuan:</span> {{ $ongoingTrip->tujuan ?? '-' }}</div>
                                    <div><i class="fas fa-clipboard-list me-2"></i> <span class="fw-semibold text-dark">Keperluan:</span> {{ $ongoingTrip->keperluan ?? '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="list-group-item border-0 border-top py-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $mobilFakultas->nama_mobil ?? 'Mobil Fakultas' }}</div>
                                    @if(!empty($mobilFakultas->plat_nomor))
                                        <span class="badge bg-dark">{{ $mobilFakultas->plat_nomor }}</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size: .92rem;">
                                    <div class="mb-1"><i class="fas fa-user me-2"></i> <span class="fw-semibold text-dark">Driver:</span> -</div>
                                    <div class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> <span class="fw-semibold text-dark">Tujuan:</span> -</div>
                                    <div><i class="fas fa-clipboard-list me-2"></i> <span class="fw-semibold text-dark">Keperluan:</span> -</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-0 text-center py-3 mt-auto">
                        <a href="{{ route('admin.riwayat-perjalanan.index') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">Lihat Logbook</a>
                    </div>
                </div>
            </div>

            {{-- 2. APPROVAL & PERMINTAAN (TABS) --}}
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white border-0 d-flex align-items-center px-3" style="min-height: 65px;">
                        {{-- Tabs Navigation --}}
                        <ul class="nav nav-pills nav-fill gap-1 w-100 mb-0 flex-nowrap overflow-auto" id="approvalTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-2 small fw-bold text-nowrap" id="ruang-tab" data-bs-toggle="tab" data-bs-target="#ruang" type="button" role="tab">
                                    <i class="fas fa-door-open me-1"></i> Ruang
                                    @if(($pendingApproval->count() ?? 0) > 0)
                                        <span class="badge bg-danger ms-1">{{ $pendingApproval->count() }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 small fw-bold text-nowrap" id="layanan-tab" data-bs-toggle="tab" data-bs-target="#layanan" type="button" role="tab">
                                    <i class="fas fa-concierge-bell me-1"></i> Layanan
                                    @if(($pendingPermintaan->count() ?? 0) > 0)
                                        <span class="badge bg-danger ms-1">{{ $pendingPermintaan->count() }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-0 d-flex flex-column flex-grow-1">
                        <div class="tab-content flex-grow-1 h-100" id="approvalTabsContent">
                            
                            {{-- Tab 1: Peminjaman Ruang --}}
                            <div class="tab-pane fade show active h-100" id="ruang" role="tabpanel">
                                <div class="d-flex flex-column h-100">
                                    @if($pendingApproval->isEmpty())
                                        <div class="d-flex flex-column justify-content-center align-items-center text-muted flex-grow-1 py-4">
                                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                            <p class="small mb-0">Semua pengajuan diproses.</p>
                                        </div>
                                    @else
                                        <div class="list-group list-group-flush dashboard-scroll flex-grow-1" style="max-height: 310px; overflow-y: auto;">
                                            @foreach($pendingApproval as $keg)
                                                <a href="{{ route('admin.kegiatan.index') }}" class="list-group-item list-group-item-action border-light py-3">
                                                    <div class="fw-bold text-dark mb-1 d-flex justify-content-between align-items-center gap-2">
                                                        <div class="text-truncate">{{ $keg->nama_kegiatan ?? '-' }}</div>
                                                        <span class="badge bg-warning text-dark flex-shrink-0">Pending</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                        <div class="small text-muted"><i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($keg->waktu_mulai)->format('d M, H:i') }}</div>
                                                        <span class="badge bg-info text-white">{{ $keg->ruangan->nama ?? 'TBA' }}</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="card-footer bg-white border-top text-center py-3 mt-auto">
                                        <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">Verifikasi Kegiatan</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Permintaan Layanan --}}
                            <div class="tab-pane fade h-100" id="layanan" role="tabpanel">
                                <div class="d-flex flex-column h-100">
                                    @if($pendingPermintaan->isEmpty())
                                        <div class="d-flex flex-column justify-content-center align-items-center text-muted flex-grow-1 py-4">
                                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                            <p class="small mb-0">Tidak ada permintaan baru.</p>
                                        </div>
                                    @else
                                        <div class="list-group list-group-flush dashboard-scroll flex-grow-1" style="max-height: 310px; overflow-y: auto;">
                                            @foreach($pendingPermintaan as $req)
                                                <a href="{{ route('admin.permintaan-kegiatan.show', $req->id) }}" class="list-group-item list-group-item-action border-light py-3">
                                                    <div class="d-flex align-items-start gap-3">
                                                        <div class="flex-grow-1">
                                                            <div class="fw-bold text-primary mb-1 text-truncate" style="max-width: 180px;">{{ $req->nama_kegiatan ?? '-' }}</div>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @if($req->request_ruang) <span class="badge bg-info">Ruang</span> @endif
                                                                @if($req->request_konsumsi) <span class="badge bg-warning">Konsumsi</span> @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-end flex-shrink-0">
                                                            <div class="small text-muted mb-1">{{ optional($req->created_at)->diffForHumans() }}</div>
                                                            <div class="small text-muted"><i class="fas fa-user me-1"></i>{{ $req->user->name ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="card-footer bg-white border-top text-center py-3 mt-auto">
                                        <a href="{{ route('admin.permintaan-kegiatan.index') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">Kelola Permintaan</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. LIVE ACTIVITY TIMELINE --}}
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white border-0 d-flex align-items-center" style="min-height: 65px;">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="fas fa-history text-primary me-2"></i> Jejak Aktivitas Terkini
                        </h6>
                    </div>
                    <div class="card-body dashboard-scroll" style="max-height: 350px; overflow-y: auto;">
                        <ul class="timeline m-0">
                            @forelse($activities as $act)
                                <li class="timeline-item">
                                    <div class="timeline-icon {{ $act['color'] }}">
                                        <i class="{{ $act['icon'] }}"></i>
                                    </div>
                                    <div class="timeline-date">{{ \Carbon\Carbon::parse($act['time'])->diffForHumans() }}</div>
                                    <div class="timeline-content">{!! $act['text'] !!}</div>
                                </li>
                            @empty
                                <p class="text-muted small text-center py-4">Belum ada aktivitas tercatat.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        {{-- JADWAL PEMAKAIAN RUANG --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-calendar-alt me-2"></i> Jadwal Pemakaian Ruang
                </h5>

                <div class="d-flex flex-wrap jadwal-btn-group gap-2" role="group">
                    <button class="btn btn-sm btn-outline-success" id="copyHariIniBtn" @if($kegiatanHariIni->isEmpty()) disabled @endif>
                        <i class="fas fa-copy me-1"></i> Salin Hari Ini
                    </button>
                    <button class="btn btn-sm btn-outline-info" id="copyBesokBtn" @if($kegiatanBesok->isEmpty()) disabled @endif>
                        <i class="fas fa-file-alt me-1"></i> Salin Besok
                    </button>
                    <a href="{{ route('admin.statistics.index') }}" class="btn btn-sm btn-outline-warning text-nowrap">
                        <i class="fas fa-chart-bar me-1"></i> Lihat Statistik
                    </a>
                </div>
            </div>

            <div class="card-body">
                <textarea id="jadwalHariIniClipboard" class="d-none">{{ trim($jadwalHariIniText ?? '') }}</textarea>
                <textarea id="jadwalBesokClipboard" class="d-none">{{ trim($jadwalBesokText ?? '') }}</textarea>

                <ul class="nav nav-tabs flex-nowrap overflow-auto" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-nowrap" id="hari-ini-tab" data-bs-toggle="tab" data-bs-target="#hari-ini" type="button" role="tab">
                            Hari Ini
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-nowrap" id="besok-tab" data-bs-toggle="tab" data-bs-target="#besok" type="button" role="tab">
                            Besok
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-3" id="myTabContent">
                    <div class="tab-pane fade show active" id="hari-ini" role="tabpanel">
                        {{-- BUNGKUS DENGAN TABLE-RESPONSIVE AGAR BISA DIGESER DI HP --}}
                        <div class="table-responsive">
                            @include('partials.kegiatanTable', ['kegiatans' => $kegiatanHariIni])
                        </div>
                    </div>
                    <div class="tab-pane fade" id="besok" role="tabpanel">
                        <div class="table-responsive">
                            @include('partials.kegiatanTable', ['kegiatans' => $kegiatanBesok])
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endcan

    {{-- USER BIASA --}}
    @cannot('home_access')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Informasi Peminjaman Ruang</h5>
            </div>
            <div class="card-body">
                <h2 class="h5 mb-3">Cara Melakukan Peminjaman Ruang</h2>
                <ol class="list-group list-group-numbered small">
                    <li class="list-group-item border-0 px-2 py-1">Klik "Cari Ruang" atau icon 🔍 pada menu sidebar.</li>
                    <li class="list-group-item border-0 px-2 py-1">Masukkan waktu mulai dan waktu selesai kegiatan.</li>
                    <li class="list-group-item border-0 px-2 py-1">Ketikkan kapasitas ruang yang dibutuhkan.</li>
                    <li class="list-group-item border-0 px-2 py-1">Kemudian klik "Cari". Ruang yang tersedia akan muncul di bawahnya.</li>
                    <li class="list-group-item border-0 px-2 py-1">Pilih ruang yang tersedia, kemudian klik "Pinjam Ruang".</li>
                    <li class="list-group-item border-0 px-2 py-1">Isi semua data yang diperlukan pada form peminjaman.</li>
                    <li class="list-group-item border-0 px-2 py-1">Upload berkas Surat Peminjaman Ruang jika diperlukan.</li>
                    <li class="list-group-item border-0 px-2 py-1">Klik tombol "Save" atau "Simpan".</li>
                    <li class="list-group-item border-0 px-2 py-1">Kegiatan berhasil dibuat dan akan muncul di menu "Kegiatan" untuk diproses.</li>
                    <li class="list-group-item border-0 px-2 py-1">Harap tunggu proses verifikasi dari pihak terkait.</li>
                    <li class="list-group-item border-0 px-2 py-1">Ketika kegiatan telah disetujui, Anda dapat menggunakan ruangan sesuai jadwal.</li>
                </ol>
            </div>
        </div>
    @endcannot

</div>

{{-- SCRIPT: COPY CLIPBOARD --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    function buttonFeedback(button) {
        const originalText = button.innerHTML;
        const originalClass = button.className;
        button.innerHTML = '<i class="fas fa-check me-2"></i> Disalin!';
        button.className = 'btn btn-sm btn-success';
        setTimeout(() => {
            button.innerHTML = originalText;
            button.className = originalClass;
        }, 2000);
    }

    function fallbackCopyTextToClipboard(text, button) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = 0; textArea.style.left = 0;
        textArea.style.width = "2em"; textArea.style.height = "2em";
        textArea.style.padding = 0; textArea.style.border = "none";
        textArea.style.outline = "none"; textArea.style.boxShadow = "none";
        textArea.style.background = "transparent";
        document.body.appendChild(textArea);
        textArea.focus(); textArea.select();
        try { document.execCommand('copy'); buttonFeedback(button); } catch (err) { alert('Gagal menyalin.'); }
        document.body.removeChild(textArea);
    }

    function copyHandler(buttonId, textareaId) {
        const button = document.getElementById(buttonId);
        const textarea = document.getElementById(textareaId);
        if (!button || !textarea) return;
        button.addEventListener('click', function() {
            const text = textarea.value || '';
            if (!text.trim()) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => buttonFeedback(button)).catch(() => fallbackCopyTextToClipboard(text, button));
            } else {
                fallbackCopyTextToClipboard(text, button);
            }
        });
    }

    copyHandler('copyHariIniBtn', 'jadwalHariIniClipboard');
    copyHandler('copyBesokBtn', 'jadwalBesokClipboard');
});
</script>
@endsection