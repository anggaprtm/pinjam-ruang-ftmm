@extends('layouts.admin')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h4 class="mb-0"><i class="fas fa-edit me-2 text-success"></i>Edit Aset Fakultas</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.aset-fakultas.update', $asetFakultas->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required" for="kode_barang">Kode Barang</label>
                    <input class="form-control {{ $errors->has('kode_barang') ? 'is-invalid' : '' }}"
                           type="text" name="kode_barang" id="kode_barang"
                           value="{{ old('kode_barang', $asetFakultas->kode_barang) }}" required>
                    @error('kode_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label required" for="nama_barang">Nama Barang</label>
                    <input class="form-control {{ $errors->has('nama_barang') ? 'is-invalid' : '' }}"
                           type="text" name="nama_barang" id="nama_barang"
                           value="{{ old('nama_barang', $asetFakultas->nama_barang) }}" required>
                    @error('nama_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="tahun_aset">Tahun Aset</label>
                    <input class="form-control {{ $errors->has('tahun_aset') ? 'is-invalid' : '' }}"
                           type="number" name="tahun_aset" id="tahun_aset"
                           value="{{ old('tahun_aset', $asetFakultas->tahun_aset) }}" min="1900" max="2100">
                    @error('tahun_aset') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label required" for="anggaran">Anggaran</label>
                    <select class="form-select {{ $errors->has('anggaran') ? 'is-invalid' : '' }}" 
                            name="anggaran" id="anggaran" required>
                        @foreach(\App\Models\AsetFakultas::ANGGARAN_OPTIONS as $k => $v)
                            <option value="{{ $k }}" {{ old('anggaran', 'DAMAS') == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('anggaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label required" for="kondisi">Kondisi</label>
                    <select class="form-select {{ $errors->has('kondisi') ? 'is-invalid' : '' }}"
                            name="kondisi" id="kondisi" required>
                        @foreach($kondisiOptions as $k => $v)
                            <option value="{{ $k }}" {{ old('kondisi', $asetFakultas->kondisi) == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('kondisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="merk">Merk</label>
                    <input class="form-control {{ $errors->has('merk') ? 'is-invalid' : '' }}"
                           type="text" name="merk" id="merk"
                           value="{{ old('merk', $asetFakultas->merk) }}">
                    @error('merk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="ruangan_id">Ruangan</label>
                    <select class="form-select {{ $errors->has('ruangan_id') ? 'is-invalid' : '' }}"
                            name="ruangan_id" id="ruangan_id">
                        <option value="">-- Belum ditentukan --</option>
                        @foreach($ruanganList as $id => $nama)
                            <option value="{{ $id }}" {{ old('ruangan_id', $asetFakultas->ruangan_id) == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('ruangan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="lokasi_text">Lokasi (teks bebas)</label>
                    <input class="form-control {{ $errors->has('lokasi_text') ? 'is-invalid' : '' }}"
                           type="text" name="lokasi_text" id="lokasi_text"
                           value="{{ old('lokasi_text', $asetFakultas->lokasi_text) }}">
                    @error('lokasi_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="deskripsi">Deskripsi</label>
                    <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}"
                              name="deskripsi" id="deskripsi" rows="3">{{ old('deskripsi', $asetFakultas->deskripsi) }}</textarea>
                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('admin.aset-fakultas.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
