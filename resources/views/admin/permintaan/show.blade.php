@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    {{-- HEADER --}}
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $permintaan->nama_kegiatan }}</h2>
                <p class="detail-sub-title mb-0">
                    Diajukan oleh: <strong>{{ $permintaan->user->name }}</strong> pada {{ $permintaan->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                {{-- BADGE STATUS UTAMA --}}
                @php
                    $status = $permintaan->status_permintaan;
                    $cls = 'badge-soft-secondary';
                    if ($status == 'pending') $cls = 'badge-soft-warning';
                    if ($status == 'proses') $cls = 'badge-soft-info';
                    if ($status == 'selesai') $cls = 'badge-soft-success';
                    if ($status == 'ditolak') $cls = 'badge-soft-danger';
                @endphp
                <span class="badge-pill-modern {{ $cls }} fs-6 px-3 py-2">
                    {{ ucfirst($permintaan->status_permintaan) }}
                </span>

                <a href="{{ route('admin.permintaan-kegiatan.index') }}" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row">
            {{-- KOLOM KIRI: DETAIL INFORMASI --}}
            <div class="col-lg-7">
                <h5 class="mb-3 font-weight-bold">Informasi Kegiatan</h5>
                
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-tag"></i></div>
                    <div class="content">
                        <div class="label">Jenis Kegiatan</div>
                        <div class="value">
                            <span class="badge bg-info text-gray-100">{{ $permintaan->jenis_kegiatan }}</span>
                        </div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-user-tag"></i></div>
                    <div class="content">
                        <div class="label">Penanggung Jawab (PIC)</div>
                        <div class="value">{{ $permintaan->picUser->name }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="content">
                        <div class="label">Waktu Pelaksanaan</div>
                        <div class="value">
                            {{ \Carbon\Carbon::parse($permintaan->tanggal_kegiatan)->translatedFormat('l, d F Y') }}
                        </div>
                        <div class="value small text-muted">
                            {{ \Carbon\Carbon::parse($permintaan->waktu_mulai)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($permintaan->waktu_selesai)->format('H:i') }} WIB
                        </div>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="content">
                        <div class="label">Estimasi Peserta</div>
                        <div class="value">{{ $permintaan->jumlah_peserta }} Orang</div>
                    </div>
                </div>

                @if($permintaan->lampiran)
                    <div class="detail-item">
                        <div class="icon"><i class="fas fa-paperclip text-muted"></i></div>
                        <div class="content">
                            <div class="label">Lampiran</div>

                            <a href="{{ \Illuminate\Support\Facades\Storage::url($permintaan->lampiran) }}"
                                target="_blank"
                                rel="noopener"
                                class="btn btn-sm btn-outline-primary mt-1">
                                <i class="fas fa-download me-1"></i> Unduh Lampiran
                            </a>

                        </div>
                    </div>
                @endif


                {{-- FITUR EDIT & DELETE (Hanya PENDING) --}}
                @if($permintaan->status_permintaan == 'pending' && (auth()->user()->id == $permintaan->user_id || auth()->user()->isAdmin()))
                    <hr>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.permintaan-kegiatan.edit', $permintaan->id) }}" class="btn btn-success">
                            <i class="fas fa-edit me-1"></i> Edit Data
                        </a>
                        <form action="{{ route('admin.permintaan-kegiatan.destroy', $permintaan->id) }}" method="POST" onsubmit="return confirm('Batalkan permintaan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger"><i class="fas fa-trash me-1"></i> Batalkan</button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- KOLOM KANAN: STATUS LAYANAN (KARTU) --}}
            <div class="col-lg-5">
                <h5 class="mb-3 font-weight-bold">Status Layanan</h5>

                {{-- 1. LAYANAN RUANG --}}
                @if($permintaan->status_ruang != 'tidak_perlu')
                <div class="card mb-3 border-0 shadow-sm" style="border-left: 5px solid #0d6efd !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0" style="color:#0d6efd;">
                                <i class="fas fa-building me-2"></i> Ruangan
                            </h6>

                            @if($permintaan->status_ruang == 'selesai')
                                <span class="badge-pill-modern badge-soft-success">SELESAI</span>
                            @else
                                <span class="badge-pill-modern badge-soft-warning">PENDING</span>
                            @endif
                        </div>
                        
                        @if($permintaan->status_ruang == 'selesai')
                            <div class="p-2 bg-light rounded mt-2">
                                <small class="text-muted">Dijadwalkan di:</small>
                                <div class="fw-bold fs-5 text-dark">
                                    {{ $permintaan->kegiatan->ruangan->nama ?? 'Ruangan' }}
                                </div>
                                <a href="{{ route('admin.kegiatan.show', $permintaan->kegiatan_id) }}" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold mt-1">
                                    Lihat Jadwal <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @else
                            <div class="alert alert-info py-1 px-2 small text-center mb-0">Menunggu Plotting Ruangan</div>
                            @can('kegiatan_create')
                                <a href="{{ route('admin.kegiatan.create', ['permintaan_id' => $permintaan->id]) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-plus-circle me-1"></i> Jadwalkan Ruang
                                </a>
                            @endcan
                        @endif
                    </div>
                </div>
                @endif

                {{-- 2. LAYANAN KONSUMSI --}}
                @if($permintaan->status_konsumsi != 'tidak_perlu')
                <div class="card mb-3 border-0 shadow-sm" style="border-left: 5px solid #ffc107 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0" style="color:#ffc107;">
                                <i class="fas fa-utensils me-2"></i> Konsumsi
                            </h6>

                            @if($permintaan->status_konsumsi == 'selesai')
                                <span class="badge-pill-modern badge-soft-success">SELESAI</span>
                            @elseif($permintaan->status_konsumsi == 'diproses')
                                <span class="badge-pill-modern badge-soft-info">DIPROSES</span>
                            @else
                                <span class="badge-pill-modern badge-soft-warning">PENDING</span>
                            @endif
                        </div>

                        <div class="small bg-light p-2 rounded mb-2">
                            <div><strong>Waktu:</strong> {{ \Carbon\Carbon::parse($permintaan->waktu_konsumsi)->format('H:i') }}</div>
                            <div><strong>Catatan:</strong> {{ $permintaan->catatan_konsumsi }}</div>
                        </div>

                        @if($permintaan->status_konsumsi != 'selesai')
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('admin.permintaan-kegiatan.prosesKonsumsi', $permintaan->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-warning btn-sm w-100 text-dark fw-bold">
                                        <i class="fas fa-check me-1"></i> Tandai Selesai / Diproses
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-warning py-1 px-2 small text-center mb-0">Menunggu Admin</div>
                            @endif
                        @else
                            <div class="text-success small fw-bold"><i class="fas fa-check-circle"></i> Konsumsi telah diproses.</div>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection