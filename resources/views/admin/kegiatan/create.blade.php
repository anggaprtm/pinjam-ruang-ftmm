@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.kegiatan.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.kegiatan.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="ruangan_id">{{ trans('cruds.kegiatan.fields.ruangan') }}</label>
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
                <span class="help-block">{{ trans('cruds.kegiatan.fields.ruangan_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="nama_kegiatan">{{ trans('cruds.kegiatan.fields.nama_kegiatan') }}</label>
                <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', '') }}" required>
                @if($errors->has('nama_kegiatan'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nama_kegiatan') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.nama_kegiatan_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="waktu_mulai">{{ trans('cruds.kegiatan.fields.waktu_mulai') }}</label>
                <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
                @if($errors->has('waktu_mulai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('waktu_mulai') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.waktu_mulai_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="waktu_selesai">{{ trans('cruds.kegiatan.fields.waktu_selesai') }}</label>
                <input class="form-control datetime {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
                @if($errors->has('waktu_selesai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('waktu_selesai') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.waktu_selesai_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="deskripsi">{{ trans('cruds.kegiatan.fields.deskripsi') }}</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi') }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">
                        {{ $errors->first('deskripsi') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.deskripsi_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="user_id">{{ trans('cruds.kegiatan.fields.user') }}</label>
                <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                    <option value="">{{ trans('global.pleaseSelect' )}}</option>
                    @foreach($users as $id => $entry)
                        <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>   
                @if($errors->has('user'))
                    <div class="invalid-feedback">
                        {{ $errors->first('user') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.user_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="berulang_sampai">Berulang sampai</label>
                <input class="form-control date {{ $errors->has('berulang_sampai') ? 'is-invalid' : '' }}" type="text" name="berulang_sampai" id="berulang_sampai" value="{{ old('berulang_sampai') }}">
                @if($errors->has('berulang_sampai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('berulang_sampai') }}
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label for="tipe_berulang">Tipe Pengulangan</label>
                <select class="form-control" name="tipe_berulang" id="tipe_berulang">
                    <option value="harian" selected>Harian</option>
                    <option value="mingguan">Mingguan</option>
                </select>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- <script>
    function toggleCustomUserInput() {
        const userSelect = document.getElementById('user_id');
        const customUserInput = document.getElementById('customUserInput');

        if (userSelect.value === 'custom') {
            customUserInput.style.display = 'block';
            customUserInput.required = true;
        } else {
            customUserInput.style.display = 'none';
            customUserInput.required = false;
            customUserInput.value = ''; // Hapus nilai input jika tidak digunakan
        }
    }
</script> -->


@endsection 
