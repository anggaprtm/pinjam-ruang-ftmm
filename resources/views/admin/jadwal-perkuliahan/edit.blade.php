@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.edit') }} Jadwal Perkuliahan</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.jadwal-perkuliahan.update", [$jadwalPerkuliahan->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="mata_kuliah">Mata Kuliah</label>
                        <input class="form-control {{ $errors->has('mata_kuliah') ? 'is-invalid' : '' }}" type="text" name="mata_kuliah" id="mata_kuliah" value="{{ old('mata_kuliah', $jadwalPerkuliahan->mata_kuliah) }}" required>
                        @if($errors->has('mata_kuliah'))
                            <div class="invalid-feedback">{{ $errors->first('mata_kuliah') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required" for="dosen_pengampu">Dosen Pengampu</label>
                        <input class="form-control {{ $errors->has('dosen_pengampu') ? 'is-invalid' : '' }}" type="text" name="dosen_pengampu" id="dosen_pengampu" value="{{ old('dosen_pengampu', $jadwalPerkuliahan->dosen_pengampu) }}" required>
                        @if($errors->has('dosen_pengampu'))
                            <div class="invalid-feedback">{{ $errors->first('dosen_pengampu') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required" for="ruangan_id">Ruangan</label>
                        <select class="form-control select2 {{ $errors->has('ruangan_id') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id" required>
                            @foreach($ruangan as $id => $entry)
                                <option value="{{ $id }}" {{ (old('ruangan_id') ? old('ruangan_id') : $jadwalPerkuliahan->ruangan->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('ruangan_id'))
                            <div class="invalid-feedback">{{ $errors->first('ruangan_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="hari">Hari</label>
                        <select class="form-control select2 {{ $errors->has('hari') ? 'is-invalid' : '' }}" name="hari" id="hari" required>
                            <option value="Senin" {{ (old('hari') ?? $jadwalPerkuliahan->hari) == 'Senin' ? 'selected' : '' }}>Senin</option>
                            <option value="Selasa" {{ (old('hari') ?? $jadwalPerkuliahan->hari) == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                            <option value="Rabu" {{ (old('hari') ?? $jadwalPerkuliahan->hari) == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                            <option value="Kamis" {{ (old('hari') ?? $jadwalPerkuliahan->hari) == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                            <option value="Jumat" {{ (old('hari') ?? $jadwalPerkuliahan->hari) == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                        </select>
                        @if($errors->has('hari'))
                            <div class="invalid-feedback">{{ $errors->first('hari') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required" for="jam_mulai">Jam Mulai</label>
                        <input class="form-control timepicker {{ $errors->has('jam_mulai') ? 'is-invalid' : '' }}" type="text" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', $jadwalPerkuliahan->jam_mulai) }}" required>
                        @if($errors->has('jam_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('jam_mulai') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required" for="jam_selesai">Jam Selesai</label>
                        <input class="form-control timepicker {{ $errors->has('jam_selesai') ? 'is-invalid' : '' }}" type="text" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', $jadwalPerkuliahan->jam_selesai) }}" required>
                        @if($errors->has('jam_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('jam_selesai') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('admin.jadwal-perkuliahan.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
