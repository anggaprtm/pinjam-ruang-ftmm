@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.kegiatan.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.kegiatan.update", [$kegiatan->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="ruangan_id">{{ trans('cruds.kegiatan.fields.ruangan') }}</label>
                <select class="form-control select2 {{ $errors->has('ruangan') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id">
                    @foreach($ruangan as $id => $entry)
                        <option value="{{ $id }}" {{ (old('ruangan_id') ? old('ruangan_id') : $kegiatan->ruangan->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
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
                <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required>
                @if($errors->has('nama_kegiatan'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nama_kegiatan') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.nama_kegiatan_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="waktu_mulai">{{ trans('cruds.kegiatan.fields.waktu_mulai') }}</label>
                <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai', $kegiatan->waktu_mulai) }}" required>
                @if($errors->has('waktu_mulai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('waktu_mulai') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.waktu_mulai_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="waktu_selesai">{{ trans('cruds.kegiatan.fields.waktu_selesai') }}</label>
                <input class="form-control datetime {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai', $kegiatan->waktu_selesai) }}" required>
                @if($errors->has('waktu_selesai'))
                    <div class="invalid-feedback">
                        {{ $errors->first('waktu_selesai') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.kegiatan.fields.waktu_selesai_helper') }}</span>
            </div>
            <div class="form-group">
                @if(auth()->user()->isAdmin())
                    <label for="deskripsi">Keterangan</label>
                    <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
                    @if($errors->has('deskripsi'))
                        <div class="invalid-feedback">
                            {{ $errors->first('deskripsi') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.kegiatan.fields.deskripsi_helper') }}</span>
                @endif
            </div>
            <div class="form-group">
                @if(auth()->user()->isAdmin())
                    <label class="required" for="user_id">{{ trans('cruds.kegiatan.fields.user') }}</label>
                    <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                        @foreach($users as $id => $entry)
                            <option value="{{ $id }}" {{ (old('user_id') ? old('user_id') : $kegiatan->user->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('user'))
                        <div class="invalid-feedback">
                            {{ $errors->first('user') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.kegiatan.fields.user_helper') }}</span>
                @endif
                </div>
                <div class="form-group">
                <label for="surat_izin">Surat Izin (PDF)</label>
                <!-- Input file -->
                <input class="form-control {{ $errors->has('surat_izin') ? 'is-invalid' : '' }}" type="file" name="surat_izin" id="surat_izin">
                
                <!-- Tampilkan pesan error jika ada -->
                @if($errors->has('surat_izin'))
                    <div class="invalid-feedback">
                        {{ $errors->first('surat_izin') }}
                    </div>
                @endif

                <!-- Link untuk melihat file surat izin jika sudah diunggah -->
                @if ($kegiatan->surat_izin)
                    <span class="help-block">
                        <a href="{{ asset('storage/' . $kegiatan->surat_izin) }}" target="_blank">Lihat Surat Izin Saat Ini</a>
                    </span>
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