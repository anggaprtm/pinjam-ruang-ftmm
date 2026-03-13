@extends('layouts.admin')
@section('content')

<div class="card detail-view-card border-0 shadow-sm">
    <div class="card-header bg-white p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">{{ $jadwalPerkuliahan->mata_kuliah }}</h2>
                <span class="badge bg-success">{{ $jadwalPerkuliahan->kode_matkul }}</span>
                <span class="badge bg-info text-white">{{ $jadwalPerkuliahan->tipe }}</span>
            </div>
            <a href="{{ route('admin.jadwal-perkuliahan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row">
            {{-- Kolom Kiri: Info Akademik --}}
            <div class="col-md-6">
                <h5 class="text-muted mb-3 small fw-bold text-uppercase">Informasi Akademik</h5>
                
                <div class="detail-item mb-3 d-flex">
                    <div class="icon me-3 text-secondary" style="width: 30px;"><i class="fas fa-calendar-alt fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small">Semester</div>
                        <div class="value fw-bold">{{ $jadwalPerkuliahan->semester->nama ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex">
                    <div class="icon me-3 text-secondary" style="width: 30px;"><i class="fas fa-graduation-cap fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small">Kelas</div>
                        <div class="value fw-bold">{{ $jadwalPerkuliahan->program_studi }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex">
                    <div class="icon me-3 text-secondary" style="width: 30px;"><i class="fas fa-user-tie fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small">Dosen Pengampu</div>
                        <div class="value fw-bold">{{ $jadwalPerkuliahan->dosen ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Info Waktu & Tempat --}}
            <div class="col-md-6">
                <h5 class="text-muted mb-3 small fw-bold text-uppercase">Waktu & Tempat</h5>

                <div class="detail-item mb-3 d-flex">
                    <div class="icon me-3 text-secondary" style="width: 30px;"><i class="fas fa-door-open fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small">Ruangan</div>
                        <div class="value fw-bold">{{ $jadwalPerkuliahan->ruangan->nama ?? '-' }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex">
                    <div class="icon me-3 text-secondary" style="width: 30px;"><i class="fas fa-calendar fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small">Hari</div>
                        <div class="value fw-bold">{{ $jadwalPerkuliahan->hari }}</div>
                    </div>
                </div>

                <div class="detail-item mb-3 d-flex">
                    <div class="icon me-3 text-secondary" style="width: 30px;"><i class="fas fa-clock fa-lg"></i></div>
                    <div class="content">
                        <div class="label text-muted small">Jam Perkuliahan</div>
                        <div class="value fw-bold">
                            {{ \Carbon\Carbon::parse($jadwalPerkuliahan->waktu_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($jadwalPerkuliahan->waktu_selesai)->format('H:i') }} WIB
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-footer bg-light p-3 text-end text-muted small">
        Data dibuat pada: {{ $jadwalPerkuliahan->created_at->format('d M Y H:i') }}
    </div>
</div>

@endsection