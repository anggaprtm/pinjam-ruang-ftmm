@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">{{ isset($dosen) ? 'Edit Data Dosen' : 'Tambah Dosen Baru' }}</h3>
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary shadow-sm">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($dosen) ? route('admin.dosen.update', $dosen->id) : route('admin.dosen.store') }}" method="POST">
        @csrf
        @if(isset($dosen)) @method('PUT') @endif

        <div class="row">
            {{-- BAGIAN KIRI: AKUN UTAMA & IDENTITAS --}}
            <div class="col-xl-6">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary">Informasi Akun & Identitas Utama</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap & Gelar Akademik <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap_gelar" class="form-control" value="{{ old('nama_lengkap_gelar', $dosen->dosenDetail->nama_lengkap_gelar ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Singkat (Panggilan/UI) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $dosen->name ?? '') }}" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $dosen->email ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Password {{ isset($dosen) ? '(Kosongkan jika tdk diubah)' : '*' }}</label>
                                <input type="password" name="password" class="form-control" {{ isset($dosen) ? '' : 'required' }}>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NIP / NIPTT <span class="text-danger">*</span></label>
                                <input type="text" name="nip" class="form-control" value="{{ old('nip', $dosen->nip ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NIK KTP</label>
                                <input type="text" name="nik" class="form-control" value="{{ old('nik', $dosen->dosenDetail->nik ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $dosen->dosenDetail->tempat_lahir ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $dosen->dosenDetail->tanggal_lahir ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin', $dosen->dosenDetail->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $dosen->dosenDetail->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nomor Ponsel (WhatsApp)</label>
                                <input type="text" name="no_ponsel" class="form-control" value="{{ old('no_ponsel', $dosen->dosenDetail->no_ponsel ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN KANAN: DATA KEPEGAWAIAN DOSEN --}}
            <div class="col-xl-6">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-success">Detail Akademik & Kepegawaian</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NIDN / NIDK</label>
                                <input type="text" name="nidn" class="form-control" value="{{ old('nidn', $dosen->dosenDetail->nidn ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NUPTK</label>
                                <input type="text" name="nuptk" class="form-control" value="{{ old('nuptk', $dosen->dosenDetail->nuptk ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Kepegawaian</label>
                                <input type="text" name="status_kepegawaian" class="form-control" placeholder="PNS / Tetap Non-PNS" value="{{ old('status_kepegawaian', $dosen->dosenDetail->status_kepegawaian ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Status Keaktifan</label>
                                <select name="status_keaktifan" class="form-select border-primary bg-light">
                                    @php $aktifStat = old('status_keaktifan', $dosen->dosenDetail->status_keaktifan ?? 'Aktif'); @endphp
                                    <option value="Aktif" {{ $aktifStat == 'Aktif' ? 'selected' : '' }}>Aktif Mengajar</option>
                                    <option value="Tugas Belajar" {{ $aktifStat == 'Tugas Belajar' ? 'selected' : '' }}>Tugas Belajar</option>
                                    <option value="Izin" {{ $aktifStat == 'Izin' ? 'selected' : '' }}>Cuti / Izin</option>
                                    <option value="Pensiun" {{ $aktifStat == 'Pensiun' ? 'selected' : '' }}>Pensiun</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Homebase Program Studi</label>
                            <input type="text" name="homebase_prodi" class="form-control" value="{{ old('homebase_prodi', $dosen->dosenDetail->homebase_prodi ?? '') }}">
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pangkat / Golongan</label>
                                <input type="text" name="pangkat_golongan" class="form-control" placeholder="cth: III/b" value="{{ old('pangkat_golongan', $dosen->dosenDetail->pangkat_golongan ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jabatan Fungsional</label>
                                <select name="jabatan_fungsional" class="form-select">
                                    @php $jabfung = old('jabatan_fungsional', $dosen->dosenDetail->jabatan_fungsional ?? ''); @endphp
                                    <option value="">-- Pilih --</option>
                                    <option value="Tenaga Pengajar" {{ $jabfung == 'Tenaga Pengajar' ? 'selected' : '' }}>Tenaga Pengajar</option>
                                    <option value="Asisten Ahli" {{ $jabfung == 'Asisten Ahli' ? 'selected' : '' }}>Asisten Ahli</option>
                                    <option value="Lektor" {{ $jabfung == 'Lektor' ? 'selected' : '' }}>Lektor</option>
                                    <option value="Lektor Kepala" {{ $jabfung == 'Lektor Kepala' ? 'selected' : '' }}>Lektor Kepala</option>
                                    <option value="Guru Besar" {{ $jabfung == 'Guru Besar' ? 'selected' : '' }}>Guru Besar</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Mulai Jadi Dosen</label>
                                <input type="date" name="tgl_mulai_dosen" class="form-control" value="{{ old('tgl_mulai_dosen', $dosen->dosenDetail->tgl_mulai_dosen ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NPWP</label>
                                <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $dosen->dosenDetail->npwp ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- TOMBOL SUBMIT DI KANAN BAWAH --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-primary fw-bold px-5 shadow-sm py-2">
                        <i class="fas fa-save me-2"></i> Simpan Data Dosen
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection