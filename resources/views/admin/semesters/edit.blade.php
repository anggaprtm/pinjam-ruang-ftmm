@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Edit Semester
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.semesters.update", [$semester->id]) }}">
            @method('PUT')
            @csrf
            <div class="form-group mb-3">
                <label class="required" for="nama">Nama Semester</label>
                <input class="form-control" type="text" name="nama" id="nama" value="{{ old('nama', $semester->nama) }}" required>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="tanggal_mulai">Tanggal Mulai</label>
                        <div class="input-group">
                            <span class="input-group-text" id="waktu_mulai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Tanggal Mulai)" aria-label="Buka picker tanggal mulai"><i class="fas fa-calendar-alt"></i></span>
                                <input class="form-control date {{ $errors->has('tanggal_mulai') ? 'is-invalid' : '' }}" type="text" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                                @if($errors->has('berlaku_mulai'))
                                    <div class="invalid-feedback">{{ $errors->first('tanggal_mulai') }}</div>
                                @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label required" for="tanggal_selesai">Tanggal Selesai</label>
                    <div class="input-group">
                        <span class="input-group-text" id="waktu_mulai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Tanggal Selesai)" aria-label="Buka picker tanggal selesai"><i class="fas fa-calendar-alt"></i></span>
                        <input class="form-control date {{ $errors->has('tanggal_selesai') ? 'is-invalid' : '' }}" type="text" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                        @if($errors->has('tanggal_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('tanggal_selesai') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $semester->is_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold ms-3" for="is_active">Status Aktif</label>
                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-primary" type="submit">Update</button>
                <a href="{{ route('admin.semesters.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection