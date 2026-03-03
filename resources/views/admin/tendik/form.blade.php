@extends('layouts.admin')
@section('content')

<div class="container-fluid p-0">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">{{ isset($tendik) ? 'Edit Data Tendik' : 'Tambah Tendik Baru' }}</h3>
        <a href="{{ route('admin.tendik.index') }}" class="btn btn-secondary shadow-sm">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger shadow-sm"><ul>@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div>
    @endif

    <form action="{{ isset($tendik) ? route('admin.tendik.update', $tendik->id) : route('admin.tendik.store') }}" method="POST">
        @csrf @if(isset($tendik)) @method('PUT') @endif

        <div class="row">
            {{-- KOLOM KIRI: AKUN & PRIBADI --}}
            <div class="col-xl-6">
                <div class="card form-card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold text-primary">Data Akun & Identitas Pribadi</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap (Sesuai KTP) <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $tendik->tendikDetail->nama_lengkap ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Panggilan (Tampilan UI) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $tendik->name ?? '') }}" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $tendik->email ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Password {{ isset($tendik) ? '(Kosongkan jika tdk diubah)' : '*' }}</label>
                                <input type="password" name="password" class="form-control" {{ isset($tendik) ? '' : 'required' }}>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">NIK KTP</label>
                            <input type="text" name="nik_ktp" class="form-control" maxlength="16" value="{{ old('nik_ktp', $tendik->tendikDetail->nik_ktp ?? '') }}">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $tendik->tendikDetail->tempat_lahir ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $tendik->tendikDetail->tanggal_lahir ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin', $tendik->tendikDetail->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $tendik->tendikDetail->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nomor Ponsel (WA)</label>
                                <input type="text" name="no_ponsel" class="form-control" value="{{ old('no_ponsel', $tendik->tendikDetail->no_ponsel ?? '') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $tendik->tendikDetail->alamat ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: KEPEGAWAIAN --}}
            <div class="col-xl-6">
                <div class="card form-card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold text-success">Detail Kepegawaian</h6></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">NIP <span class="text-danger">*</span></label>
                                <input type="text" name="nip" class="form-control border-primary" value="{{ old('nip', $tendik->nip ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NIK Pegawai</label>
                                <input type="text" name="nik" class="form-control" value="{{ old('nik', $tendik->tendikDetail->nik ?? '') }}" placeholder="Jika berbeda dg NIP">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Jabatan</label>
                                <input type="text" name="nama_jabatan" class="form-control" value="{{ old('nama_jabatan', $tendik->tendikDetail->nama_jabatan ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sub Bagian / Unit</label>
                                <input type="text" name="sub_bagian" class="form-control" value="{{ old('sub_bagian', $tendik->tendikDetail->sub_bagian ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Kepegawaian</label>
                                <input type="text" name="status_kepegawaian" class="form-control" placeholder="PNS / Tetap Non-PNS / PTT" value="{{ old('status_kepegawaian', $tendik->tendikDetail->status_kepegawaian ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pangkat / Golongan</label>
                                <input type="text" name="pangkat_golongan" class="form-control" placeholder="cth: Penata Muda - III/a" value="{{ old('pangkat_golongan', $tendik->tendikDetail->pangkat_golongan ?? '') }}">
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">TMT (Terhitung Mulai Tgl)</label>
                                <input type="date" name="tmt" class="form-control" value="{{ old('tmt', $tendik->tendikDetail->tmt ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NPWP</label>
                                <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $tendik->tendikDetail->npwp ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-success fw-bold px-5 shadow-sm py-2">
                        <i class="fas fa-save me-2"></i> Simpan Data Tendik
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection