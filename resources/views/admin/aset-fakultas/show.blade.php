@extends('layouts.admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-gray-800"><i class="fas fa-box-open me-2 text-primary"></i>Detail Aset Fakultas</h4>
    <div class="d-flex gap-2">
        @can('aset_fakultas_edit')
            <a href="{{ route('admin.aset-fakultas.edit', $asetFakultas->id) }}" class="btn btn-success shadow-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('admin.aset-fakultas.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-info-circle me-2"></i>Informasi Utama</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr>
                            <td width="30%" class="text-muted fw-semibold align-middle">Kode Barang</td>
                            <td class="align-middle">: <code class="fs-6 bg-light px-2 py-1 rounded text-dark border">{{ $asetFakultas->kode_barang }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold align-middle">Nama Barang</td>
                            <td class="align-middle">: <span class="fw-bold fs-6">{{ $asetFakultas->nama_barang }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold align-middle">Merk / Type</td>
                            <td class="align-middle">: {{ $asetFakultas->merk ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold align-middle">Tahun Aset</td>
                            <td class="align-middle">: {{ $asetFakultas->tahun_aset ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold align-middle">Lokasi Asli (Teks Excel)</td>
                            <td class="align-middle">: {{ $asetFakultas->lokasi_text ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr class="my-4">
                
                <div>
                    <label class="text-muted fw-semibold mb-2"><i class="fas fa-align-left me-1"></i> Deskripsi</label>
                    <div class="p-3 bg-light rounded border">
                        {{ $asetFakultas->deskripsi ?? 'Tidak ada deskripsi yang dicatat untuk aset ini.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-tag me-2"></i>Status & Lokasi</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted d-block small fw-semibold mb-2">Kondisi Barang</label>
                    @php
                        $badgeKondisi = match($asetFakultas->kondisi) {
                            'Baik'         => 'success',
                            'Rusak Ringan' => 'warning',
                            'Rusak Berat'  => 'danger',
                            default        => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $badgeKondisi }} fs-6 px-3 py-2 shadow-sm">{{ $asetFakultas->kondisi }}</span>
                </div>

                <div class="mb-4">
                    <label class="text-muted d-block small fw-semibold mb-2">Anggaran</label>
                    <span class="badge bg-primary fs-6 px-3 py-2 shadow-sm">{{ $asetFakultas->anggaran ?? 'DAMAS' }}</span>
                </div>

                <div class="mb-2">
                    <label class="text-muted d-block small fw-semibold mb-2">Ruangan (Penempatan)</label>
                    @if($asetFakultas->ruangan)
                        <span class="badge bg-info text-dark fs-6 px-3 py-2 shadow-sm">
                            <i class="fas fa-door-open me-1"></i> {{ $asetFakultas->ruangan->nama }}
                        </span>
                    @else
                        <span class="badge bg-secondary fs-6 px-3 py-2 shadow-sm">Belum ditautkan ke ruangan</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-history me-2"></i>Riwayat Sistem</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted d-block small fw-semibold mb-1">Tanggal Dibuat</label>
                    <div class="fs-6">
                        <i class="far fa-calendar-plus me-2 text-success"></i> 
                        {{ \Carbon\Carbon::parse($asetFakultas->created_at)->translatedFormat('d F Y - H:i') }}
                    </div>
                </div>
                <div class="mb-0">
                    <label class="text-muted d-block small fw-semibold mb-1">Terakhir Diperbarui</label>
                    <div class="fs-6">
                        <i class="far fa-edit me-2 text-warning"></i> 
                        {{ \Carbon\Carbon::parse($asetFakultas->updated_at)->translatedFormat('d F Y - H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection