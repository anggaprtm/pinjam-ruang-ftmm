@extends('layouts.admin')
@section('content')

<div class="card detail-view-card shadow-sm border-0">
    <div class="detail-header bg-light border-bottom p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-primary text-white me-3 d-flex justify-content-center align-items-center" style="width: 60px; height: 60px; font-size: 24px; border-radius: 50%;">
                    {{ substr($dosen->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="detail-title h4 mb-1 fw-bold">{{ $dosen->dosenDetail->nama_lengkap_gelar ?? $dosen->name }}</h2>
                    <p class="detail-sub-title mb-0 text-muted"><i class="fas fa-envelope me-1"></i> {{ $dosen->email }}</p>
                </div>
            </div>
            <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    
    <div class="card-body p-4">
        <div class="row">
            {{-- Kolom Kiri: Akun & Identitas Pribadi --}}
            <div class="col-lg-6 mb-4 mb-lg-0 border-end pe-lg-4">
                <h5 class="mb-4 font-weight-bold text-primary"><i class="fas fa-user-circle me-2"></i> Identitas Pribadi & Akun</h5>
                
                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-id-badge fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">NIP / NIPTT</div>
                        <div class="value fw-bold">{{ $dosen->nip ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-address-card fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">NIK KTP</div>
                        <div class="value">{{ $dosen->dosenDetail->nik ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-venus-mars fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Jenis Kelamin</div>
                        <div class="value">
                            @if(($dosen->dosenDetail->jenis_kelamin ?? '') == 'L') Laki-laki
                            @elseif(($dosen->dosenDetail->jenis_kelamin ?? '') == 'P') Perempuan
                            @else - @endif
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fas fa-birthday-cake fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Tempat, Tanggal Lahir</div>
                        <div class="value">
                            {{ $dosen->dosenDetail->tempat_lahir ?? '-' }}, 
                            {{ $dosen->dosenDetail->tanggal_lahir ? \Carbon\Carbon::parse($dosen->dosenDetail->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="detail-item d-flex align-items-start">
                    <div class="icon text-primary me-3 mt-1"><i class="fab fa-whatsapp fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">No. Ponsel (WA)</div>
                        <div class="value">{{ $dosen->dosenDetail->no_ponsel ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Detail Akademik & Kepegawaian --}}
            <div class="col-lg-6 ps-lg-4">
                <h5 class="mb-4 font-weight-bold text-success"><i class="fas fa-university me-2"></i> Akademik & Kepegawaian</h5>
                
                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-info-circle fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Status Keaktifan</div>
                        <div class="value mt-1">
                            @php
                                $status = $dosen->dosenDetail->status_keaktifan ?? 'Aktif';
                                $badgeClass = 'bg-success';
                                if($status == 'Tugas Belajar') $badgeClass = 'bg-info';
                                elseif($status == 'Izin' || $status == 'Cuti') $badgeClass = 'bg-warning text-dark';
                                elseif($status == 'Pensiun') $badgeClass = 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-3">{{ $status }}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-id-card-alt fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">NIDN / NUPTK</div>
                        <div class="value fw-bold">
                            {{ $dosen->dosenDetail->nidn ?? '-' }} / {{ $dosen->dosenDetail->nuptk ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-briefcase fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Status Pegawai</div>
                        <div class="value">{{ $dosen->dosenDetail->status_kepegawaian ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-medal fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Jabatan Fungsional & Golongan</div>
                        <div class="value">
                            {{ $dosen->dosenDetail->jabatan_fungsional ?? '-' }} 
                            <span class="badge bg-light text-dark border ms-1">{{ $dosen->dosenDetail->pangkat_golongan ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-building fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Homebase Prodi</div>
                        <div class="value">{{ $dosen->dosenDetail->homebase_prodi ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex align-items-start">
                    <div class="icon text-success me-3 mt-1"><i class="fas fa-calendar-check fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small fw-bold text-uppercase">Tgl. Mulai Menjadi Dosen</div>
                        <div class="value">
                            {{ $dosen->dosenDetail->tgl_mulai_dosen ? \Carbon\Carbon::parse($dosen->dosenDetail->tgl_mulai_dosen)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection