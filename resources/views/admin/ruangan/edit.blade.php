@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.edit') }} {{ trans('cruds.ruangan.title_singular') }}</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.ruangan.update", [$ruangan->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group mb-3">
                <label class="form-label required" for="nama">{{ trans('cruds.ruangan.fields.nama') }}</label>
                <input class="form-control {{ $errors->has('nama') ? 'is-invalid' : '' }}" type="text" name="nama" id="nama" value="{{ old('nama', $ruangan->nama) }}" required>
                @if($errors->has('nama'))
                    <div class="invalid-feedback">{{ $errors->first('nama') }}</div>
                @endif
            </div>

            <div class="form-group mb-3">
                <label class="form-label required" for="kapasitas">{{ trans('cruds.ruangan.fields.kapasitas') }}</label>
                <input class="form-control {{ $errors->has('kapasitas') ? 'is-invalid' : '' }}" type="number" name="kapasitas" id="kapasitas" value="{{ old('kapasitas', $ruangan->kapasitas) }}" step="1" required>
                @if($errors->has('kapasitas'))
                    <div class="invalid-feedback">{{ $errors->first('kapasitas') }}</div>
                @endif
            </div>
            <div class="form-group">
                <label class="required" for="lantai">Lantai</label>
                <input class="form-control {{ $errors->has('lantai') ? 'is-invalid' : '' }}" type="number" name="lantai" id="lantai" value="{{ old('lantai', $ruangan->lantai) }}" step="1" required>
                @if($errors->has('lantai'))
                    <div class="invalid-feedback">{{ $errors->first('lantai') }}</div>
                @endif
            </div>
            <div class="form-group">
                <label for="foto">Foto Ruangan</label>
                <input type="file" class="form-control-file {{ $errors->has('foto') ? 'is-invalid' : '' }}" name="foto" id="foto">
                @if($errors->has('foto'))
                    <div class="invalid-feedback">{{ $errors->first('foto') }}</div>
                @endif
                <span class="help-block">Unggah gambar berupa .jpg dengan ukuran maksimal 2MB</span>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="deskripsi">{{ trans('cruds.ruangan.fields.deskripsi') }}</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi', $ruangan->deskripsi) }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                @endif
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Status</label>
                <div class="form-check {{ $errors->has('is_active') ? 'is-invalid' : '' }}">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $ruangan->is_active || old('is_active', 0) === 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif</label>
                </div>
                @if($errors->has('is_active'))
                    <div class="invalid-feedback">{{ $errors->first('is_active') }}</div>
                @endif
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.ruangan.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
