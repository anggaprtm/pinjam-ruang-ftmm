@extends('layouts.admin')

@section('styles')
<style>
    .dashboard-panel-card { background: #fff; border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,.08); border: none; overflow: hidden; margin-bottom: 2rem; }
    .table thead th { background-color: #f4fdf8; color: #13855c; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; border-top: none; border-bottom: 1px solid #e3e6f0; padding: 1rem 1.5rem; }
    .table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
    .avatar-circle { width: 40px; height: 40px; background-color: #1cc88a; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1rem; margin-right: 1rem; }
    .date-badge { display: inline-block; padding: 4px 8px; margin: 2px; border-radius: 6px; background-color: #d1fae5; color: #065f46; font-size: 0.75rem; font-weight: 600; border: 1px solid #a7f3d0; }
</style>
@endsection

@section('content')
<div class="content">

    {{-- HEADER & NAVIGATION --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.absensi.index', ['tanggal' => $bulanParam . '-01']) }}" class="btn btn-sm btn-light text-primary border shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h3 class="h3 mb-0 text-gray-800 fw-bold">Rekap Lembur Valid</h3>
            </div>
            <p class="text-muted small mb-0">Rekapitulasi kehadiran di hari Libur/Akhir Pekan (Durasi ≥ 4 Jam).</p>
        </div>
        
        <div class="d-flex gap-2">
            {{-- FORM FILTER BULAN --}}
            <form action="{{ route('admin.absensi.rekap-lembur') }}" method="GET">
                <input type="hidden" name="role" value="{{ $roleFilter }}">
                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-calendar-alt text-success"></i></span>
                    <input type="month" name="bulan" class="form-control border-0" value="{{ $bulanParam }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    {{-- SWITCHER TENDIK / DOSEN --}}
    <div class="mb-4">
        <ul class="nav nav-pills shadow-sm p-1 bg-white rounded-pill d-inline-flex">
            <li class="nav-item">
                <a class="nav-link rounded-pill fw-bold {{ $roleFilter == 'Pegawai' ? 'active bg-success text-white' : 'text-dark' }} px-4" 
                   href="{{ request()->fullUrlWithQuery(['role' => 'Pegawai']) }}">
                   <i class="fas fa-user-tie me-2"></i>Tendik
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill fw-bold {{ $roleFilter == 'Dosen' ? 'active bg-success text-white' : 'text-dark' }} px-4" 
                   href="{{ request()->fullUrlWithQuery(['role' => 'Dosen']) }}">
                   <i class="fas fa-chalkboard-teacher me-2"></i>Dosen
                </a>
            </li>
        </ul>
    </div>

    {{-- DATA TABEL --}}
    <div class="card border-0 shadow-sm rounded-lg dashboard-panel-card" style="border-top: 4px solid #1cc88a;">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-success">
                <i class="fas fa-business-time me-2"></i>Daftar Lengkap Lembur ({{ \Carbon\Carbon::createFromFormat('Y-m', $bulanParam)->translatedFormat('F Y') }})
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 5%;">No</th>
                        <th style="width: 30%;">Pegawai</th>
                        <th class="text-center" style="width: 15%;">Total Lembur</th>
                        <th>Tanggal Kejadian (Tanggal)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekaps as $index => $stat)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 text-uppercase">
                                        {{ substr($stat->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $stat->user->name ?? 'Unknown' }}</div>
                                        <div class="small text-muted">{{ $stat->user->nip ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill fs-6 px-3 py-2 shadow-sm">
                                    {{ $stat->total_lembur }}x
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @php
                                        $dates = explode(',', $stat->tanggal_kasus);
                                        sort($dates); // Urutkan tanggal dari terkecil
                                    @endphp
                                    @foreach($dates as $day)
                                        <span class="date-badge shadow-sm">
                                            {{ str_pad(trim($day), 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-mug-hot fa-3x text-success mb-3 opacity-50"></i>
                                <h5 class="fw-bold text-gray-600">Belum Ada Data</h5>
                                <p class="text-muted small mb-0">Tidak ada rekam jejak lembur valid pada bulan ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection