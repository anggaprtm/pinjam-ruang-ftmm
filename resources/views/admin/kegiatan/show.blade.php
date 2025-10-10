@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $kegiatan->nama_kegiatan }}</h2>
                <p class="detail-sub-title mb-0">
                    Detail lengkap untuk kegiatan peminjaman ruangan.
                </p>
            </div>
            <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row">
            {{-- Kolom Kiri: Detail Utama --}}
            <div class="col-lg-8">
                <h5 class="mb-3 font-weight-bold">Informasi Peminjaman</h5>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-user-circle"></i></div>
                    <div class="content">
                        <div class="label">Peminjam</div>
                        <div class="value">{{ $kegiatan->user->name ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-door-open"></i></div>
                    <div class="content">
                        <div class="label">Ruangan yang Dipinjam</div>
                        <div class="value">{{ $kegiatan->ruangan->nama ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="content">
                        <div class="label">Waktu Mulai</div>
                        <div class="value">{{ \Carbon\Carbon::parse($kegiatan->waktu_mulai)->translatedFormat('l, d F Y - H:i') }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="content">
                        <div class="label">Waktu Selesai</div>
                        <div class="value">{{ \Carbon\Carbon::parse($kegiatan->waktu_selesai)->translatedFormat('l, d F Y - H:i') }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                    <div class="content">
                        <div class="label">Deskripsi Kegiatan</div>
                        <div class="value">{{ $kegiatan->deskripsi ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-user-tag"></i></div>
                    <div class="content">
                        <div class="label">Nama Penanggung Jawab (PIC)</div>
                        <div class="value">{{ $kegiatan->nama_pic ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-phone"></i></div>
                    <div class="content">
                        <div class="label">Nomor Telepon Penanggung Jawab</div>
                        <div class="value">{{ $kegiatan->nomor_telepon ?? '-' }}</div>
                    </div>
                </div>
                @if($kegiatan->surat_izin)
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-paperclip"></i></div>
                    <div class="content">
                        <div class="label">Surat Izin</div>
                        <div class="value">
                            <a href="{{ asset('storage/' . $kegiatan->surat_izin) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-download me-1"></i> Lihat/Unduh Surat
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Kolom Kanan: Status & Riwayat --}}
            <div class="col-lg-4">
                <h5 class="mb-3 font-weight-bold">Status & Riwayat</h5>
                <div class="p-3 bg-light rounded">
                    <div class="detail-item">
                        <div class="icon"><i class="fas fa-info-circle"></i></div>
                        <div class="content">
                            <div class="label">Status Saat Ini</div>
                            <div class="value">
                                @php
                                    $statusClass = str_replace('_', '-', $kegiatan->status);
                                    $statusText = '';
                                    switch ($kegiatan->status) {
                                        case 'belum_disetujui': 
                                            $statusText = 'Menunggu Verifikasi Operator'; 
                                            break;
                                        case 'verifikasi_sarpras': 
                                            $statusText = 'Operator Sudah Verifikasi'; 
                                            break;
                                        case 'verifikasi_akademik': 
                                            $statusText = 'Akademik Sudah Verifikasi'; 
                                            break;
                                        case 'disetujui': 
                                            $statusText = 'Kegiatan Disetujui'; 
                                            break;
                                        case 'ditolak': 
                                            $statusText = 'Kegiatan Ditolak'; 
                                            break;
                                        default: 
                                            $statusText = $kegiatan->status; 
                                            break;
                                    }
                                @endphp
                                <span class="badge-status badge-status-{{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="icon"><i class="fas fa-comment-dots"></i></div>
                        <div class="content">
                            <div class="label">Catatan Pemroses</div>
                            <div class="value fst-italic">"{{ $kegiatan->notes ?? 'Tidak ada catatan.' }}"</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="icon"><i class="fas fa-history"></i></div>
                        <div class="content">
                            <div class="label">Riwayat Verifikasi</div>
                            <div class="value small">
                                @if($kegiatan->verifikasi_sarpras_at)
                                    <div>Verifikasi Operator: {{ \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i') }}</div>
                                @endif
                                @if($kegiatan->verifikasi_akademik_at)
                                    <div>Verifikasi Akademik: {{ \Carbon\Carbon::parse($kegiatan->verifikasi_akademik_at)->format('d/m/y H:i') }}</div>
                                @endif
                                @if($kegiatan->disetujui_at)
                                    <div>Disetujui: {{ \Carbon\Carbon::parse($kegiatan->disetujui_at)->format('d/m/y H:i') }}</div>
                                @endif
                                @if($kegiatan->ditolak_at)
                                    <div>Ditolak: {{ \Carbon\Carbon::parse($kegiatan->ditolak_at)->format('d/m/y H:i') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
