@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $mobil->nama_mobil }}</h2>
                <p class="detail-sub-title mb-0">
                    <span class="badge bg-dark fs-6">{{ $mobil->plat_nomor }}</span>
                </p>
            </div>
            
            <div class="d-flex">
                @can('mobil_edit')
                    <a href="{{ route('admin.mobils.edit', $mobil->id) }}" class="btn btn-success me-2">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>
                @endcan

                <a href="{{ route('admin.mobils.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4">
        <div class="row">
            <div class="col-md-6">
                <h5 class="font-weight-bold text-primary mb-3">Informasi Kendaraan</h5>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th style="width: 150px;">Nama Mobil</th>
                            <td>{{ $mobil->nama_mobil }}</td>
                        </tr>
                        <tr>
                            <th>Plat Nomor</th>
                            <td>{{ $mobil->plat_nomor }}</td>
                        </tr>
                        <tr>
                            <th>Warna</th>
                            <td>{{ $mobil->warna ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Saat Ini</th>
                            <td>
                                @if($mobil->status == 'tersedia')
                                    <span class="badge bg-success">Tersedia</span>
                                @elseif($mobil->status == 'dipakai')
                                    <span class="badge bg-danger">Sedang Dipakai</span>
                                @else
                                    <span class="badge bg-warning text-dark">Maintenance</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $mobil->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            {{-- OPTIONAL: Bisa menampilkan riwayat pemakaian terakhir disini nanti --}}
            <div class="col-md-6">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Riwayat perjalanan kendaraan ini dapat dilihat di menu <strong>Logbook Driver</strong>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection