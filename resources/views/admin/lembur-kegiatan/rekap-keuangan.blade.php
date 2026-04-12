@extends('layouts.admin')

@section('styles')
<style>
    .dashboard-panel-card { background:#fff; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,.08); border:none; overflow:hidden; margin-bottom:2rem; }
    .table thead th { background-color:#f4fdf8; color:#13855c; font-weight:700; text-transform:uppercase; font-size:.75rem; letter-spacing:.05em; border-top:none; border-bottom:1px solid #e3e6f0; padding:1rem 1.5rem; }
    .table tbody td { padding:.9rem 1.5rem; vertical-align:middle; border-bottom:1px solid #e3e6f0; }
    .avatar-circle { width:38px; height:38px; background-color:#dcfce7; color:#15803d; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:.9rem; flex-shrink:0; }
    .durasi-chip { display:inline-flex; align-items:center; gap:5px; background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; border-radius:8px; padding:3px 10px; font-size:.8rem; font-weight:700; }
    .kegiatan-tag { display:inline-block; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:6px; padding:2px 8px; font-size:.75rem; font-weight:600; }
    .peran-tag { display:inline-block; background:#faf5ff; color:#6b21a8; border:1px solid #e9d5ff; border-radius:6px; padding:2px 8px; font-size:.75rem; font-weight:600; }
    .stat-summary { background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1px solid #bbf7d0; border-radius:12px; padding:1.25rem 1.5rem; }
    @media print {
        .no-print { display:none !important; }
        .content { padding:0 !important; }
    }
</style>
@endsection

@section('content')
<div class="content">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3 no-print">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.lembur-kegiatan.index', ['bulan' => $bulanParam]) }}" class="btn btn-sm btn-light text-primary border shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h3 class="h3 mb-0 text-gray-800 fw-bold">Rekap Keuangan Lembur</h3>
            </div>
            <p class="text-muted small mb-0">Rekapitulasi lembur valid lengkap beserta nama kegiatan — untuk keperluan pencairan.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.lembur-kegiatan.rekap-keuangan') }}" method="GET">
                <div class="input-group shadow-sm" style="border-radius:10px; overflow:hidden;">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-calendar-alt text-success"></i></span>
                    <input type="month" name="bulan" class="form-control border-0" value="{{ $bulanParam }}" onchange="this.form.submit()">
                </div>
            </form>
            <button onclick="window.print()" class="btn btn-outline-success shadow-sm">
                <i class="fas fa-print me-1"></i> Cetak
            </button>
        </div>
    </div>

    {{-- RINGKASAN --}}
    @php
        $totalOrang      = $rows->pluck('nip')->unique()->count();
        $totalSesi       = $rows->count();
        $totalJamAll     = $rows->sum('durasi_jam');
    @endphp
    <div class="row g-3 mb-4 no-print">
        <div class="col-md-4">
            <div class="stat-summary">
                <div class="text-muted small fw-semibold">TOTAL PEGAWAI LEMBUR</div>
                <div class="fs-3 fw-bold text-success">{{ $totalOrang }}</div>
                <div class="text-muted small">orang (unik)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-summary">
                <div class="text-muted small fw-semibold">TOTAL SESI LEMBUR</div>
                <div class="fs-3 fw-bold text-success">{{ $totalSesi }}</div>
                <div class="text-muted small">kejadian valid</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-summary">
                <div class="text-muted small fw-semibold">TOTAL JAM LEMBUR</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($totalJamAll, 1) }}</div>
                <div class="text-muted small">jam akumulatif</div>
            </div>
        </div>
    </div>

    {{-- TABEL UTAMA --}}
    <div class="card border-0 shadow-sm rounded-lg dashboard-panel-card" style="border-top:4px solid #1cc88a;">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-success">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                Detail Lembur Valid — {{ \Carbon\Carbon::createFromFormat('Y-m', $bulanParam)->translatedFormat('F Y') }}
            </h6>
            <span class="badge bg-success rounded-pill">{{ $totalSesi }} Record</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:4%;">No</th>
                        <th style="width:22%;">Pegawai</th>
                        <th style="width:22%;">Kegiatan</th>
                        <th style="width:10%;">Tanggal</th>
                        <th style="width:10%;">Peran</th>
                        <th style="width:12%;">Presensi</th>
                        <th style="width:10%;">Durasi</th>
                        <th style="width:10%;">Surat Tugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle">{{ strtoupper(substr($row->nama, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.9rem;">{{ $row->nama }}</div>
                                        <div class="small text-muted">{{ $row->nip }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="kegiatan-tag">{{ $row->kegiatan }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</span>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('l') }}</div>
                            </td>
                            <td>
                                @if($row->peran && $row->peran !== '-')
                                    <span class="peran-tag">{{ $row->peran }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <i class="fas fa-sign-in-alt text-success me-1"></i>{{ $row->jam_masuk }}
                                </div>
                                <div class="small">
                                    <i class="fas fa-sign-out-alt text-danger me-1"></i>{{ $row->jam_keluar }}
                                </div>
                            </td>
                            <td>
                                @if($row->durasi_jam)
                                    <span class="durasi-chip">
                                        <i class="fas fa-clock"></i>
                                        {{ number_format($row->durasi_jam, 1) }} jam
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->surat_tugas)
                                    <a href="{{ Storage::url($row->surat_tugas) }}" target="_blank"
                                       class="btn btn-xs btn-outline-primary py-1 px-2" style="font-size:.72rem;">
                                        <i class="fas fa-paperclip"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-mug-hot fa-3x text-success mb-3 opacity-50 d-block"></i>
                                <h5 class="fw-bold text-gray-600">Belum Ada Data Lembur Valid</h5>
                                <p class="text-muted small mb-0">Belum ada pegawai dengan status lembur valid di bulan ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->count() > 0)
                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="6" class="ps-4 fw-bold text-end text-muted py-3">Total Akumulatif</td>
                        <td colspan="2" class="py-3">
                            <span class="durasi-chip fs-6">
                                <i class="fas fa-sigma"></i>
                                {{ number_format($totalJamAll, 1) }} jam
                            </span>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection