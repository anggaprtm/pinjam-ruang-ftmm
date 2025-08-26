@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $role->title }}</h2>
                <p class="detail-sub-title mb-0">Detail untuk peran.</p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="detail-item">
            <div class="icon"><i class="fas fa-id-card"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.role.fields.id') }}</div>
                <div class="value">{{ $role->id }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-briefcase"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.role.fields.title') }}</div>
                <div class="value">{{ $role->title }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-unlock-alt"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.role.fields.permissions') }}</div>
                <div class="value permission-badges-container">
                    @forelse($role->permissions as $key => $item)
                        <span class="badge badge-permission">{{ $item->title }}</span>
                    @empty
                        <span>Tidak ada hak akses yang diberikan.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
