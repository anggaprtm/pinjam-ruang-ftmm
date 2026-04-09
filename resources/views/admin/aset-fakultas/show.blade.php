@extends('layouts.admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Detail Aset Fakultas</h4>
    <div class="d-flex gap-2">
        @can('aset_fakultas_edit')
            <a href="{{ route('admin.aset-fakultas.edit', $asetFakultas->id) }}" class="btn btn-sm btn-success">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('admin.aset-fakultas.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="text-muted small">Kode Barang</label>
                <div class="fw-semibold"><code>{{ $asetFakultas->kode_barang }}</code></div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Nama Barang</label>
                <div class="fw-semibold">{{ $asetFakultas->nama_barang }}</div>
            </div>
            <div class="col-md-4">
                <label class="text-muted small">Tahun Aset</label>
                <div>{{ $asetFakultas->tahun_aset ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <label class="text-muted small">Kondisi</label>
                <div>
                    @php
                        $badge = match($asetFakultas->kondisi) {
                            'Baik'         => 'success',
                            'Rusak Ringan' => 'warning',
                            'Rusak Berat'  => 'danger',
                            default        => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $badge }} fs-6">{{ $asetFakultas->kondisi }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="text-muted small">Merk</label>
                <div>{{ $asetFakultas->merk ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Ruangan (Sistem)</label>
                <div>
                    @if($asetFakultas->ruangan)
                        <span class="badge bg-info text-dark fs-6">
                            <i class="fas fa-door-open me-1"></i>{{ $asetFakultas->ruangan->nama }}
                        </span>
                    @else
                        <span class="text-muted">Belum ditautkan ke ruangan</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Lokasi (dari dokumen asli)</label>
                <div>{{ $asetFakultas->lokasi_text ?? '-' }}</div>
            </div>
            <div class="col-12">
                <label class="text-muted small">Deskripsi</label>
                <div class="border rounded p-3 bg-light">{{ $asetFakultas->deskripsi ?? 'Tidak ada deskripsi.' }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Dibuat</label>
                <div>{{ $asetFakultas->created_at }}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Terakhir Diperbarui</label>
                <div>{{ $asetFakultas->updated_at }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
