@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.create') }} Jadwal Perkuliahan</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.jadwal-perkuliahan.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group mb-3">
                        <label class="form-label required" for="tipe">Tipe</label>
                        <select class="form-control {{ $errors->has('tipe') ? 'is-invalid' : '' }}" name="tipe" id="tipe" required>
                            <option value="">-- Pilih Tipe --</option>
                            @foreach(['Kuliah Reguler', 'PHL'] as $tipe)
                                <option value="{{ $tipe }}" {{ old('tipe') == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('tipe'))
                            <div class="invalid-feedback">{{ $errors->first('tipe') }}</div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label required" for="kode_matkul">Kode MK</label>
                            {{-- Perhatikan value old-nya sesuaikan jika di file edit --}}
                            <input class="form-control {{ $errors->has('kode_matkul') ? 'is-invalid' : '' }}" 
                                type="text" name="kode_matkul" id="kode_matkul" 
                                value="{{ old('kode_matkul', isset($jadwalPerkuliahan) ? $jadwalPerkuliahan->kode_matkul : '') }}" 
                                placeholder="Cth: TI-301" required>
                        </div>
                    </div>

                    {{-- Mata Kuliah (Sisa col-md-8) --}}
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label class="form-label required" for="mata_kuliah">Mata Kuliah</label>
                            <input class="form-control {{ $errors->has('mata_kuliah') ? 'is-invalid' : '' }}" 
                                type="text" name="mata_kuliah" id="mata_kuliah" 
                                value="{{ old('mata_kuliah', isset($jadwalPerkuliahan) ? $jadwalPerkuliahan->mata_kuliah : '') }}" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="dosen_pengampu">Dosen Pengampu (Opsional)</label>
                        <input class="form-control {{ $errors->has('dosen_pengampu') ? 'is-invalid' : '' }}" type="text" name="dosen_pengampu" id="dosen_pengampu" value="{{ old('dosen_pengampu', '') }}">
                        @if($errors->has('dosen_pengampu'))
                            <div class="invalid-feedback">{{ $errors->first('dosen_pengampu') }}</div>
                        @endif
                    </div>


                    <div class="form-group mb-3">
                        <label class="form-label required" for="ruangan_id">Ruangan</label>
                        <select class="form-control select2 {{ $errors->has('ruangan_id') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id" required>
                            @foreach($ruangan as $id => $entry)
                                <option value="{{ $id }}" {{ old('ruangan_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
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
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('hari'))
                            <div class="invalid-feedback">{{ $errors->first('hari') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_mulai">Jam Mulai</label>
                        <input class="form-control timepicker {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('jam_mulai') }}" required>
                        @if($errors->has('waktu_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_mulai') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_selesai">Jam Selesai</label>
                        <input class="form-control timepicker {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('jam_selesai') }}" required>
                        @if($errors->has('waktu_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_selesai') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="berlaku_mulai">Berlaku Mulai</label>
                        <input class="form-control date {{ $errors->has('berlaku_mulai') ? 'is-invalid' : '' }}" type="text" name="berlaku_mulai" id="berlaku_mulai" value="{{ old('berlaku_mulai') }}" required>
                        @if($errors->has('berlaku_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('berlaku_mulai') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="berlaku_sampai">Berlaku Sampai</label>
                        <input class="form-control date {{ $errors->has('berlaku_sampai') ? 'is-invalid' : '' }}" type="text" name="berlaku_sampai" id="berlaku_sampai" value="{{ old('berlaku_sampai') }}" required>
                        @if($errors->has('berlaku_sampai'))
                            <div class="invalid-feedback">{{ $errors->first('berlaku_sampai') }}</div>
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
