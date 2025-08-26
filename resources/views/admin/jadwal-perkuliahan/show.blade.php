@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $jadwalPerkuliahan->mata_kuliah }}</h2>
                <p class="detail-sub-title mb-0">Detail untuk jadwal perkuliahan.</p>
            </div>
            <a href="{{ route('admin.jadwal-perkuliahan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="detail-item">
            <div class="icon"><i class="fas fa-id-card"></i></div>
            <div class="content">
                <div class="label">ID</div>
                <div class="value">{{ $jadwalPerkuliahan->id }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-book"></i></div>
            <div class="content">
                <div class="label">Mata Kuliah</div>
                <div class="value">{{ $jadwalPerkuliahan->mata_kuliah }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="content">
                <div class="label">Dosen Pengampu</div>
                <div class="value">{{ $jadwalPerkuliahan->dosen_pengampu }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-door-open"></i></div>
            <div class="content">
                <div class="label">Ruangan</div>
                <div class="value">{{ $jadwalPerkuliahan->ruangan->nama ?? '' }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <div class="content">
                <div class="label">Hari</div>
                <div class="value">{{ $jadwalPerkuliahan->hari }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-clock"></i></div>
            <div class="content">
                <div class="label">Waktu</div>
                <div class="value">{{ \Carbon\Carbon::parse($jadwalPerkuliahan->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwalPerkuliahan->jam_selesai)->format('H:i') }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
