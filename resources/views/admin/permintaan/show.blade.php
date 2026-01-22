@extends('layouts.admin')
@section('content')

<div class="row mb-4">
    <div class="col-md-8">
        <h3 class="fw-bold text-dark">{{ $permintaan->nama_kegiatan }}</h3>
        <p class="text-muted mb-0">Diajukan oleh: <span class="fw-bold">{{ $permintaan->user->name }}</span> pada {{ $permintaan->created_at->format('d M Y, H:i') }}</p>
    </div>
    <div class="col-md-4 text-end">
        {{-- BADGE STATUS UTAMA --}}
        @php
            $badges = [
                'pending' => 'warning', 'proses' => 'info', 'selesai' => 'success', 'ditolak' => 'danger'
            ];
        @endphp
        <span class="badge bg-{{ $badges[$permintaan->status_permintaan] }} fs-6 px-3 py-2">
            Status: {{ ucfirst($permintaan->status_permintaan) }}
        </span>
    </div>
</div>

<div class="row">
    {{-- KOLOM KIRI: DETAIL PERMINTAAN --}}
    <div class="col-lg-8">
        {{-- FITUR EDIT & BATAL (Hanya muncul jika PENDING dan user berhak) --}}
        @if($permintaan->status_permintaan == 'pending')
            @if(auth()->user()->id == $permintaan->user_id || auth()->user()->isAdmin())
                <div class="card mt-3 border-danger">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle"></i> Permintaan belum diproses, Anda masih bisa mengubah atau membatalkannya.</span>
                        <div>
                            <a href="{{ route('admin.permintaan-kegiatan.edit', $permintaan->id) }}" class="btn btn-warning text-dark me-2">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.permintaan-kegiatan.destroy', $permintaan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan permintaan ini? Data akan dihapus.')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-1"></i> Batalkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">Detail Kegiatan</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Penanggung Jawab (PIC)</th>
                        <td>: {{ $permintaan->picUser->name }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Kegiatan</th>
                        <td>: {{ $permintaan->jenis_kegiatan }}</td>
                    </tr>
                    <tr>
                        <th>Waktu Pelaksanaan</th>
                        <td>
                            : <span class="fw-bold text-primary">{{ \Carbon\Carbon::parse($permintaan->tanggal_kegiatan)->format('d F Y') }}</span> <br> 
                            &nbsp; Pukul {{ \Carbon\Carbon::parse($permintaan->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($permintaan->waktu_selesai)->format('H:i') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Peserta</th>
                        <td>: {{ $permintaan->jumlah_peserta }} Orang</td>
                    </tr>
                    <tr>
                        <th>Lampiran</th>
                        <td>
                            @if($permintaan->lampiran)
                                : <a href="{{ asset('storage/'.$permintaan->lampiran) }}" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-file-download"></i> Lihat Lampiran</a>
                            @else
                                : -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: STATUS & AKSI (PROCESSOR) --}}
    <div class="col-lg-4">
        
        {{-- 1. PANEL RUANGAN --}}
        @if($permintaan->status_ruang != 'tidak_perlu')
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-building me-2"></i> Layanan Ruang</span>
                {{-- Badge Status --}}
            </div>
            <div class="card-body">
                @if($permintaan->status_ruang == 'selesai')
                    <div class="alert alert-success m-0">
                        {{-- TAMPILKAN NAMA RUANGAN DISINI --}}
                        <div class="mb-2">
                            <span class="text-muted small">Telah dijadwalkan di:</span><br>
                            <span class="fs-4 fw-bold">
                                <i class="fas fa-door-open me-1"></i> 
                                {{ $permintaan->kegiatan->ruangan->nama ?? 'Ruangan Tidak Ditemukan' }}
                            </span>
                        </div>
                        
                        {{-- TOMBOL LIHAT JADWAL --}}
                        <a href="{{ route('admin.kegiatan.show', $permintaan->kegiatan_id) }}" class="btn btn-sm btn-outline-success w-100 fw-bold">
                            Lihat Detail Jadwal <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                @else
                    <p class="small text-muted mb-3">Permintaan ini membutuhkan ruangan.</p>
                    
                    {{-- HANYA ADMIN/YANG PUNYA AKSES CREATE KEGIATAN YG BISA LIHAT TOMBOL INI --}}
                    @can('kegiatan_create') 
                        <a href="{{ route('admin.kegiatan.create', ['permintaan_id' => $permintaan->id]) }}" class="btn btn-primary w-100">
                            <i class="fas fa-door-open me-2"></i> Jadwalkan Ruang
                        </a>
                    @else
                        <div class="alert alert-warning py-2 text-center small mb-0">
                            <i class="fas fa-clock"></i> Menunggu Pemrosesan Admin Ruang
                        </div>
                    @endcan
                @endif
            </div>
        </div>
        @endif

        {{-- 2. PANEL KONSUMSI --}}
        @if($permintaan->status_konsumsi != 'tidak_perlu')
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="fas fa-utensils me-2"></i> Layanan Konsumsi</span>
                {{-- Badge Status --}}
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-3">
                    <li><strong>Jam Datang:</strong> {{ $permintaan->waktu_konsumsi }}</li>
                    <li><strong>Catatan:</strong> <br> {{ $permintaan->catatan_konsumsi }}</li>
                </ul>

                @if($permintaan->status_konsumsi != 'selesai')
                    {{-- HANYA ADMIN YANG BISA PROSES KONSUMSI --}}
                    @if(auth()->user()->isAdmin()) 
                        <form action="{{ route('admin.permintaan-kegiatan.prosesKonsumsi', $permintaan->id) }}" method="POST" onsubmit="return confirm('Tandai konsumsi sudah dipesan/siap?')">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100 text-dark fw-bold">
                                <i class="fas fa-check-circle me-2"></i> Proses Konsumsi
                            </button>
                        </form>
                    @else
                        <div class="alert alert-secondary py-2 text-center small mb-0">
                            <i class="fas fa-clock"></i> Menunggu Pemrosesan Admin Konsumsi
                        </div>
                    @endif
                @else
                    <div class="alert alert-success m-0 p-2 text-center">
                        <i class="fas fa-check"></i> Konsumsi Diproses
                    </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

<div class="mt-3">
    <a href="{{ route('admin.permintaan-kegiatan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection