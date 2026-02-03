@extends('layouts.admin')

@section('styles')
<style>
    /* --- STAT CARD STYLE --- */
    .dashboard-stat-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,.08);
        padding: 1.5rem;
        height: 100%;
        transition: all .2s ease-in-out;
        border: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }
    .dashboard-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 40px rgba(0,0,0,.12);
    }
    .stat-label { color: #858796; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
    .stat-value { color: #5a5c69; font-size: 1.75rem; font-weight: 700; line-height: 1.2; }
    
    .icon-container {
        width: 3.5rem; height: 3.5rem; border-radius: 999px; display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.15); flex-shrink: 0;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
    .bg-gradient-danger  { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); }
    
    /* --- TABLE PANEL --- */
    .dashboard-panel-card { background: #fff; border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,.08); border: none; overflow: hidden; margin-bottom: 2rem; }
    .dashboard-panel-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,.06); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    
    .table thead th { background-color: #f8f9fc; color: #4e73df; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; border-top: none; border-bottom: 1px solid #e3e6f0; padding: 1rem 1.5rem; }
    .table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
    .avatar-circle { width: 40px; height: 40px; background-color: #4e73df; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1rem; margin-right: 1rem; }

    /* New Badge Styles */
    .badge-soft-danger { background-color: #fce8e6; color: #e74a3b; border: 1px solid #fadbd8; }
    .badge-soft-warning { background-color: #fff8e1; color: #f6c23e; border: 1px solid #fce8b2; }
    .last-sync-badge { font-size: 0.75rem; background: #e3e6f0; color: #5a5c69; padding: 5px 12px; border-radius: 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .last-sync-badge.active { background: #d1fae5; color: #065f46; } /* Hijau kalau baru update */
</style>
@endsection

@section('content')
<div class="content">

    {{-- HEADER & FILTER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="h3 mb-1 text-gray-800 fw-bold">Dashboard Presensi</h3>
            
            {{-- LAST SYNC INDICATOR --}}
            @if($lastSync)
                <div class="last-sync-badge {{ \Carbon\Carbon::parse($lastSync)->diffInMinutes(now()) < 60 ? 'active' : '' }}" 
                     title="Terakhir diambil dari Info Absen Unair">
                    <i class="fas fa-history"></i> 
                    Update: {{ \Carbon\Carbon::parse($lastSync)->format('H:i') }} WIB 
                    <small class="ms-1 opacity-75">({{ \Carbon\Carbon::parse($lastSync)->diffForHumans() }})</small>
                </div>
            @else
                <div class="last-sync-badge">
                    <i class="fas fa-exclamation-circle"></i> Belum ada data hari ini
                </div>
            @endif
        </div>
        
        <form action="{{ route('admin.absensi.index') }}" method="GET">
            <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-calendar-alt text-primary"></i></span>
                <input type="date" name="tanggal" class="form-control border-0" value="{{ $tanggal }}" style="outline: none; box-shadow: none;">
                <button type="submit" class="btn btn-primary px-4 fw-bold">Filter</button>
            </div>
        </form>
    </div>

    {{-- STAT CARDS ROW --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-stat-card">
                <div><div class="stat-label">Total Pegawai</div><div class="stat-value">{{ $stats['total_pegawai'] }}</div></div>
                <div class="icon-container bg-gradient-primary"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-stat-card">
                <div><div class="stat-label text-success">Tepat Waktu</div><div class="stat-value">{{ $stats['hadir'] }}</div></div>
                <div class="icon-container bg-gradient-success"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-stat-card">
                <div><div class="stat-label text-warning">Terlambat</div><div class="stat-value">{{ $stats['terlambat'] }}</div></div>
                <div class="icon-container bg-gradient-warning"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-stat-card">
                <div><div class="stat-label text-danger">Belum / Alpha</div><div class="stat-value">{{ $stats['alpha'] }}</div></div>
                <div class="icon-container bg-gradient-danger"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
    </div>

    {{-- ALERT PULANG AWAL (Muncul hanya jika ada yang pulang awal) --}}
    @if($stats['pulang_awal'] > 0)
    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; background-color: #fff5f5; border-left: 5px solid #e74a3b !important;">
        <div class="me-3 d-flex align-items-center justify-content-center bg-danger text-white rounded-circle" style="width: 40px; height: 40px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h6 class="alert-heading fw-bold mb-0 text-danger">Perhatian!</h6>
            <p class="mb-0 text-dark small">
                Terdapat <b>{{ $stats['pulang_awal'] }} Pegawai</b> yang terdeteksi pulang sebelum jam {{ $batasPulang }}. Cek tabel di bawah untuk detail.
            </p>
        </div>
    </div>
    @endif

    {{-- TABEL DATA --}}
    <div class="dashboard-panel-card">
        <div class="dashboard-panel-header">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-list-ul me-2"></i> Detail Kehadiran 
                <span class="text-muted fw-normal ms-1">— {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>
            </h6>
            <div class="d-flex gap-2">
                {{-- TOMBOL SINKRONISASI MANUAL --}}
                <form action="{{ route('admin.absensi.sync') }}" method="POST" onsubmit="return showLoading(this)">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3 shadow-sm btn-sync">
                        <i class="fas fa-sync me-1"></i> Sinkronisasi Live
                    </button>
                </form>

                {{-- Tombol Refresh Halaman (GET) --}}
                <a href="{{ request()->fullUrl() }}" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">
                    <i class="fas fa-redo-alt me-1"></i> Refresh View
                </a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Pegawai</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">Pulang</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        {{-- Logika Cek Pulang Awal Per Baris --}}
                        @php
                            $isPulangAwal = !empty($log->jam_keluar) && $log->jam_keluar != '-' && $log->jam_keluar < $batasPulang;
                        @endphp
                        
                        <tr class="{{ $isPulangAwal ? 'bg-light' : '' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle shadow-sm">
                                        {{ strtoupper(substr($log->user->name ?? 'X', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                            {{ $log->user->name ?? 'User Unknown' }}
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.8rem;">
                                            NIP.{{ $log->user->nip ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($log->jam_masuk)
                                    <span class="fw-bold {{ $log->status == 'terlambat' ? 'text-danger' : 'text-dark' }}">
                                        {{ $log->jam_masuk }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->jam_keluar && $log->jam_keluar != '-')
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="fw-bold {{ $isPulangAwal ? 'text-danger' : 'text-dark' }}">
                                            {{ $log->jam_keluar }}
                                        </span>
                                        @if($isPulangAwal)
                                            <span class="badge badge-soft-danger rounded-pill" style="font-size: 0.65rem;">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Pulang Lebih Awal
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- BADGE STATUS UTAMA --}}
                                @php
                                    $badgeClass = match($log->status) {
                                        'hadir'     => 'bg-success',
                                        'terlambat' => 'bg-warning text-dark',
                                        'alpha'     => 'bg-danger',
                                        default     => 'bg-secondary'
                                    };
                                    $statusText = match($log->status) {
                                        'hadir'     => 'Tepat Waktu',
                                        'terlambat' => 'Terlambat',
                                        'alpha'     => 'Belum Scan',
                                        default     => ucfirst($log->status)
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2 shadow-sm">
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted opacity-75">
                                    <i class="fas fa-folder-open fa-2x mb-3"></i>
                                    <h6 class="fw-bold">Tidak ada data presensi</h6>
                                    <p class="small mb-0">Bot belum berjalan atau hari libur.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->isNotEmpty())
        <div class="card-footer bg-white border-0 py-3 text-center">
            <small class="text-muted">Menampilkan {{ $logs->count() }} data presensi</small>
        </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
@parent
<script>
    function showLoading(form) {
        let btn = form.querySelector('.btn-sync');
        let icon = btn.querySelector('i');
        
        // Ubah tampilan tombol saat loading
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengambil Data...';
        
        // Submit form manual karena tombol disabled tidak submit otomatis
        form.submit(); 
        return true;
    }
</script>
@endsection