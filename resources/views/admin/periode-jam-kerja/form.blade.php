@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">{{ isset($periode) ? 'Edit' : 'Tambah' }} Periode Jam Kerja</h3>
        <a href="{{ route('admin.periode-jam-kerja.index') }}" class="btn btn-secondary shadow-sm">Kembali</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ isset($periode) ? route('admin.periode-jam-kerja.update', $periode->id) : route('admin.periode-jam-kerja.store') }}" method="POST">
                @csrf
                @if(isset($periode)) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Nama Periode</label>
                        <input type="text" name="nama_periode" class="form-control @error('nama_periode') is-invalid @enderror" value="{{ old('nama_periode', $periode->nama_periode ?? '') }}" placeholder="Contoh: Ramadhan 2026" required>
                        @error('nama_periode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', $periode->tanggal_mulai ?? '') }}" required>
                        @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', $periode->tanggal_selesai ?? '') }}" required>
                        @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="mt-2 mb-3">

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Jam Masuk</label>
                        <input type="time" name="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" value="{{ old('jam_masuk', isset($periode) ? \Carbon\Carbon::parse($periode->jam_masuk)->format('H:i') : '08:00') }}" required>
                        @error('jam_masuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Batas Pulang (Senin - Kamis)</label>
                        <input type="time" name="jam_pulang_senin_kamis" class="form-control @error('jam_pulang_senin_kamis') is-invalid @enderror" value="{{ old('jam_pulang_senin_kamis', isset($periode) ? \Carbon\Carbon::parse($periode->jam_pulang_senin_kamis)->format('H:i') : '16:30') }}" required>
                        @error('jam_pulang_senin_kamis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Batas Pulang (Jumat)</label>
                        <input type="time" name="jam_pulang_jumat" class="form-control @error('jam_pulang_jumat') is-invalid @enderror" value="{{ old('jam_pulang_jumat', isset($periode) ? \Carbon\Carbon::parse($periode->jam_pulang_jumat)->format('H:i') : '17:00') }}" required>
                        @error('jam_pulang_jumat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection