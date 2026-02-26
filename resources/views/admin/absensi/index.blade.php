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
</style>
@endsection

@section('content')
<div class="content">

    {{-- HEADER & FILTER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="h3 mb-1 text-gray-800 fw-bold">Dashboard Presensi</h3>
            @if($lastSync)
                <div class="badge bg-light text-dark border p-2">
                    <i class="fas fa-history text-primary"></i> Last Update: {{ \Carbon\Carbon::parse($lastSync)->format('H:i') }} WIB
                </div>
            @endif
        </div>
        
        <div class="d-flex gap-2">
            {{-- TOMBOL AKSES KE PENGATURAN BOT --}}
            <a href="{{ route('admin.bot-setting.index') }}" class="btn btn-outline-info fw-bold shadow-sm d-flex align-items-center" style="border-radius: 10px;">
                <i class="fas fa-robot me-2"></i> Atur Bot
            </a>

            {{-- TOMBOL AKSES KE CRUD HARI LIBUR --}}
            <a href="{{ route('admin.hari-libur.index') }}" class="btn btn-outline-danger fw-bold shadow-sm d-flex align-items-center" style="border-radius: 10px;">
                <i class="fas fa-calendar-times me-2"></i> Atur Libur
            </a>

            {{-- TOMBOL AKSES KE CRUD PERIODE JAM KERJA --}}
            <a href="{{ route('admin.periode-jam-kerja.index') }}" class="btn btn-outline-success fw-bold shadow-sm d-flex align-items-center" style="border-radius: 10px;">
                <i class="fas fa-clock me-2"></i> Atur Jadwal
            </a>

            {{-- FORM FILTER TANGGAL --}}
            <form action="{{ route('admin.absensi.index') }}" method="GET">
                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                    <input type="date" name="tanggal" class="form-control border-0" value="{{ $tanggal }}">
                    <button type="submit" class="btn btn-primary fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="dashboard-stat-card">
                <div><div class="small fw-bold text-uppercase text-muted">Total Pegawai</div><div class="h3 fw-bold mb-0">{{ $stats['total_pegawai'] }}</div></div>
                <div class="icon-container bg-gradient-primary"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="dashboard-stat-card">
                <div><div class="small fw-bold text-uppercase text-success">Hadir</div><div class="h3 fw-bold mb-0">{{ $stats['hadir'] }}</div></div>
                <div class="icon-container bg-gradient-success"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="dashboard-stat-card">
                <div><div class="small fw-bold text-uppercase text-warning">Terlambat</div><div class="h3 fw-bold mb-0">{{ $stats['terlambat'] }}</div></div>
                <div class="icon-container bg-gradient-warning"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="dashboard-stat-card">
                <div><div class="small fw-bold text-uppercase text-danger">Belum Presensi</div><div class="h3 fw-bold mb-0">{{ $stats['alpha'] }}</div></div>
                <div class="icon-container bg-gradient-danger"><i class="fas fa-times"></i></div>
            </div>
        </div>
    </div>

    @if($stats['pulang_awal'] > 0)
    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; background-color: #fff5f5; border-left: 5px solid #e74a3b !important;">
        <div class="me-3 d-flex align-items-center justify-content-center bg-danger text-white rounded-circle" style="width: 40px; height: 40px;">
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
        {{-- KOLOM KIRI: TABEL ABSENSI (Lebih Lebar) --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-list me-2"></i>Log Harian ({{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }})
                        
                        {{-- BADGE STATUS HARI LIBUR --}}
                        @if(isset($isLibur) && $isLibur)
                            <span class="badge bg-danger text-white ms-2" style="font-size: 0.8rem;">
                                <i class="fas fa-calendar-times me-1"></i> {{ $keteranganLibur ?? 'Hari Libur' }}
                            </span>
                        @endif
                    </h6>
                    
                    {{-- TOMBOL SINKRONISASI MANUAL --}}
                    <form action="{{ route('admin.absensi.sync') }}" method="POST" onsubmit="return showLoading(this)">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        
                        <button type="submit" class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3 shadow-sm btn-sync">
                            <i class="fas fa-sync me-1"></i> Sinkronisasi Tgl {{ \Carbon\Carbon::parse($tanggal)->format('d/m') }}
                        </button>
                    </form>
                </div>
                <div class="table-responsive">
                    
                    {{-- LOGIC TAMPILAN KOSONG SAAT HARI LIBUR --}}
                    @if(isset($isLibur) && $isLibur && $pegawais->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-calendar-times fa-4x text-gray-300"></i>
                            </div>
                            <h5 class="fw-bold text-gray-600">{{ $keteranganLibur ?? 'Akhir Pekan' }}</h5>
                            <p class="text-muted small mb-0">Tidak ada pegawai yang melakukan presensi (Lembur) pada tanggal ini.</p>
                        </div>
                    @else
                        {{-- TABEL PRESENSI (Sama seperti biasa) --}}
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Pegawai</th>
                                    <th class="text-center">Masuk</th>
                                    <th class="text-center">Pulang</th>
                                    <th class="text-center">Durasi</th>
                                    <th class="text-center">Status Presensi</th>
                                    <th class="text-center">Status Bot</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pegawais as $pegawai)
                                    @php
                                        // Ambil log hari ini (bisa null jika belum scan sama sekali)
                                        $log = $pegawai->absensiLogs->first();
    
                                        // Default Values
                                        $jamMasuk  = $log->jam_masuk ?? '-';
                                        $jamKeluar = $log->jam_keluar ?? '-';
                                        $status    = $log->status ?? 'alpha'; 
                                        
                                        // AMBIL BATAS PULANG DARI DATABASE (SNAPSHOT)
                                        // Jika data lama belum punya batas_jam_keluar, kita kasih fallback jam reguler
                                        $batasPulangDb = $log->batas_jam_keluar ?? (\Carbon\Carbon::parse($tanggal)->isFriday() ? '17:00' : '16:30');

                                        // Cek Pulang Awal menggunakan $batasPulangDb
                                        $isPulangAwal = ($jamKeluar !== '-' && $jamKeluar < $batasPulangDb);

                                        // Hitung Durasi Kerja
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

                                        // Hitung Durasi Keterlambatan
                                        $durasiTelat = '';
                                        if ($status == 'terlambat' && $jamMasuk !== '-') {
                                            try {
                                                // Tentukan batas masuk berdasarkan hari
                                                $carbonDate = \Carbon\Carbon::parse($tanggal);
                                                $batasMasuk = $carbonDate->isFriday() ? '08:00' : '08:00';
                                                
                                                $batasWaktu = \Carbon\Carbon::createFromFormat('H:i', $batasMasuk);
                                                $waktuMasuk = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk);
                                                
                                                if ($waktuMasuk->greaterThan($batasWaktu)) {
                                                    $diff = $batasWaktu->diff($waktuMasuk);
                                                    $menit = ($diff->h * 60) + $diff->i;
                                                    $durasiTelat = "($menit menit)";
                                                }
                                            } catch(\Exception $e) { }
                                        }

                                        // Cek Koneksi Telegram
                                        $hasTelegram = !empty($pegawai->telegram_chat_id);
                                        
                                        // Ambil History Notif dari Database
                                        $notifHistory = $log->notif_history ?? [];
                                    @endphp

                                    <tr class="{{ $isPulangAwal ? 'bg-soft-danger' : '' }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3 text-uppercase">
                                                    {{ substr($pegawai->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $pegawai->name }}</div>
                                                    <div class="small text-muted">{{ $pegawai->nip }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold {{ $status == 'terlambat' ? 'text-danger' : 'text-dark' }}">
                                            <div>{{ $jamMasuk }}</div>
                                            @if($durasiTelat)
                                                <div class="small text-danger mt-1" style="font-size: 0.75rem;">{{ $durasiTelat }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold {{ $isPulangAwal ? 'text-danger' : 'text-dark' }}">
                                            {{ $jamKeluar }}
                                            @if($isPulangAwal)
                                                <i class="fas fa-exclamation-circle text-danger ms-1" title="Pulang Awal"></i>
                                            @endif
                                        </td>
                                        <td class="text-center text-primary fw-bold">
                                            {{ $durasiKerja }}
                                        </td>
                                        <td class="text-center">
                                            @if($status == 'hadir')
                                                {{-- Jika Hari Libur, ganti badge jadi "Masuk (Lembur)" --}}
                                                @if(isset($isLibur) && $isLibur)
                                                    <span class="badge bg-info text-white rounded-pill">Masuk (Lembur)</span>
                                                @else
                                                    <span class="badge bg-success rounded-pill">Tepat Waktu</span>
                                                @endif
                                            @elseif($status == 'terlambat')
                                                <span class="badge bg-warning text-dark rounded-pill">Terlambat</span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill">Belum Scan</span>
                                            @endif

                                            @if($isPulangAwal)
                                                <span class="badge bg-danger rounded-pill ms-1">Pulang Awal</span>
                                            @endif
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                
                                                {{-- 1. Icon Koneksi Telegram --}}
                                                @if($hasTelegram)
                                                    <span 
                                                        class="badge rounded-pill d-inline-flex align-items-center justify-content-center"
                                                        data-bs-toggle="tooltip"
                                                        title="Telegram Terhubung"
                                                        style="background-color: #0088cc; width: 28px; height: 28px;"
                                                    >
                                                        <i class="fab fa-telegram-plane text-white" style="font-size: 14px;"></i>
                                                    </span>
                                                @else

                                                    <span class="badge rounded-pill bg-light text-secondary border" data-bs-toggle="tooltip" title="Belum ada ID Telegram">
                                                        <i class="fas fa-plug-circle-xmark"></i> No ID
                                                    </span>
                                                @endif

                                                {{-- 2. Icon History Notifikasi --}}
                                                @if($hasTelegram && $log)
                                                    {{-- Notif Telat Masuk --}}
                                                    @if(isset($notifHistory['telat_masuk']))
                                                        <span class="badge rounded-pill bg-danger" title="Diingatkan Telat Masuk jam {{ $notifHistory['telat_masuk'] }}">
                                                            <i class="fas fa-bell"></i> Pagi
                                                        </span>
                                                    @endif

                                                    {{-- Notif Pulang Awal --}}
                                                    @if(isset($notifHistory['pulang_awal']))
                                                        <span class="badge rounded-pill bg-warning text-dark" title="Diingatkan Pulang Awal jam {{ $notifHistory['pulang_awal'] }}">
                                                            <i class="fas fa-bell"></i> Pulang
                                                        </span>
                                                    @endif

                                                    {{-- Notif Belum Pulang --}}
                                                    @if(isset($notifHistory['belum_pulang']))
                                                        <span class="badge rounded-pill bg-info text-white" title="Diingatkan Scan Pulang jam {{ $notifHistory['belum_pulang'] }}">
                                                            <i class="fas fa-bell"></i> Pulang
                                                        </span>
                                                    @endif

                                                    {{-- Notif Telat 2x --}}
                                                    @if(isset($notifHistory['telat_2x']))
                                                        <span class="badge rounded-pill bg-dark border border-warning text-warning" title="Peringatan Telat 2x jam {{ $notifHistory['telat_2x'] }}">
                                                            <i class="fas fa-bell"></i> Peringatan Telat 2x
                                                        </span>
                                                    @endif
                                                    
                                                    {{-- Jika Telegram ada tapi belum ada notif apa2 --}}
                                                    @if(empty($notifHistory))
                                                        <small class="text-muted ms-1" style="font-size: 0.7rem;">Standby</small>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4">Tidak ada data pegawai.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: LEADERBOARD TELAT --}}
        <div class="col-lg-4">
            <div class="leaderboard-card mb-4">
                <div class="leaderboard-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-trophy me-2"></i>Top Terlambat (Bulan Ini)</span>
                    <span class="badge bg-white text-primary border">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                </div>
                
                @if($topLate->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p class="mb-0 small">Belum ada yang terlambat bulan ini. Mantap!</p>
                    </div>
                @else
                    @foreach($topLate as $index => $late)
                        @php
                            // Format tanggal keterlambatan dengan padding 2 digit
                            $dates = explode(',', $late->tanggal_telat);
                            $formattedDates = array_map(function($day) {
                                return str_pad($day, 2, '0', STR_PAD_LEFT);
                            }, $dates);
                            $tanggalList = implode(',', $formattedDates);
                        @endphp
                        <div class="leaderboard-item">
                            <div class="d-flex align-items-center">
                                <div class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $late->user->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">{{ $late->user->nip ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger h5 mb-0">{{ $late->total_telat }}x</div>
                                <div class="small text-muted" style="font-size: 0.7rem;">Terlambat</div>
                                <div class="small text-muted" style="font-size: 0.65rem;">({{ $tanggalList }})</div>
                            </div>
                        </div>
                    @endforeach
                @endif
                
                <div class="p-3 bg-light text-center small text-muted border-top">
                    Data dihitung akumulasi per bulan berjalan
                </div>
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