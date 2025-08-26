@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.create') }} {{ trans('cruds.kegiatan.title_singular') }}</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.kegiatan.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                {{-- Kolom Kiri --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="nama_kegiatan">{{ trans('cruds.kegiatan.fields.nama_kegiatan') }}</label>
                        <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', '') }}" required>
                        @if($errors->has('nama_kegiatan'))
                            <div class="invalid-feedback">{{ $errors->first('nama_kegiatan') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="ruangan_id">{{ trans('cruds.kegiatan.fields.ruangan') }}</label>
                        <select class="form-control select2 {{ $errors->has('ruangan') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id" required oninvalid="this.setCustomValidity('Silakan pilih ruangan terlebih dahulu')">
                            {{-- Menambahkan opsi 'pleaseSelect' sesuai referensi --}}
                            <option value="">{{ trans('global.pleaseSelect' )}}</option>
                            @foreach($ruangan as $id => $entry)
                                <option value="{{ $id }}" {{ old('ruangan_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('ruangan'))
                            <div class="invalid-feedback">{{ $errors->first('ruangan') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="nomor_telepon">Nomor Telepon PIC</label>
                        <input class="form-control {{ $errors->has('nomor_telepon') ? 'is-invalid' : '' }}" type="text" name="nomor_telepon" id="nomor_telepon" value="{{ old('nomor_telepon', '') }}">
                        @if($errors->has('nomor_telepon'))
                            <div class="invalid-feedback">{{ $errors->first('nomor_telepon') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_mulai">{{ trans('cruds.kegiatan.fields.waktu_mulai') }}</label>
                        <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
                        @if($errors->has('waktu_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_mulai') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_selesai">{{ trans('cruds.kegiatan.fields.waktu_selesai') }}</label>
                        <input class="form-control datetime {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
                        @if($errors->has('waktu_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_selesai') }}</div>
                        @endif
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="form-group mb-3">
                            <label class="required" for="user_id">{{ trans('cruds.kegiatan.fields.user') }}</label>
                            <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                                {{-- Menambahkan opsi 'pleaseSelect' sesuai referensi --}}
                                <option value="">{{ trans('global.pleaseSelect' )}}</option>
                                @foreach($users as $id => $entry)
                                    <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('user'))
                                <div class="invalid-feedback">{{ $errors->first('user') }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="deskripsi">{{ trans('cruds.kegiatan.fields.deskripsi') }}</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi') }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                @endif
            </div>

            {{-- =================== BAGIAN BERULANG DITAMBAHKAN KEMBALI =================== --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="berulang_sampai">Berulang sampai</label>
                        <input class="form-control date {{ $errors->has('berulang_sampai') ? 'is-invalid' : '' }}" type="text" name="berulang_sampai" id="berulang_sampai" value="{{ old('berulang_sampai') }}">
                        @if($errors->has('berulang_sampai'))
                            <div class="invalid-feedback">{{ $errors->first('berulang_sampai') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="tipe_berulang">Tipe Pengulangan</label>
                        <select class="form-control" name="tipe_berulang" id="tipe_berulang">
                            <option value="harian" selected>Harian</option>
                            <option value="mingguan">Mingguan</option>
                        </select>
                    </div>
                </div>
            </div>
            {{-- =================== AKHIR BAGIAN BERULANG =================== --}}

            <div class="form-group mb-3">
                <label class="form-label" for="surat_izin">Unggah Surat Izin</label>
                <input class="form-control {{ $errors->has('surat_izin') ? 'is-invalid' : '' }}" type="file" name="surat_izin" id="surat_izin">
                @if($errors->has('surat_izin'))
                    <div class="invalid-feedback">{{ $errors->first('surat_izin') }}</div>
                @endif
                <span class="help-block">Surat izin dapat diunggah berupa (pdf) dan tidak lebih dari 2MB</span>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
