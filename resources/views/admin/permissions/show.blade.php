@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $permission->title }}</h2>
                <p class="detail-sub-title mb-0">
                    Detail untuk hak akses.
                </p>
            </div>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-12">
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-id-card"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.permission.fields.id') }}</div>
                        <div class="value">{{ $permission->id }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-unlock-alt"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.permission.fields.title') }}</div>
                        <div class="value">{{ $permission->title }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
