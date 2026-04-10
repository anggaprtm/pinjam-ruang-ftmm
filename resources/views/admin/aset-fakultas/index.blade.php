@extends('layouts.admin')
@section('content')

{{-- Tambahan CSS khusus halaman ini --}}
<style>
    /* Menyembunyikan teks pagination bahasa Inggris bawaan Laravel */
    .pagination-wrapper p.small.text-muted { display: none !important; }
    
    /* Styling untuk header tabel yang bisa di-sort */
    .sortable-header { color: #333; text-decoration: none; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s; }
    .sortable-header:hover { color: #0d6efd; text-decoration: none; }
    .sortable-icon { font-size: 0.8rem; opacity: 0.4; }
    .sortable-header.active .sortable-icon { opacity: 1; color: #0d6efd; }
    
    /* Styling tabel agar lebih modern mirip ruangan */
    .table-modern th { background-color: #f8f9fa; font-weight: 600; padding: 12px 15px; border-bottom: 2px solid #dee2e6; }
    .table-modern td { padding: 12px 15px; vertical-align: middle; }
</style>

{{-- Helper Fungsi Sort (Biar kode HTML gak berantakan) --}}
@php
    function sortUrl($field) {
        $order = (request('sort') === $field && request('order') === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $field, 'order' => $order]);
    }
    function sortClass($field) {
        return request('sort') === $field ? 'active' : '';
    }
    function sortIcon($field) {
        if (request('sort') === $field) {
            return request('order') === 'desc' ? 'fa-sort-down' : 'fa-sort-up';
        }
        return 'fa-sort';
    }
@endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="font-weight-bold text-nowrap"><i class="fas fa-boxes me-2"></i>Aset Fakultas</h3>
    <div class="d-flex gap-2 flex-wrap">
        @can('aset_fakultas_create')
            <a class="btn btn-secondary shadow-sm" href="{{ route('admin.aset-fakultas.import.form') }}">
                <i class="fas fa-file-import me-1"></i> Import Excel
            </a>
            <a class="btn btn-success shadow-sm" href="{{ route('admin.aset-fakultas.create') }}">
                <i class="fas fa-plus-circle me-1"></i> Tambah Aset
            </a>
        @endcan
        @can('aset_fakultas_access')
            <button class="btn btn-warning shadow-sm" id="btnOpenMove">
                <i class="fas fa-people-carry me-1"></i> Pindah Ruang
            </button>
            
            <button class="btn btn-danger shadow-sm" id="btnOpenExport">
                <i class="fas fa-file-pdf me-1"></i> Export DIR (PDF/ZIP)
            </button>
        @endcan
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-primary">{{ number_format($stats['total']) }}</div>
                <div class="small text-muted">Total Aset</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['baik']) }}</div>
                <div class="small text-muted">Kondisi Baik</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-warning">{{ number_format($stats['rusak_ringan']) }}</div>
                <div class="small text-muted">Rusak Ringan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-danger">{{ number_format($stats['rusak_berat']) }}</div>
                <div class="small text-muted">Rusak Berat</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.aset-fakultas.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1 fw-semibold">Cari Nama / Kode / Merk</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ketik untuk cari..." value="{{ $filterSearch }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1 fw-semibold">Ruangan</label>
                <select name="ruangan_id" class="form-select form-select-sm select2">
                    <option value="">-- Semua Ruangan --</option>
                    @foreach($ruanganList as $r)
                        <option value="{{ $r->id }}" {{ $filterRuangan == $r->id ? 'selected' : '' }}>{{ $r->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1 fw-semibold">Kondisi</label>
                <select name="kondisi" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($kondisiOptions as $k => $v)
                        <option value="{{ $k }}" {{ $filterKondisi == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.aset-fakultas.index') }}" class="btn btn-sm btn-outline-secondary w-100 shadow-sm">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" width="40"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                        
                        <th width="15%">
                            <a href="{{ sortUrl('kode_barang') }}" class="sortable-header {{ sortClass('kode_barang') }}">
                                <span><i class="fas fa-barcode me-1 text-muted"></i> Kode Barang</span>
                                <i class="fas {{ sortIcon('kode_barang') }} sortable-icon"></i>
                            </a>
                        </th>
                        
                        <th width="25%">
                            <a href="{{ sortUrl('nama_barang') }}" class="sortable-header {{ sortClass('nama_barang') }}">
                                <span><i class="fas fa-box-open me-1 text-muted"></i> Nama Barang</span>
                                <i class="fas {{ sortIcon('nama_barang') }} sortable-icon"></i>
                            </a>
                        </th>
                        
                        <th width="10%" class="text-center">
                            <a href="{{ sortUrl('tahun_aset') }}" class="sortable-header justify-content-center {{ sortClass('tahun_aset') }}">
                                <span><i class="fas fa-calendar-alt me-1 text-muted"></i> Tahun</span>
                                <i class="fas {{ sortIcon('tahun_aset') }} sortable-icon ms-2"></i>
                            </a>
                        </th>
                        
                        <th width="10%" class="text-center">
                            <a href="{{ sortUrl('kondisi') }}" class="sortable-header justify-content-center {{ sortClass('kondisi') }}">
                                <span><i class="fas fa-heartbeat me-1 text-muted"></i> Kondisi</span>
                                <i class="fas {{ sortIcon('kondisi') }} sortable-icon ms-2"></i>
                            </a>
                        </th>
                        
                        <th width="15%">
                            <a href="{{ sortUrl('merk') }}" class="sortable-header {{ sortClass('merk') }}">
                                <span><i class="fas fa-tag me-1 text-muted"></i> Merk</span>
                                <i class="fas {{ sortIcon('merk') }} sortable-icon"></i>
                            </a>
                        </th>
                        
                        <th width="15%"><i class="fas fa-map-marker-alt me-1 text-muted"></i> Lokasi / Ruang</th>
                        <th width="10%" class="text-center"><i class="fas fa-cogs me-1 text-muted"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asets as $item)
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input row-check" value="{{ $item->id }}">
                            </td>
                            <td><code class="fs-6 bg-light border rounded px-2 py-1 text-dark">{{ $item->kode_barang }}</code></td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->nama_barang }}</div>
                                @if($item->deskripsi)
                                    <div class="text-muted small" style="font-size: 0.8rem;">{{ Str::limit($item->deskripsi, 50) }}</div>
                                @endif
                            </td>
                            <td class="text-center fw-semibold">{{ $item->tahun_aset ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $badge = match($item->kondisi) {
                                        'Baik'         => 'success',
                                        'Rusak Ringan' => 'warning',
                                        'Rusak Berat'  => 'danger',
                                        default        => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }} px-2 py-1 shadow-sm">{{ $item->kondisi }}</span>
                            </td>
                            <td class="fst-italic">{{ $item->merk ?? '-' }}</td>
                            <td>
                                @if($item->ruangan)
                                    <span class="badge bg-info text-white shadow-sm">
                                        <i class="fas fa-door-open me-1"></i>{{ $item->ruangan->nama }}
                                    </span>
                                @else
                                    <span class="text-muted small"><i class="fas fa-map-pin me-1 opacity-50"></i> {{ Str::limit($item->lokasi_text, 30) ?? 'Belum ditentukan' }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    @can('aset_fakultas_show')
                                        <a class="btn btn-sm btn-info" href="{{ route('admin.aset-fakultas.show', $item->id) }}" title="Detail">
                                            <i class="fas fa-eye text-white"></i>
                                        </a>
                                    @endcan
                                    @can('aset_fakultas_edit')
                                        <a class="btn btn-sm btn-success" href="{{ route('admin.aset-fakultas.edit', $item->id) }}" title="Edit">
                                            <i class="fas fa-edit text-white"></i>
                                        </a>
                                    @endcan
                                    @can('aset_fakultas_delete')
                                        <form action="{{ route('admin.aset-fakultas.destroy', $item->id) }}" method="POST"
                                              onsubmit="return confirm('Hapus aset ini secara permanen?');" style="display:inline-block; margin:0;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger border-start-0" title="Hapus" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                <i class="fas fa-trash text-white"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-box-open fa-3x mb-3 d-block opacity-50"></i>
                                <h5>Belum ada data aset</h5>
                                <p class="small mb-0">Data yang dicari tidak ditemukan atau belum ada data yang ditambahkan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($asets->hasPages())
            <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-bottom">
                <div class="text-muted small fw-semibold">
                    <i class="fas fa-list-ol me-1"></i> Menampilkan <span class="text-dark">{{ $asets->firstItem() }}</span> – <span class="text-dark">{{ $asets->lastItem() }}</span> dari total <span class="text-dark">{{ number_format($asets->total()) }}</span> aset
                </div>
                <div class="pagination-wrapper m-0">
                    {{ $asets->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- MODAL EXPORT DIR (Biarkan sama persis seperti aslinya) --}}
<div class="modal fade" id="modalExportDir" tabindex="-1" aria-labelledby="modalExportDirLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalExportDirLabel">
                    <i class="fas fa-file-pdf me-2"></i>Export Daftar Inventaris Ruang (DIR)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <p class="text-muted small mb-3">
                    Pilih satu atau beberapa ruangan. Jika hanya satu ruangan dipilih, hasilnya langsung <strong>.pdf</strong>.
                    Jika lebih dari satu, semua dikemas dalam <strong>.zip</strong> (satu PDF per ruangan).
                </p>

                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary bg-white shadow-sm" id="btnSelectAll">
                        <i class="fas fa-check-double me-1"></i>Pilih Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary bg-white shadow-sm" id="btnClearAll">
                        <i class="fas fa-times me-1"></i>Hapus Pilihan
                    </button>
                    <span class="ms-auto badge bg-secondary align-self-center shadow-sm" id="selectedCount">0 dipilih</span>
                </div>

                <div class="row g-2" id="ruanganCheckList">
                    @foreach($ruanganList as $r)
                        @php $jmlAset = $asetPerRuangan[$r->id] ?? 0; @endphp
                        <div class="col-12 col-md-6">
                            <label class="d-flex align-items-center border bg-white rounded shadow-sm px-3 py-2 gap-2 {{ $jmlAset === 0 ? 'opacity-50' : '' }}"
                                   for="ruangan_exp_{{ $r->id }}"
                                   style="cursor:pointer; transition: all .15s;">
                                <input class="form-check-input ruangan-check flex-shrink-0 mt-0"
                                       type="checkbox"
                                       name="ruangan_ids[]"
                                       value="{{ $r->id }}"
                                       id="ruangan_exp_{{ $r->id }}"
                                       {{ $jmlAset === 0 ? 'disabled' : '' }}>
                                <span class="flex-grow-1 small fw-semibold">{{ $r->nama }}</span>
                                <span class="badge {{ $jmlAset > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $jmlAset }} aset
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger shadow-sm" id="btnExportDir" disabled>
                    <i class="fas fa-download me-1"></i>
                    <span id="btnExportLabel">Export PDF</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMoveAset" tabindex="-1" aria-labelledby="modalMoveAsetLabel">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.aset-fakultas.mass-move') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark" id="modalMoveAsetLabel">
                        <i class="fas fa-people-carry me-2"></i>Pindah Ruangan Massal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i> Anda akan memindahkan <strong id="moveCountDisplay" class="fs-6">0</strong> barang yang dicentang.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold required">Pilih Ruangan Tujuan</label>
                        <select name="ruangan_id" class="form-select" required>
                            <option value="">-- Pilih Ruangan Baru --</option>
                            @foreach($ruanganList as $r)
                                <option value="{{ $r->id }}">{{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Input hidden untuk menampung ID aset yang dicentang --}}
                    <div id="hiddenMoveInputs"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning shadow-sm fw-bold">
                        <i class="fas fa-exchange-alt me-1"></i> Pindahkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<form id="formExportDir" action="{{ route('admin.aset-fakultas.export-zip') }}" method="POST" style="display:none;">
    @csrf
    <div id="hiddenRuanganInputs"></div>
    <input type="hidden" name="tanggal_ttd" id="hiddenTanggalTtd">
</form>

@endsection

@section('scripts')
@parent
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        placeholder: "-- Semua Ruangan --",
        allowClear: true // Memberikan tombol 'x' kecil untuk mereset pilihan
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // ── Buka modal export ──
    document.getElementById('btnOpenExport')?.addEventListener('click', function () {
        new bootstrap.Modal(document.getElementById('modalExportDir')).show();
    });

    // ── Buka modal Pindah Ruangan ──
    document.getElementById('btnOpenMove')?.addEventListener('click', function () {
        // Ambil semua checkbox aset yang sedang dicentang
        const checked = document.querySelectorAll('.row-check:checked');
        
        // Kalau tidak ada yang dicentang, kasih peringatan
        if (checked.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Barang Dulu',
                text: 'Silakan centang minimal satu barang yang ingin dipindahkan!',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Tampilkan jumlah barang di dalam modal
        document.getElementById('moveCountDisplay').textContent = checked.length;

        // Masukkan ID aset yang dicentang ke dalam form tersembunyi di modal
        const container = document.getElementById('hiddenMoveInputs');
        container.innerHTML = ''; // Kosongkan dulu
        checked.forEach(cb => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'aset_ids[]';
            inp.value = cb.value;
            container.appendChild(inp);
        });

        // Buka modalnya
        new bootstrap.Modal(document.getElementById('modalMoveAset')).show();
    });

    // ── Check all / clear all ──
    document.getElementById('btnSelectAll')?.addEventListener('click', function () {
        document.querySelectorAll('.ruangan-check:not(:disabled)').forEach(cb => cb.checked = true);
        updateSelectedCount();
    });
    document.getElementById('btnClearAll')?.addEventListener('click', function () {
        document.querySelectorAll('.ruangan-check').forEach(cb => cb.checked = false);
        updateSelectedCount();
    });

    // ── Hitung yang dipilih, toggle label & tombol ──
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.ruangan-check:checked').length;
        document.getElementById('selectedCount').textContent = checked + ' dipilih';
        const btn = document.getElementById('btnExportDir');
        btn.disabled = checked === 0;
        document.getElementById('btnExportLabel').textContent =
            checked > 1 ? 'Export ZIP (' + checked + ' PDF)' : 'Export PDF';
    }

    document.querySelectorAll('.ruangan-check').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // ── Klik Export → SweetAlert minta tanggal TTD ──
    document.getElementById('btnExportDir')?.addEventListener('click', function () {
        const checked = document.querySelectorAll('.ruangan-check:checked');
        if (checked.length === 0) return;

        const today = new Date().toISOString().split('T')[0];

        Swal.fire({
            title: '<i class="fas fa-calendar-alt me-2 text-primary"></i>Tanggal Tanda Tangan',
            html: `
                <p class="text-muted mb-3" style="font-size:13px;">
                    Tentukan tanggal yang akan tercetak di bagian tanda tangan pada PDF.
                </p>
                <input type="date" id="swal-tanggal" class="form-control" value="${today}">
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-file-pdf me-1"></i> Generate',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const val = document.getElementById('swal-tanggal').value;
                if (!val) {
                    Swal.showValidationMessage('Pilih tanggal terlebih dahulu.');
                    return false;
                }
                return val;
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            document.getElementById('hiddenTanggalTtd').value = result.value;
            const container = document.getElementById('hiddenRuanganInputs');
            container.innerHTML = '';
            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ruangan_ids[]';
                inp.value = cb.value;
                container.appendChild(inp);
            });

            bootstrap.Modal.getInstance(document.getElementById('modalExportDir')).hide();

            setTimeout(() => {
                document.getElementById('formExportDir').submit();
            }, 300);
        });
    });

    // ── Checkbox "Pilih Semua" di tabel utama ──
    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
});
</script>
@endsection