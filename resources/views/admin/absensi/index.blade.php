@extends('layouts.admin')

@section('styles')
<style>
    /* Style tetap sama seperti sebelumnya, tambah dikit untuk leaderboard */
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
    
    .avatar-circle { width: 40px; height: 40px; background-color: #4e73df; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    .dashboard-panel-card { background: #fff; border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,.08); border: none; overflow: hidden; margin-bottom: 2rem; }
    .dashboard-panel-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,.06); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    
    .table thead th { background-color: #f8f9fc; color: #4e73df; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; border-top: none; border-bottom: 1px solid #e3e6f0; padding: 1rem 1.5rem; }
    .table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
    .avatar-circle { width: 40px; height: 40px; background-color: #4e73df; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1rem; margin-right: 1rem; }

    /* New Badge Styles */
    .badge-soft-danger { background-color: #fce8e6; color: #e74a3b; border: 1px solid #fadbd8; }
    .badge-soft-warning { background-color: #fff8e1; color: #f6c23e; border: 1px solid #fce8b2; }
    .last-sync-badge { font-size: 0.75rem; background: #e3e6f0; color: #5a5c69; padding: 5px 12px; border-radius: 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .last-sync-badge.active { background: #d1fae5; color: #065f46; } 
    /* Leaderboard Style */
    .leaderboard-card { background: #fff; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.08); overflow: hidden; }
    .leaderboard-header { background: #f8f9fc; padding: 15px 20px; border-bottom: 1px solid #e3e6f0; font-weight: bold; color: #4e73df; }
    .leaderboard-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #f1f1f1; }
    .leaderboard-item:last-child { border-bottom: none; }
    .rank-badge { width: 25px; height: 25px; background: #e3e6f0; color: #5a5c69; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; margin-right: 10px; }
    .rank-1 { background: #f6c23e; color: #fff; } 
    .rank-2 { background: #e6e6e6; color: #5a5c69; } 
    .rank-3 { background: #d78e48; color: #fff; } 
    .bg-primary { background: #741847 !important}
    
    /* Sembunyikan scrollbar di switcher mobile tapi tetap bisa di-scroll */
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection

@section('content')
<div class="content">

    {{-- HEADER & FILTER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-nowrap"><i class="fas fa-fingerprint me-2"></i> Dashboard Presensi</h3>
            @if($lastSync)
                <div class="badge bg-light text-dark border p-2">
                    <i class="fas fa-history text-primary"></i> Last Update: {{ \Carbon\Carbon::parse($lastSync)->format('H:i') }} WIB
                </div>
            @endif
        </div>
        
        {{-- FIX: d-flex flex-wrap agar tombol turun ke bawah di layar kecil --}}
        <div class="d-flex flex-wrap gap-2 w-100 mt-2 mt-md-0 justify-content-md-end" style="max-width: 100%;">
            {{-- TOMBOL AKSES KE PENGATURAN BOT --}}
            <a href="{{ route('admin.bot-setting.index') }}" class="btn btn-outline-info fw-bold shadow-sm d-flex align-items-center flex-grow-1 flex-md-grow-0 justify-content-center" style="border-radius: 10px;">
                <i class="fas fa-robot me-2"></i> Atur Bot
            </a>

            {{-- TOMBOL AKSES KE CRUD HARI LIBUR --}}
            <a href="{{ route('admin.hari-libur.index') }}" class="btn btn-outline-danger fw-bold shadow-sm d-flex align-items-center flex-grow-1 flex-md-grow-0 justify-content-center" style="border-radius: 10px;">
                <i class="fas fa-calendar-times me-2"></i> Atur Libur
            </a>

            {{-- TOMBOL AKSES KE CRUD PERIODE JAM KERJA --}}
            <a href="{{ route('admin.periode-jam-kerja.index') }}" class="btn btn-outline-success fw-bold shadow-sm d-flex align-items-center flex-grow-1 flex-md-grow-0 justify-content-center" style="border-radius: 10px;">
                <i class="fas fa-clock me-2"></i> Atur Jadwal
            </a>
            <a href="{{ route('admin.jadwal-wfh.index') }}" class="btn btn-outline-success fw-bold shadow-sm d-flex align-items-center flex-grow-1 flex-md-grow-0 justify-content-center" style="border-radius: 10px;">
                <i class="fas fa-user-clock me-2"></i> Atur WFH
            </a>

            <a href="{{ route('admin.lembur-kegiatan.index') }}" 
            class="btn btn-outline-warning fw-bold shadow-sm d-flex align-items-center flex-grow-1 flex-md-grow-0 justify-content-center" 
            style="border-radius: 10px;">
                <i class="fas fa-business-time me-2"></i> Atur Lembur
            </a>

            {{-- FORM FILTER TANGGAL --}}
            <form action="{{ route('admin.absensi.index') }}" method="GET" class="w-100 flex-md-grow-0 mt-2 mt-md-0" style="max-width: 300px;">
                <div class="input-group shadow-sm w-100" style="border-radius: 10px; overflow: hidden;">
                    <input type="date" name="tanggal" class="form-control border-0" value="{{ $tanggal }}">
                    <button type="submit" class="btn btn-primary fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SWITCHER TENDIK / DOSEN --}}
    <div class="mb-4">
        {{-- FIX: flex-nowrap dan overflow-auto agar bisa di-swipe di layar kecil --}}
        <ul class="nav nav-pills shadow-sm p-1 bg-white rounded-pill d-inline-flex flex-nowrap overflow-auto hide-scrollbar" style="max-width: 100%; white-space: nowrap;">
            <li class="nav-item">
                <a class="nav-link rounded-pill fw-bold {{ $roleFilter == 'Pegawai' ? 'active bg-primary text-white' : 'text-dark' }} px-4" 
                   href="{{ request()->fullUrlWithQuery(['role' => 'Pegawai']) }}">
                   <i class="fas fa-user-tie me-2"></i>Tendik
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill fw-bold {{ $roleFilter == 'Dosen' ? 'active bg-primary text-white' : 'text-dark' }} px-4" 
                   href="{{ request()->fullUrlWithQuery(['role' => 'Dosen']) }}">
                   <i class="fas fa-chalkboard-teacher me-2"></i>Dosen
                </a>
            </li>
        </ul>
    </div>

    {{-- STAT CARDS --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-6 mb-3 mb-xl-0">
            <div class="dashboard-stat-card p-3 p-md-4">
                <div><div class="small fw-bold text-uppercase text-muted" style="font-size: 0.65rem;">Total Pegawai</div><div class="h4 h3-md fw-bold mb-0">{{ $stats['total_pegawai'] }}</div></div>
                <div class="icon-container bg-gradient-primary" style="width: 2.5rem; height: 2.5rem; font-size: 1.2rem;"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-6 mb-3 mb-xl-0">
            <div class="dashboard-stat-card p-3 p-md-4">
                <div><div class="small fw-bold text-uppercase text-success" style="font-size: 0.65rem;">Hadir</div><div class="h4 h3-md fw-bold mb-0">{{ $stats['hadir'] }}</div></div>
                <div class="icon-container bg-gradient-success" style="width: 2.5rem; height: 2.5rem; font-size: 1.2rem;"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-6 mb-3 mb-xl-0">
            <div class="dashboard-stat-card p-3 p-md-4">
                <div><div class="small fw-bold text-uppercase text-warning" style="font-size: 0.65rem;">Terlambat</div><div class="h4 h3-md fw-bold mb-0">{{ $stats['terlambat'] }}</div></div>
                <div class="icon-container bg-gradient-warning" style="width: 2.5rem; height: 2.5rem; font-size: 1.2rem;"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-6 mb-3 mb-xl-0">
            <div class="dashboard-stat-card p-3 p-md-4">
                <div><div class="small fw-bold text-uppercase text-danger" style="font-size: 0.65rem;">Alpha</div><div class="h4 h3-md fw-bold mb-0">{{ $stats['alpha'] }}</div></div>
                <div class="icon-container bg-gradient-danger" style="width: 2.5rem; height: 2.5rem; font-size: 1.2rem;"><i class="fas fa-times"></i></div>
            </div>
        </div>
    </div>

    @if($stats['pulang_awal'] > 0)
    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; background-color: #fff5f5; border-left: 5px solid #e74a3b !important;">
        <div class="me-3 d-flex align-items-center justify-content-center bg-danger text-white rounded-circle flex-shrink-0" style="width: 40px; height: 40px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h6 class="alert-heading fw-bold mb-0 text-danger">Perhatian!</h6>
            <p class="mb-0 text-dark small">
                Terdapat <b>{{ $stats['pulang_awal'] }} Pegawai</b> yang terdeteksi pulang sebelum jam batas jadwalnya. Cek tabel di bawah untuk detail.
            </p>
        </div>
    </div>
    @endif

    <div class="row">
        {{-- KOLOM KIRI: TABEL ABSENSI --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-lg">
                {{-- FIX: flex-column flex-md-row untuk Card Header --}}
                <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 gap-md-0">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-list me-2"></i>Log Harian ({{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }})
                        
                        @if(isset($isLibur) && $isLibur)
                            <span class="badge bg-danger text-white ms-0 ms-md-2 mt-2 mt-md-0 d-inline-block" style="font-size: 0.8rem;">
                                <i class="fas fa-calendar-times me-1"></i> {{ $keteranganLibur ?? 'Hari Libur' }}
                            </span>
                        @endif
                    </h6>
                    
                    {{-- TOMBOL SINKRONISASI MANUAL --}}
                    <form action="{{ route('admin.absensi.sync') }}" method="POST" onsubmit="return showLoading(this)" class="flex-shrink-0">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        
                        <button type="submit" class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3 shadow-sm btn-sync w-100 w-md-auto">
                            <i class="fas fa-sync me-1"></i> Sync Tgl {{ \Carbon\Carbon::parse($tanggal)->format('d/m') }}
                        </button>
                    </form>
                </div>
                <div class="table-responsive">
                    
                    @if(isset($isLibur) && $isLibur && $pegawais->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-calendar-times fa-4x text-gray-300"></i>
                            </div>
                            <h5 class="fw-bold text-gray-600">{{ $keteranganLibur ?? 'Akhir Pekan' }}</h5>
                            <p class="text-muted small mb-0">Tidak ada pegawai yang melakukan presensi (Lembur) pada tanggal ini.</p>
                        </div>
                    @else
                        <table class="table align-middle mb-0">
                            {{-- FIX: text-nowrap untuk header tabel agar tidak patah --}}
                            <thead class="bg-light text-nowrap">
                                <tr>
                                    <th class="ps-4">Pegawai</th>
                                    
                                    @if($roleFilter === 'Dosen')
                                        <th class="text-center">Waktu Scan</th>
                                    @else
                                        <th class="text-center">Masuk</th>
                                        <th class="text-center">Pulang</th>
                                        <th class="text-center">Durasi</th>
                                    @endif
                                    
                                    <th class="text-center">Status Presensi</th>
                                    <th class="text-center">Status Bot</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pegawais as $pegawai)
                                    @php
                                        $log = $pegawai->absensiLogs->first();
                                        $jamMasuk  = $log->jam_masuk ?? '-';
                                        $jamKeluar = $log->jam_keluar ?? '-';
                                        $status    = $log->status ?? 'alpha'; 
                                        
                                        $batasPulangDb = $log->batas_jam_keluar ?? (\Carbon\Carbon::parse($tanggal)->isFriday() ? '17:00' : '16:30');
                                        $isPulangAwal = (!$isLibur && $roleFilter === 'Pegawai' && $jamKeluar !== '-' && $jamKeluar < $batasPulangDb);

                                        $durasiKerja = '-';
                                        if ($jamMasuk !== '-' && $jamKeluar !== '-') {
                                            try {
                                                $start = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk);
                                                $end   = \Carbon\Carbon::createFromFormat('H:i', $jamKeluar);
                                                if ($end->greaterThan($start)) {
                                                    $diff = $start->diff($end);
                                                    $durasiKerja = $diff->format('%Hj %Im'); 
                                                }
                                            } catch(\Exception $e) { $durasiKerja = 'err'; }
                                        }

                                        $durasiTelat = '';
                                        if ($status == 'terlambat' && $jamMasuk !== '-' && $roleFilter === 'Pegawai') {
                                            try {
                                                $batasMasuk = $log->batas_jam_masuk ?? '08:00'; 
                                                $batasWaktu = \Carbon\Carbon::parse($batasMasuk);
                                                $waktuMasuk = \Carbon\Carbon::parse($jamMasuk);
                                                
                                                if ($waktuMasuk->greaterThan($batasWaktu)) {
                                                    $diff = $batasWaktu->diff($waktuMasuk);
                                                    $menit = ($diff->h * 60) + $diff->i;
                                                    $durasiTelat = "($menit menit)";
                                                }
                                            } catch(\Exception $e) { }
                                        }

                                        $hasTelegram = !empty($pegawai->telegram_chat_id);
                                        $notifHistory = $log->notif_history ?? [];
                                    @endphp

                                    <tr class="{{ $isPulangAwal ? 'bg-soft-danger' : '' }}">
                                        <td class="ps-4" style="min-width: 200px;">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3 text-uppercase flex-shrink-0">
                                                    {{ substr($pegawai->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $pegawai->name }}</div>
                                                    <div class="small text-muted">{{ $pegawai->nip }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        @if($roleFilter === 'Dosen')
                                            @php
                                                $statusKeaktifan = $pegawai->dosenDetail?->status_keaktifan ?? 'Aktif';
                                                $waktuScanDosen = '-';
                                                if ($jamMasuk !== '-') {
                                                    $waktuScanDosen = $jamMasuk;
                                                } elseif ($jamKeluar !== '-') {
                                                    $waktuScanDosen = $jamKeluar;
                                                }
                                            @endphp

                                            <td class="text-center fw-bold text-dark text-nowrap">
                                                {{ $statusKeaktifan === 'Aktif' ? $waktuScanDosen : '-' }}
                                            </td>
                                            <td class="text-center text-nowrap">
                                                @if($statusKeaktifan !== 'Aktif')
                                                    <span class="badge bg-info rounded-pill px-3">{{ $statusKeaktifan }}</span>
                                                @else
                                                    @if($waktuScanDosen !== '-')
                                                        <span class="badge bg-success rounded-pill">Sudah Absen</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">Belum Absen</span>
                                                    @endif
                                                @endif
                                            </td>
                                            
                                        @else
                                            <td class="text-center fw-bold text-nowrap {{ $status == 'terlambat' ? 'text-danger' : 'text-dark' }}">
                                                <div>{{ $jamMasuk }}</div>
                                                @if($durasiTelat)
                                                    <div class="small text-danger mt-1" style="font-size: 0.75rem;">{{ $durasiTelat }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-nowrap {{ $isPulangAwal ? 'text-danger' : 'text-dark' }}">
                                                {{ $jamKeluar }}
                                                @if($isPulangAwal)
                                                    <i class="fas fa-exclamation-circle text-danger ms-1" title="Pulang Awal"></i>
                                                @endif
                                            </td>
                                            <td class="text-center text-primary fw-bold text-nowrap">
                                                {{ $durasiKerja }}
                                            </td>
                                            <td class="text-center text-nowrap">
                                            @php
                                                $modeKerjaBadge = '';
                                                $modeKerjaStr = strtolower($log->mode_kerja ?? '');
                                                
                                                // Tentukan warna badge tambahan untuk mode kerja
                                                if (str_contains($modeKerjaStr, 'wfh') || str_contains($modeKerjaStr, 'rumah')) {
                                                    $modeKerjaBadge = '<span class="badge border border-info text-info ms-1">WFH</span>';
                                                } elseif (str_contains($modeKerjaStr, 'dinas luar')) {
                                                    $modeKerjaBadge = '<span class="badge border border-primary text-primary ms-1">Dinas Luar</span>';
                                                } elseif (str_contains($modeKerjaStr, 'wfo') || str_contains($modeKerjaStr, 'kantor')) {
                                                    $modeKerjaBadge = '<span class="badge border border-success text-success ms-1">WFO</span>';
                                                }
                                            @endphp

                                            @if(str_contains($modeKerjaStr, 'dinas luar'))
                                                {{-- Handle khusus Dinas Luar yang scan masuk/keluarnya "-" --}}
                                                <span class="badge bg-primary rounded-pill"><i class="fas fa-briefcase me-1"></i> Dinas Luar</span>
                                            @elseif($status == 'hadir')
                                                @if(isset($isLibur) && $isLibur)
                                                    <span class="badge bg-info text-white rounded-pill">Lembur Valid</span> {!! $modeKerjaBadge !!}
                                                @else
                                                    <span class="badge bg-success rounded-pill">Tepat Waktu</span> {!! $modeKerjaBadge !!}
                                                @endif
                                            @elseif($status == 'terlambat')
                                                <span class="badge bg-warning text-dark rounded-pill">Terlambat</span> {!! $modeKerjaBadge !!}
                                            @elseif(in_array($status, ['cuti', 'tugas belajar']))
                                                <span class="badge bg-secondary text-white rounded-pill px-3">
                                                    <i class="fas fa-plane-departure me-1"></i> {{ ucwords($status) }}
                                                </span>
                                            @else
                                                @if(isset($isLibur) && $isLibur && ($jamMasuk !== '-' || $jamKeluar !== '-'))
                                                    @if($jamMasuk !== '-' && $jamKeluar === '-')
                                                        <span class="badge bg-warning text-dark rounded-pill">Sedang Lembur</span>
                                                    @else
                                                        <span class="badge bg-danger rounded-pill" title="Durasi kurang dari 4 jam">Tidak Valid (< 4 Jam)</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary rounded-pill">Belum Scan</span>
                                                @endif
                                            @endif

                                            @if($isPulangAwal && !str_contains($modeKerjaStr, 'dinas luar'))
                                                <span class="badge bg-danger rounded-pill ms-1">Pulang Awal</span>
                                            @endif
                                        </td>
                                        @endif
                                        
                                        {{-- FIX: flex-wrap dan min-width agar badge notifikasi bisa turun ke bawah saat mentok --}}
                                        <td class="text-center">
                                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-1" style="min-width: 140px;">
                                                @if($hasTelegram)
                                                    <span class="badge rounded-pill d-inline-flex align-items-center justify-content-center" data-bs-toggle="tooltip" title="Telegram Terhubung" style="background-color: #0088cc; width: 28px; height: 28px;">
                                                        <i class="fab fa-telegram-plane text-white" style="font-size: 14px;"></i>
                                                    </span>
                                                @else
                                                    <span class="badge rounded-pill bg-light text-secondary border" data-bs-toggle="tooltip" title="Belum ada ID Telegram">
                                                        <i class="fas fa-plug-circle-xmark"></i> No ID
                                                    </span>
                                                @endif

                                                @if($log)
                                                    @if(isset($notifHistory['email_telat_2x_sent']))
                                                        <span class="badge rounded-pill bg-success" title="Email SP sukses terkirim ke SMTP jam {{ $notifHistory['email_telat_2x_sent'] }}">
                                                            <i class="fas fa-envelope-open-text"></i> Email SP (Sent)
                                                        </span>
                                                    @elseif(isset($notifHistory['email_telat_2x_queued']))
                                                        <span class="badge rounded-pill bg-secondary text-white" title="Email SP sedang diantrekan (Queued) sejak jam {{ $notifHistory['email_telat_2x_queued'] }}">
                                                            <i class="fas fa-envelope text-warning"></i> Email SP (Queued)
                                                        </span>
                                                    @endif
                                                @endif

                                                @if($hasTelegram && $log)
                                                    @if(isset($notifHistory['telat_masuk']))
                                                        <span class="badge rounded-pill bg-danger" title="Diingatkan Telat Masuk jam {{ $notifHistory['telat_masuk'] }}">
                                                            <i class="fas fa-bell"></i> Pagi
                                                        </span>
                                                    @endif

                                                    @if(isset($notifHistory['pulang_awal']))
                                                        <span class="badge rounded-pill bg-warning text-dark" title="Diingatkan Pulang Awal jam {{ $notifHistory['pulang_awal'] }}">
                                                            <i class="fas fa-bell"></i> Pulang
                                                        </span>
                                                    @endif

                                                    @if(isset($notifHistory['belum_pulang']))
                                                        <span class="badge rounded-pill bg-info text-white" title="Diingatkan Scan Pulang jam {{ $notifHistory['belum_pulang'] }}">
                                                            <i class="fas fa-bell"></i> Pulang
                                                        </span>
                                                    @endif

                                                    @if(isset($notifHistory['telat_2x']))
                                                        <span class="badge rounded-pill bg-dark border border-warning text-warning" title="Peringatan Telat 2x jam {{ $notifHistory['telat_2x'] }}">
                                                            <i class="fas fa-bell"></i> Peringatan Telat 2x
                                                        </span>
                                                    @endif
                                                    
                                                    @if(isset($notifHistory['siang_dosen_belum']))
                                                        <span class="badge rounded-pill bg-danger" title="Peringatan Belum Absen Siang jam {{ $notifHistory['siang_dosen_belum'] }}">
                                                            <i class="fas fa-exclamation-circle"></i> Siang (Belum)
                                                        </span>
                                                    @endif

                                                    @if(isset($notifHistory['siang_dosen_sudah']))
                                                        <span class="badge rounded-pill bg-success" title="Info Sudah Absen Siang dikirim jam {{ $notifHistory['siang_dosen_sudah'] }}">
                                                            <i class="fas fa-check-circle"></i> Siang (OK)
                                                        </span>
                                                    @endif
                                                    
                                                    @if(empty($notifHistory))
                                                        <small class="text-muted ms-1" style="font-size: 0.7rem;">Standby</small>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="100%" class="text-center py-4">Tidak ada data pegawai.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: LEADERBOARD TELAT & LEMBUR --}}
        <div class="col-lg-4">
            {{-- 1. CARD LEADERBOARD TELAT --}}
            <div class="leaderboard-card mb-4">
                @php
                    $isDosen = $roleFilter === 'Dosen';
                    $titleRank = $isDosen ? 'Top Belum Absen' : 'Top Terlambat';
                    $labelRank = $isDosen ? 'Tidak Absen' : 'Terlambat';
                    $colorClass = $isDosen ? 'text-secondary' : 'text-danger';
                    $emptyMsg = $isDosen ? 'Semua dosen rajin absen bulan ini. Mantap!' : 'Belum ada yang terlambat bulan ini. Mantap!';
                @endphp

                <div class="leaderboard-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-trophy me-2"></i>{{ $titleRank }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-primary border">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('M Y') }}</span>
                        <a href="{{ route('admin.absensi.rekap-telat', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m')]) }}" class="btn btn-sm btn-primary py-0 px-2 shadow-sm" style="font-size: 0.75rem; border-radius: 6px;">
                            Semua <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                
                @if($topStats->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p class="mb-0 small">{{ $emptyMsg }}</p>
                    </div>
                @else
                    @foreach($topStats as $index => $stat)
                        @php
                            $dates = explode(',', $stat->tanggal_kasus);
                            $formattedDates = array_map(function($day) { return str_pad($day, 2, '0', STR_PAD_LEFT); }, $dates);
                            $tanggalList = implode(',', $formattedDates);
                        @endphp
                        <div class="leaderboard-item">
                            <div class="d-flex align-items-center">
                                <div class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $stat->user->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">{{ $stat->user->nip ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold {{ $colorClass }} h5 mb-0">{{ $stat->total_kasus }}x</div>
                                <div class="small text-muted" style="font-size: 0.7rem;">{{ $labelRank }}</div>
                                <div class="small text-muted" style="font-size: 0.65rem;">({{ $tanggalList }})</div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- 2. CARD LEADERBOARD LEMBUR --}}
            <div class="leaderboard-card mb-4" style="border-top: 4px solid #1cc88a;">
                <div class="leaderboard-header d-flex justify-content-between align-items-center" style="background: #f4fdf8; color: #13855c; border-bottom: 1px solid #e3e6f0;">
                    <span><i class="fas fa-business-time me-2"></i>Rekap Lembur</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-success border">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('M Y') }}</span>
                        <a href="{{ route('admin.absensi.rekap-lembur', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m')]) }}" 
                        class="btn btn-sm btn-success py-0 px-2 shadow-sm" style="font-size: 0.75rem; border-radius: 6px;">
                            Rekap <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                        <a href="{{ route('admin.lembur-kegiatan.index', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m')]) }}" 
                        class="btn btn-sm btn-outline-success py-0 px-2 shadow-sm" style="font-size: 0.75rem; border-radius: 6px;">
                            <i class="fas fa-tasks"></i> Kegiatan
                        </a>
                    </div>
                </div>

                @if($topLembur->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-mug-hot fa-2x mb-2 text-success" style="opacity: 0.5;"></i>
                        <p class="mb-0 small">Belum ada data lembur valid bulan ini.</p>
                    </div>
                @else
                    @foreach($topLembur as $index => $stat)
                        @php
                            $dates = explode(',', $stat->tanggal_lembur);
                            $formattedDates = array_map(function($day) { return str_pad($day, 2, '0', STR_PAD_LEFT); }, $dates);
                            $tanggalList = implode(',', $formattedDates);
                        @endphp
                        <div class="leaderboard-item">
                            <div class="d-flex align-items-center">
                                <div class="rank-badge bg-success text-white">{{ $index + 1 }}</div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $stat->user->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">{{ $stat->user->nip ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success h5 mb-0">{{ $stat->total_lembur }}x</div>
                                <div class="small text-muted" style="font-size: 0.7rem;">Valid (≥ 4 Jam)</div>
                                <div class="small text-muted" style="font-size: 0.65rem;">({{ $tanggalList }})</div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    function showLoading(form) {
        let btn = form.querySelector('.btn-sync');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sync...';
        form.submit(); 
        return true;
    }
</script>
@endsection
