@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $user->name }}</h2>
                <p class="detail-sub-title mb-0">Detail untuk pengguna.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="detail-item">
            <div class="icon"><i class="fas fa-id-card"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.user.fields.id') }}</div>
                <div class="value">{{ $user->id }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-user"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.user.fields.name') }}</div>
                <div class="value">{{ $user->name }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-envelope"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.user.fields.email') }}</div>
                <div class="value">{{ $user->email }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.user.fields.nip') }}</div>
                <div class="value">{{ $user->nip }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="icon"><i class="fas fa-briefcase"></i></div>
            <div class="content">
                <div class="label">{{ trans('cruds.user.fields.roles') }}</div>
                <div class="value permission-badges-container">
                    @forelse($user->roles as $key => $item)
                        <span class="badge badge-permission">{{ $item->title }}</span>
                    @empty
                        <span>Tidak ada peran yang diberikan.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
