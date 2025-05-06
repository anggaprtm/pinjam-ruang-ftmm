@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} Jadwal Perkuliahan
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.jadwal-perkuliahan.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="ruangan_id">Ruangan</label>
                <select class="form-control select2 {{ $errors->has('ruangan') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id" required oninvalid="this.setCustomValidity('Silakan pilih ruangan terlebih dahulu')">
                    @foreach($ruangan as $id => $entry)
                        <option value="{{ $id }}" {{ old('ruangan_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('ruangan'))
                    <div class="invalid-feedback">
                        {{ $errors->first('ruangan') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="mata_kuliah">Mata Kuliah</label>
                <input class="form-control {{ $errors->has('mata_kuliah') ? 'is-invalid' : '' }}" type="text" name="mata_kuliah" id="mata_kuliah" value="{{ old('mata_kuliah', '') }}" required>
                @if($errors->has('mata_kuliah'))
                    <div class="invalid-feedback">
                        {{ $errors->first('mata_kuliah') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="dosen">Dosen</label>
                <input class="form-control {{ $errors->has('dosen') ? 'is-invalid' : '' }}" type="text" name="dosen" id="dosen" value="{{ old('dosen', '') }}">
                @if($errors->has('dosen'))
                    <div class="invalid-feedback">
                        {{ $errors->first('dosen') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="hari">Hari</label>
                <select class="form-control {{ $errors->has('hari') ? 'is-invalid' : '' }}" name="hari" id="hari" required>
                    <option value="">-- Pilih Hari --</option>
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                        <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                    @endforeach
                </select>
                @if($errors->has('hari'))
                    <div class="invalid-feedback">
                        {{ $errors->first('hari') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="waktu_mulai">Waktu Mulai</label>
                <input class="form-control timepicker {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
                @if($errors->has('waktu_mulai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('waktu_mulai') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="waktu_selesai">Waktu Selesai</label>
                <input class="form-control timepicker {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
                @if($errors->has('waktu_selesai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('waktu_selesai') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="berlaku_mulai">Berlaku Mulai</label>
                <input class="form-control date {{ $errors->has('berlaku_mulai') ? 'is-invalid' : '' }}" type="text" name="berlaku_mulai" id="berlaku_mulai" value="{{ old('berlaku_mulai') }}" required>
                @if($errors->has('berlaku_mulai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('berlaku_mulai') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="berlaku_sampai">Berlaku Sampai</label>
                <input class="form-control date {{ $errors->has('berlaku_sampai') ? 'is-invalid' : '' }}" type="text" name="berlaku_sampai" id="berlaku_sampai" value="{{ old('berlaku_sampai') }}" required>
                @if($errors->has('berlaku_sampai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('berlaku_sampai') }}
                    </div>
                @endif
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
