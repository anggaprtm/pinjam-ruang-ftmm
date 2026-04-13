@extends('layouts.admin')

@section('styles')
<style>
    /* Helper text untuk input wajib */
    label.required::after { content: " *"; color: red; }
    /* Style judul section form */
    .form-section { border-left: 3px solid #198754; padding-left: 12px; margin-bottom: 8px; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-user-tie me-2 text-success"></i>{{ isset($dosen) ? 'Edit Data Dosen' : 'Tambah Dosen Baru' }}</h4>
        <small class="text-muted">Lengkapi profil identitas, akun, dan data kepegawaian dosen.</small>
    </div>
    <a href="{{ route('admin.dosen.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger shadow-sm">
        <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Terdapat kesalahan:</div>
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

    <div class="row g-4">
        {{-- BAGIAN KIRI: AKUN UTAMA & IDENTITAS --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4 form-section text-success">AKUN & IDENTITAS UTAMA</h6>

                    <div class="mb-3">
                        <label class="form-label required fw-semibold small">Nama Lengkap & Gelar Akademik</label>
                        <input type="text" name="nama_lengkap_gelar" class="form-control" 
                               placeholder="Contoh: Prof. Dr. Budi Santoso, S.T., M.T."
                               value="{{ old('nama_lengkap_gelar', $dosen->dosenDetail->nama_lengkap_gelar ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required fw-semibold small">Nama Panggilan / Nama UI</label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="Contoh: Budi Santoso"
                               value="{{ old('name', $dosen->name ?? '') }}" required>
                        <small class="text-muted">Nama ini akan tampil di sudut kanan atas aplikasi.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold small">Email Utama</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email', $dosen->email ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small {{ isset($dosen) ? '' : 'required' }}">Password Login</label>
                            <input type="password" name="password" class="form-control" 
                                   {{ isset($dosen) ? '' : 'required' }} placeholder="{{ isset($dosen) ? 'Kosongkan jika tdk diubah' : '' }}">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold small">NIP / NIPTT</label>
                            <input type="text" name="nip" class="form-control" 
                                   value="{{ old('nip', $dosen->nip ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NIK KTP</label>
                            <input type="text" name="nik" class="form-control" 
                                   value="{{ old('nik', $dosen->dosenDetail->nik ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" 
                                   value="{{ old('tempat_lahir', $dosen->dosenDetail->tempat_lahir ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" 
                                   value="{{ old('tanggal_lahir', $dosen->dosenDetail->tanggal_lahir ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select my-select2" data-placeholder="-- Pilih --">
                                <option value=""></option>
                                <option value="L" {{ old('jenis_kelamin', $dosen->dosenDetail->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $dosen->dosenDetail->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Ponsel (WhatsApp)</label>
                            <input type="text" name="no_ponsel" class="form-control" 
                                   value="{{ old('no_ponsel', $dosen->dosenDetail->no_ponsel ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN KANAN: DATA KEPEGAWAIAN DOSEN --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4 form-section text-success">AKADEMIK & KEPEGAWAIAN</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NIDN / NIDK</label>
                            <input type="text" name="nidn" class="form-control" 
                                   value="{{ old('nidn', $dosen->dosenDetail->nidn ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NUPTK</label>
                            <input type="text" name="nuptk" class="form-control" 
                                   value="{{ old('nuptk', $dosen->dosenDetail->nuptk ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status Kepegawaian</label>
                            <input type="text" name="status_kepegawaian" class="form-control" 
                                   placeholder="PNS / Tetap Non-PNS" 
                                   value="{{ old('status_kepegawaian', $dosen->dosenDetail->status_kepegawaian ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary small">Status Keaktifan</label>
                            <select name="status_keaktifan" class="form-select my-select2 border-primary bg-light" data-placeholder="-- Pilih Status --">
                                <option value=""></option>
                                @php $aktifStat = old('status_keaktifan', $dosen->dosenDetail->status_keaktifan ?? 'Aktif'); @endphp
                                <option value="Aktif" {{ $aktifStat == 'Aktif' ? 'selected' : '' }}>Aktif Mengajar</option>
                                <option value="Tugas Belajar" {{ $aktifStat == 'Tugas Belajar' ? 'selected' : '' }}>Tugas Belajar</option>
                                <option value="Izin" {{ $aktifStat == 'Izin' ? 'selected' : '' }}>Cuti / Izin</option>
                                <option value="Pensiun" {{ $aktifStat == 'Pensiun' ? 'selected' : '' }}>Pensiun</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Homebase Program Studi</label>
                        <select name="homebase_prodi" class="form-select my-select2-tags" data-placeholder="-- Pilih atau ketik Prodi --">
                            <option value=""></option>
                            @php 
                                $prodiPresets = ['S1 Rekayasa Nanoteknologi', 'S1 Teknik Robotika dan Kecerdasan Buatan', 'S1 Teknologi Sains Data', 'S1 Teknik Industri', 'S1 Teknik Elektro'];
                                $currentProdi = old('homebase_prodi', $dosen->dosenDetail->homebase_prodi ?? '');
                            @endphp
                            @foreach($prodiPresets as $prodi)
                                <option value="{{ $prodi }}" {{ $currentProdi == $prodi ? 'selected' : '' }}>{{ $prodi }}</option>
                            @endforeach
                            @if($currentProdi && !in_array($currentProdi, $prodiPresets))
                                <option value="{{ $currentProdi }}" selected>{{ $currentProdi }}</option>
                            @endif
                        </select>
                    </div>

                    <hr class="my-4">

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Pangkat / Golongan</label>
                            <input type="text" name="pangkat_golongan" class="form-control" 
                                   placeholder="cth: III/b" 
                                   value="{{ old('pangkat_golongan', $dosen->dosenDetail->pangkat_golongan ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jabatan Fungsional</label>
                            <select name="jabatan_fungsional" class="form-select my-select2" data-placeholder="-- Pilih --">
                                <option value=""></option>
                                @php $jabfung = old('jabatan_fungsional', $dosen->dosenDetail->jabatan_fungsional ?? ''); @endphp
                                <option value="Tenaga Pengajar" {{ $jabfung == 'Tenaga Pengajar' ? 'selected' : '' }}>Tenaga Pengajar</option>
                                <option value="Asisten Ahli" {{ $jabfung == 'Asisten Ahli' ? 'selected' : '' }}>Asisten Ahli</option>
                                <option value="Lektor" {{ $jabfung == 'Lektor' ? 'selected' : '' }}>Lektor</option>
                                <option value="Lektor Kepala" {{ $jabfung == 'Lektor Kepala' ? 'selected' : '' }}>Lektor Kepala</option>
                                <option value="Guru Besar" {{ $jabfung == 'Guru Besar' ? 'selected' : '' }}>Guru Besar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-primary">Jabatan Struktural</label>
                            <select name="jabatan_struktural" class="form-select my-select2-tags border-primary" data-placeholder="-- Kosongkan jika tidak ada --">
                                <option value=""></option>
                                @php 
                                    $jabstrukPresets = ['Dekan', 'Wakil Dekan I', 'Wakil Dekan II', 'Wakil Dekan III', 'Ketua Departemen Teknik', 'Sekretaris Departemen Teknik', 
                                    'Koordinator Program Studi S1 Teknologi Sains Data', 'Koordinator Program Studi S1 Teknik Elektro', 'Koordinator Program Studi S1 Teknik Industri', 
                                    'Koordinator Program Studi S1 Teknik Robotika dan Kecerdasan Buatan', 'Koordinator Program Studi S1 Rekayasa Nanoteknologi'];
                                    $currentJabstruk = old('jabatan_struktural', $dosen->dosenDetail->jabatan_struktural ?? '');
                                @endphp
                                @foreach($jabstrukPresets as $js)
                                    <option value="{{ $js }}" {{ $currentJabstruk == $js ? 'selected' : '' }}>{{ $js }}</option>
                                @endforeach
                                @if($currentJabstruk && !in_array($currentJabstruk, $jabstrukPresets))
                                    <option value="{{ $currentJabstruk }}" selected>{{ $currentJabstruk }}</option>
                                @endif
                            </select>
                            <small class="text-muted" style="font-size:0.7rem;">Otomatis di Surat Tugas</small>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Mulai Jadi Dosen</label>
                            <input type="date" name="tgl_mulai_dosen" class="form-control" 
                                   value="{{ old('tgl_mulai_dosen', $dosen->dosenDetail->tgl_mulai_dosen ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NPWP</label>
                            <input type="text" name="npwp" class="form-control" 
                                   value="{{ old('npwp', $dosen->dosenDetail->npwp ?? '') }}">
                        </div>
                    </div>

                    {{-- TOMBOL SUBMIT DI KANAN BAWAH --}}
                    <div class="text-end mt-5">
                        <button type="submit" class="btn btn-success fw-bold px-5 shadow-sm py-2">
                            <i class="fas fa-save me-2"></i> Simpan Data Dosen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
@parent
<script>
$(document).ready(function() {
    // 1. Init Select2 Standar
    $('.my-select2').select2({
        theme: 'bootstrap-5', // Ambil tema bootstrap-5 yang sudah ada di admin.blade.php
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 10
    });

    // 2. Init Select2 khusus Tags (bisa diketik)
    $('.my-select2-tags').select2({
        theme: 'bootstrap-5',
        width: '100%',
        tags: true,
        allowClear: true
    });

    // 3. Teks Tanggal Otomatis
    function formatTanggalIndo(dateString) {
        if (!dateString) return '';
        var parts = dateString.split('-');
        var date = new Date(parts[0], parts[1] - 1, parts[2]); 
        
        return new Intl.DateTimeFormat('id-ID', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        }).format(date);
    }

    var dateInputs = ['tanggal_lahir', 'tgl_mulai_dosen'];
    
    dateInputs.forEach(function(name) {
        var input = document.querySelector('input[name="' + name + '"]');
        if (!input) return;

        var helperText = document.createElement('small');
        helperText.className = 'text-primary d-block mt-1';
        helperText.style.fontWeight = '500';
        input.parentNode.insertBefore(helperText, input.nextSibling);

        function updateHelper() {
            if (input.value) {
                helperText.innerHTML = '<i class="fas fa-calendar-check me-1"></i> Tertulis: ' + formatTanggalIndo(input.value);
            } else {
                helperText.innerHTML = '';
            }
        }

        input.addEventListener('change', updateHelper);
        input.addEventListener('input', updateHelper);
        updateHelper(); 
    });
});
</script>
@endsection