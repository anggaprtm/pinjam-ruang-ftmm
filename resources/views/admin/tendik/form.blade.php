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
        <h4 class="mb-0 fw-bold"><i class="fas fa-user-cog me-2 text-success"></i>{{ isset($tendik) ? 'Edit Data Tendik' : 'Tambah Tendik Baru' }}</h4>
        <small class="text-muted">Lengkapi profil identitas, akun, dan data kepegawaian Tenaga Kependidikan.</small>
    </div>
    <a href="{{ route('admin.tendik.index') }}" class="btn btn-sm btn-secondary shadow-sm">
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

<form action="{{ isset($tendik) ? route('admin.tendik.update', $tendik->id) : route('admin.tendik.store') }}" method="POST">
    @csrf
    @if(isset($tendik)) @method('PUT') @endif

    <div class="row g-4">
        {{-- BAGIAN KIRI: AKUN UTAMA & IDENTITAS PRIBADI --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4 form-section text-success">AKUN & IDENTITAS PRIBADI</h6>

                    <div class="mb-3">
                        <label class="form-label required fw-semibold small">Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" name="nama_lengkap" class="form-control" 
                               placeholder="Contoh: Budi Santoso, S.Kom."
                               value="{{ old('nama_lengkap', $tendik->tendikDetail->nama_lengkap ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required fw-semibold small">Nama Panggilan / Nama UI</label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="Contoh: Budi"
                               value="{{ old('name', $tendik->name ?? '') }}" required>
                        <small class="text-muted">Nama ini akan tampil di sudut kanan atas aplikasi.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold small">Email Utama</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email', $tendik->email ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small {{ isset($tendik) ? '' : 'required' }}">Password Login</label>
                            <input type="password" name="password" class="form-control" 
                                   {{ isset($tendik) ? '' : 'required' }} placeholder="{{ isset($tendik) ? 'Kosongkan jika tdk diubah' : '' }}">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold small text-primary">NIP / NIPTT / NIK UNAIR</label>
                            <input type="text" name="nip" class="form-control border-primary" 
                                   value="{{ old('nip', $tendik->nip ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NIK KTP</label>
                            <input type="text" name="nik_ktp" class="form-control" maxlength="16"
                                   value="{{ old('nik_ktp', $tendik->tendikDetail->nik_ktp ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" 
                                   value="{{ old('tempat_lahir', $tendik->tendikDetail->tempat_lahir ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" 
                                   value="{{ old('tanggal_lahir', $tendik->tendikDetail->tanggal_lahir ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select my-select2" data-placeholder="-- Pilih --">
                                <option value=""></option>
                                <option value="L" {{ old('jenis_kelamin', $tendik->tendikDetail->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $tendik->tendikDetail->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Ponsel (WhatsApp)</label>
                            <input type="text" name="no_ponsel" class="form-control" 
                                   value="{{ old('no_ponsel', $tendik->tendikDetail->no_ponsel ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2" placeholder="Jl. Raya Kampus C...">{{ old('alamat', $tendik->tendikDetail->alamat ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN KANAN: DATA KEPEGAWAIAN TENDIK --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-4 form-section text-success">DETAIL KEPEGAWAIAN</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Jabatan</label>
                            <input type="text" name="nama_jabatan" class="form-control" 
                                   placeholder="cth: Pengelola IT / Administrasi Akademik"
                                   value="{{ old('nama_jabatan', $tendik->tendikDetail->nama_jabatan ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Sub Bagian / Unit</label>
                            <input type="text" name="sub_bagian" class="form-control" 
                                   placeholder="cth: Akademik / Sarpras"
                                   value="{{ old('sub_bagian', $tendik->tendikDetail->sub_bagian ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status Kepegawaian</label>
                            <input type="text" name="status_kepegawaian" class="form-control" 
                                   placeholder="PNS / Tetap Non-PNS / PTT" 
                                   value="{{ old('status_kepegawaian', $tendik->tendikDetail->status_kepegawaian ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Pangkat / Golongan</label>
                            <input type="text" name="pangkat_golongan" class="form-control" 
                                   placeholder="cth: Penata Muda - III/a" 
                                   value="{{ old('pangkat_golongan', $tendik->tendikDetail->pangkat_golongan ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-warning small"><i class="fas fa-toggle-on me-1"></i> Status Keaktifan</label>
                        <select name="status_keaktifan" class="form-select my-select2 border-warning bg-light" data-placeholder="-- Pilih Status --">
                            <option value=""></option>
                            @php $aktifStat = old('status_keaktifan', $tendik->tendikDetail->status_keaktifan ?? 'Aktif'); @endphp
                            <option value="Aktif" {{ $aktifStat == 'Aktif' ? 'selected' : '' }}>Aktif (Presensi Berjalan)</option>
                            <option value="Cuti" {{ $aktifStat == 'Cuti' ? 'selected' : '' }}>Sedang Cuti</option>
                            <option value="Tugas Belajar" {{ $aktifStat == 'Tugas Belajar' ? 'selected' : '' }}>Tugas Belajar</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">TMT (Terhitung Mulai Tanggal)</label>
                            <input type="date" name="tmt" class="form-control" 
                                   value="{{ old('tmt', $tendik->tendikDetail->tmt ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NPWP</label>
                            <input type="text" name="npwp" class="form-control" 
                                   value="{{ old('npwp', $tendik->tendikDetail->npwp ?? '') }}">
                        </div>
                    </div>

                    {{-- TOMBOL SUBMIT DI KANAN BAWAH --}}
                    <div class="text-end mt-5">
                        <button type="submit" class="btn btn-success fw-bold px-5 shadow-sm py-2">
                            <i class="fas fa-save me-2"></i> Simpan Data Tendik
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
        minimumResultsForSearch: Infinity // Menghilangkan search box untuk pilihan sedikit
    });

    // 2. Teks Tanggal Otomatis
    function formatTanggalIndo(dateString) {
        if (!dateString) return '';
        var parts = dateString.split('-');
        var date = new Date(parts[0], parts[1] - 1, parts[2]); 
        
        return new Intl.DateTimeFormat('id-ID', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        }).format(date);
    }

    // Targetkan input tanggal lahir dan TMT
    var dateInputs = ['tanggal_lahir', 'tmt'];
    
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