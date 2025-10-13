@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.ruangan.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.ruangan.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="nama">{{ trans('cruds.ruangan.fields.nama') }}</label>
                <input class="form-control {{ $errors->has('nama') ? 'is-invalid' : '' }}" type="text" name="nama" id="nama" value="{{ old('nama', '') }}" required>
                @if($errors->has('nama'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nama') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.ruangan.fields.nama_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="deskripsi">{{ trans('cruds.ruangan.fields.deskripsi') }}</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi') }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">
                        {{ $errors->first('deskripsi') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.ruangan.fields.deskripsi_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="kapasitas">{{ trans('cruds.ruangan.fields.kapasitas') }}</label>
                <input class="form-control {{ $errors->has('kapasitas') ? 'is-invalid' : '' }}" type="number" name="kapasitas" id="kapasitas" value="{{ old('kapasitas', '') }}" step="1">
                @if($errors->has('kapasitas'))
                    <div class="invalid-feedback">
                        {{ $errors->first('kapasitas') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.ruangan.fields.kapasitas_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="lantai">Lantai</label>
                <input class="form-control {{ $errors->has('lantai') ? 'is-invalid' : '' }}" type="number" name="lantai" id="lantai" value="{{ old('lantai', '') }}" step="1" required>
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
                <span class="help-block">{{ trans('cruds.ruangan.fields.foto_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection
