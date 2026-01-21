@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.edit') }} Data Kendaraan</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.mobils.update", [$mobil->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="nama_mobil">Nama Mobil</label>
                        <input class="form-control {{ $errors->has('nama_mobil') ? 'is-invalid' : '' }}" type="text" name="nama_mobil" id="nama_mobil" value="{{ old('nama_mobil', $mobil->nama_mobil) }}" required>
                        @if($errors->has('nama_mobil'))
                            <div class="invalid-feedback">{{ $errors->first('nama_mobil') }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="plat_nomor">Plat Nomor</label>
                        <input class="form-control {{ $errors->has('plat_nomor') ? 'is-invalid' : '' }}" type="text" name="plat_nomor" id="plat_nomor" value="{{ old('plat_nomor', $mobil->plat_nomor) }}" required>
                        @if($errors->has('plat_nomor'))
                            <div class="invalid-feedback">{{ $errors->first('plat_nomor') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="warna">Warna Mobil</label>
                        <input class="form-control {{ $errors->has('warna') ? 'is-invalid' : '' }}" type="text" name="warna" id="warna" value="{{ old('warna', $mobil->warna) }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="status">Status</label>
                        <select class="form-control select2 {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status" required>
                            <option value="tersedia" {{ $mobil->status == 'tersedia' ? 'selected' : '' }}>Tersedia (Ready)</option>
                            <option value="dipakai" {{ $mobil->status == 'dipakai' ? 'selected' : '' }}>Sedang Dipakai</option>
                            <option value="maintenance" {{ $mobil->status == 'maintenance' ? 'selected' : '' }}>Maintenance / Bengkel</option>
                        </select>
                        <small class="text-muted">Status akan berubah otomatis jika ada jadwal, namun Anda bisa mengubahnya manual disini.</small>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.mobils.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection