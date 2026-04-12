@extends('layouts.admin')

@section('styles')
<style>
    .lk-card { background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.07); border:none; overflow:hidden; margin-bottom:1.5rem; transition:box-shadow .2s; }
    .lk-card:hover { box-shadow:0 8px 32px rgba(0,0,0,.12); }
    .lk-card-header { padding:1rem 1.5rem; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; }
    .tanggal-chip { display:inline-flex; align-items:center; gap:6px; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; border-radius:8px; padding:4px 12px; font-size:.8rem; font-weight:600; }
    .stat-pill { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 10px; font-size:.75rem; font-weight:600; }
    .pill-valid { background:#d1fae5; color:#065f46; }
    .pill-menunggu { background:#fef9c3; color:#713f12; }
    .pill-tidak { background:#fee2e2; color:#7f1d1d; }
    .empty-state { text-align:center; padding:4rem 2rem; }
    .table thead th { background:#f8fafc; color:#475569; font-weight:700; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:2px solid #e2e8f0; padding:.85rem 1.25rem; }
    .table tbody td { padding:.85rem 1.25rem; vertical-align:middle; border-bottom:1px solid #f1f5f9; }
</style>
@endsection

@section('content')
<div class="content">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="fas fa-business-time text-success me-2"></i>Manajemen Kegiatan Lembur
            </h3>
            <p class="text-muted small mb-0">Kelola kegiatan lembur dan assignment pegawai. Validasi otomatis berdasarkan data presensi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.lembur-kegiatan.rekap-keuangan') }}" class="btn btn-outline-success shadow-sm">
                <i class="fas fa-file-invoice-dollar me-1"></i> Rekap Keuangan
            </a>
            <a href="{{ route('admin.lembur-kegiatan.create') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-plus me-1"></i> Buat Kegiatan
            </a>
        </div>
    </div>

    {{-- FILTER BULAN --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <form action="{{ route('admin.lembur-kegiatan.index') }}" method="GET" class="d-flex gap-2">
            <div class="input-group shadow-sm" style="border-radius:10px; overflow:hidden; width:220px;">
                <span class="input-group-text bg-white border-0"><i class="fas fa-calendar-alt text-success"></i></span>
                <input type="month" name="bulan" class="form-control border-0" value="{{ $bulanParam }}" onchange="this.form.submit()">
            </div>
        </form>
        <span class="text-muted small">{{ \Carbon\Carbon::createFromFormat('Y-m', $bulanParam)->translatedFormat('F Y') }}</span>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- LIST KEGIATAN --}}
    @forelse($kegiatan as $item)
        @php
            $totalAssign = $item->pegawaiAssignments->count();
            $totalValid  = $item->pegawaiAssignments->where('status_validasi', 'valid')->count();
            $totalMenunggu = $item->pegawaiAssignments->where('status_validasi', 'menunggu')->count();
            $totalTidak  = $item->pegawaiAssignments->where('status_validasi', 'tidak_valid')->count();
            $hariTanggal = \Carbon\Carbon::parse($item->tanggal);
        @endphp
        <div class="lk-card">
            <div class="lk-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="tanggal-chip">
                        <i class="fas fa-calendar-day"></i>
                        {{ $hariTanggal->translatedFormat('l, d F Y') }}
                    </div>
                    @if($hariTanggal->isWeekend())
                        <span class="badge bg-warning text-dark">Weekend</span>
                    @else
                        <span class="badge bg-info text-white">Libur Nasional</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.lembur-kegiatan.show', $item) }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-eye me-1"></i>Detail
                    </a>
                    <a href="{{ route('admin.lembur-kegiatan.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <form action="{{ route('admin.lembur-kegiatan.destroy', $item) }}" method="POST"
                          onsubmit="return confirm('Hapus kegiatan ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            <div class="card-body px-4 py-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">{{ $item->nama_kegiatan }}</h5>
                        @if($item->deskripsi)
                            <p class="text-muted small mb-2">{{ Str::limit($item->deskripsi, 120) }}</p>
                        @endif
                        @if($item->file_surat_tugas)
                            <a href="{{ Storage::url($item->file_surat_tugas) }}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2" style="font-size:.75rem;">
                                <i class="fas fa-paperclip me-1"></i>Surat Tugas
                            </a>
                        @endif
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0 ms-3">
                        <span class="stat-pill pill-valid">
                            <i class="fas fa-check-circle"></i> {{ $totalValid }} Valid
                        </span>
                        @if($totalMenunggu > 0)
                        <span class="stat-pill pill-menunggu">
                            <i class="fas fa-times-circle"></i> {{ $totalMenunggu }} Tidak FaceRecog
                        </span>
                        @endif
                        @if($totalTidak > 0)
                        <span class="stat-pill pill-tidak">
                            <i class="fas fa-times-circle"></i> {{ $totalTidak }} Tidak Valid
                        </span>
                        @endif
                        <span class="stat-pill" style="background:#f1f5f9; color:#475569;">
                            <i class="fas fa-users"></i> {{ $totalAssign }} Pegawai
                        </span>
                    </div>
                </div>

                {{-- Preview nama pegawai --}}
                @if($totalAssign > 0)
                <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-1">
                    @foreach($item->pegawaiAssignments->take(6) as $assign)
                        @php
                            $colorClass = match($assign->status_validasi) {
                                'valid'       => 'bg-success text-white',
                                'tidak_valid' => 'bg-danger text-white',
                                default       => 'bg-warning text-dark',
                            };
                        @endphp
                        <span class="badge {{ $colorClass }} fw-normal" style="font-size:.72rem;">
                            {{ $assign->user->name ?? 'Unknown' }}
                        </span>
                    @endforeach
                    @if($totalAssign > 6)
                        <span class="badge bg-secondary fw-normal" style="font-size:.72rem;">+{{ $totalAssign - 6 }} lainnya</span>
                    @endif
                </div>
                @endif

                <div class="mt-2 text-end">
                    <small class="text-muted">Dibuat {{ $item->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-lg">
            <div class="empty-state">
                <i class="fas fa-umbrella-beach fa-3x text-success mb-3 opacity-50"></i>
                <h5 class="fw-bold text-gray-600">Belum Ada Kegiatan Lembur</h5>
                <p class="text-muted small">Belum ada kegiatan lembur yang dibuat untuk bulan ini.</p>
                <a href="{{ route('admin.lembur-kegiatan.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Buat Kegiatan Pertama
                </a>
            </div>
        </div>
    @endforelse

</div>
@endsection