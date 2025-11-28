@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.edit') }} {{ trans('cruds.kegiatan.title_singular') }}</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.kegiatan.update", [$kegiatan->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="row">
                {{-- Kolom Kiri --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="nama_kegiatan">{{ trans('cruds.kegiatan.fields.nama_kegiatan') }}</label>
                        <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required>
                        @if($errors->has('nama_kegiatan'))
                            <div class="invalid-feedback">{{ $errors->first('nama_kegiatan') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="ruangan_id">{{ trans('cruds.kegiatan.fields.ruangan') }}</label>
                        <select class="form-control select2 {{ $errors->has('ruangan_id') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id">
                            @foreach($ruangan as $id => $entry)
                                <option value="{{ $id }}" {{ (old('ruangan_id') ? old('ruangan_id') : $kegiatan->ruangan->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('ruangan_id'))
                            <div class="invalid-feedback">{{ $errors->first('ruangan_id') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="nama_pic">Nama PIC</label>
                        <input class="form-control {{ $errors->has('nama_pic') ? 'is-invalid' : '' }}" type="text" name="nama_pic" value="{{ old('nama_pic', $kegiatan->nama_pic) }}">
                        @if($errors->has('nama_pic'))
                            <div class="invalid-feedback">{{ $errors->first('nama_pic') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="nomor_telepon">Nomor Telepon PIC</label>
                        <input class="form-control {{ $errors->has('nomor_telepon') ? 'is-invalid' : '' }}" type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $kegiatan->nomor_telepon) }}">
                        @if($errors->has('nomor_telepon'))
                            <div class="invalid-feedback">{{ $errors->first('nomor_telepon') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_mulai">{{ trans('cruds.kegiatan.fields.waktu_mulai') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="waktu_mulai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Mulai)" aria-label="Buka picker waktu mulai"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai', $kegiatan->waktu_mulai) }}" required>
                        </div>
                        @if($errors->has('waktu_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_mulai') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_selesai">{{ trans('cruds.kegiatan.fields.waktu_selesai') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="waktu_selesai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Selesai)" aria-label="Buka picker waktu selesai"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai', $kegiatan->waktu_selesai) }}" required>
                        </div>
                        @if($errors->has('waktu_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_selesai') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="form-group mb-3">
                    <label for="deskripsi">Keterangan</label>
                    <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
                    @if($errors->has('deskripsi'))
                        <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                    @endif
                </div>

                <div class="form-group mb-3">
                    <label class="required" for="user_id">{{ trans('cruds.kegiatan.fields.user') }}</label>
                    <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                        @foreach($users as $id => $entry)
                            <option value="{{ $id }}" {{ (old('user_id') ? old('user_id') : $kegiatan->user->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('user_id'))
                        <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                    @endif
                </div>
            @endif

            <div class="form-group mb-3">
                <label class="form-label" for="surat_izin">Surat Izin (PDF)</label>
                <input class="form-control {{ $errors->has('surat_izin') ? 'is-invalid' : '' }}" type="file" name="surat_izin" id="surat_izin">
                @if($errors->has('surat_izin'))
                    <div class="invalid-feedback">{{ $errors->first('surat_izin') }}</div>
                @endif
                
                @if ($kegiatan->surat_izin)
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $kegiatan->surat_izin) }}" target="_blank">Lihat surat izin saat ini</a>
                    </div>
                @endif
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

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        // Toggle datetimepicker when calendar icon clicked (edit form)
        $('#waktu_mulai_toggle').on('click', function(e) {
            e.preventDefault();
            try {
                $('#waktu_mulai').data('DateTimePicker').show();
            } catch (err) {
                $('#waktu_mulai').focus();
            }
        });

        $('#waktu_selesai_toggle').on('click', function(e) {
            e.preventDefault();
            try {
                $('#waktu_selesai').data('DateTimePicker').show();
            } catch (err) {
                $('#waktu_selesai').focus();
            }
        });
    });
</script>
@endsection
