@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $ruangan->nama }}</h2>
                <p class="detail-sub-title mb-0">
                    Detail lengkap untuk ruangan.
                </p>
            </div>
            <a href="{{ route('admin.ruangan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.ruangan.fields.kapasitas') }}</div>
                        <div class="value">{{ $ruangan->kapasitas }} Orang</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-info-circle"></i></div>
                    <div class="content">
                        <div class="label">Status</div>
                        <div class="value">
                            @if($ruangan->is_active)
                                <span class="badge-status badge-status-aktif">Aktif</span>
                            @else
                                <span class="badge-status badge-status-tidak-aktif">Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.ruangan.fields.deskripsi') }}</div>
                        <div class="value">{{ $ruangan->deskripsi ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
