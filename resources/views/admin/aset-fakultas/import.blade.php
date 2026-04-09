@extends('layouts.admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-file-import me-2 text-secondary"></i>Import Aset Fakultas dari Excel</h4>
    <a href="{{ route('admin.aset-fakultas.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-3">
    {{-- Upload Form --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Upload File Excel</h6>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.aset-fakultas.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">File Excel (.xlsx / .xls)</label>
                        <input type="file" class="form-control {{ $errors->has('file') ? 'is-invalid' : '' }}"
                               name="file" accept=".xlsx,.xls" required>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Maksimal ukuran file: 10MB</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Panduan Format --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-1 text-info"></i>Format File Excel</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    File Excel harus memiliki <strong>baris header di baris ke-2</strong>
                    (baris pertama adalah judul seperti "Daftar Barang").
                    Kolom yang dibutuhkan:
                </p>
                <table class="table table-sm table-bordered small">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kolom di Header</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><code>Kode Barang</code></td><td>Wajib, unik</td></tr>
                        <tr><td><code>Tahun Asset</code></td><td>Opsional (angka)</td></tr>
                        <tr><td><code>Nama Barang</code></td><td>Wajib</td></tr>
                        <tr><td><code>Kondisi Barang</code></td><td>Baik / Rusak Ringan / Rusak Berat</td></tr>
                        <tr><td><code>Anggaran</code></td><td>Opsional (DAMAS / HIBAH / IKU). Default: DAMAS</td></tr>
                        <tr><td><code>Merk</code></td><td>Opsional</td></tr>
                        <tr><td><code>Deskripsi</code></td><td>Opsional</td></tr>
                        <tr><td><code>Lokasi</code></td><td>Opsional, format: Gedung :: Ruangan</td></tr>
                    </tbody>
                </table>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-lightbulb me-1"></i>
                    Jika <strong>kode_barang</strong> sudah ada di sistem, data akan di-<em>update</em> (bukan duplikat).
                    Kolom <strong>Lokasi</strong> akan otomatis dicocokkan dengan nama ruangan di sistem.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
