@extends('layouts.admin')
@section('content')

<div class="card detail-view-card shadow-sm border-0">
    <div class="detail-header bg-light border-bottom p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-success text-white me-3 d-flex justify-content-center align-items-center" style="width: 60px; height: 60px; font-size: 24px; border-radius: 50%;">
                    {{ substr($tendik->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="detail-title h4 mb-1 fw-bold">{{ $tendik->tendikDetail->nama_lengkap ?? $tendik->name }}</h2>
                    <p class="detail-sub-title mb-0 text-muted"><i class="fas fa-briefcase me-1"></i> {{ $tendik->tendikDetail->nama_jabatan ?? 'Pegawai' }}</p>
                </div>
            </div>
            <a href="{{ route('admin.tendik.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    
    <div class="card-body p-4">
        <div class="row">
            {{-- Kolom Kiri: Akun & Identitas --}}
            <div class="col-lg-6 mb-4 mb-lg-0 border-end pe-lg-4">
                <h5 class="mb-4 font-weight-bold text-primary"><i class="fas fa-user me-2"></i> Data Pribadi</h5>
                
                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-address-card fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">NIK KTP</div>
                        <div class="value">{{ $tendik->tendikDetail->nik_ktp ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-venus-mars fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Jenis Kelamin</div>
                        <div class="value">
                            @if(($tendik->tendikDetail->jenis_kelamin ?? '') == 'L') Laki-laki
                            @elseif(($tendik->tendikDetail->jenis_kelamin ?? '') == 'P') Perempuan
                            @else - @endif
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-birthday-cake fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Tempat, Tanggal Lahir</div>
                        <div class="value">
                            {{ $tendik->tendikDetail->tempat_lahir ?? '-' }}, 
                            {{ $tendik->tendikDetail->tanggal_lahir ? \Carbon\Carbon::parse($tendik->tendikDetail->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fab fa-whatsapp fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">No. Ponsel (WA) / Email</div>
                        <div class="value">{{ $tendik->tendikDetail->no_ponsel ?? '-' }} <br> <small class="text-muted">{{ $tendik->email }}</small></div>
                    </div>
                </div>

                <div class="detail-item d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-map-marker-alt fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Alamat</div>
                        <div class="value">{{ $tendik->tendikDetail->alamat ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Kepegawaian --}}
            <div class="col-lg-6 ps-lg-4">
                <h5 class="mb-4 font-weight-bold text-success"><i class="fas fa-id-badge me-2"></i> Data Kepegawaian</h5>
                
                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-fingerprint fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">NIP / NIK</div>
                        <div class="value fw-bold">
                            {{ $tendik->nip ?? '-' }} <span class="text-muted fw-normal mx-1">/</span> {{ $tendik->tendikDetail->nik ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-network-wired fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Sub Bagian / Unit</div>
                        <div class="value">{{ $tendik->tendikDetail->sub_bagian ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-file-signature fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Status Kepegawaian</div>
                        <div class="value">
                            @if($tendik->tendikDetail && $tendik->tendikDetail->status_kepegawaian)
                                <span class="badge bg-info text-white">{{ $tendik->tendikDetail->status_kepegawaian }}</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-layer-group fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Pangkat & Golongan</div>
                        <div class="value">{{ $tendik->tendikDetail->pangkat_golongan ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-calendar-alt fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">TMT (Terhitung Mulai Tgl)</div>
                        <div class="value">
                            {{ $tendik->tendikDetail->tmt ? \Carbon\Carbon::parse($tendik->tendikDetail->tmt)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                </div>
                
                <div class="detail-item d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-money-check-alt fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">NPWP</div>
                        <div class="value">{{ $tendik->tendikDetail->npwp ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection