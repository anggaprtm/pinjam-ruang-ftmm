@extends('layouts.admin')
@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="font-weight-bold text-nowrap">Aset Fakultas</h3>
    <div class="d-flex gap-2 flex-wrap">
        @can('aset_fakultas_create')
            <a class="btn btn-secondary" href="{{ route('admin.aset-fakultas.import.form') }}">
                <i class="fas fa-file-import me-1"></i> Import Excel
            </a>
            <a class="btn btn-success" href="{{ route('admin.aset-fakultas.create') }}">
                <i class="fas fa-plus-circle me-1"></i> Tambah Aset
            </a>
        @endcan
        @can('aset_fakultas_access')
            <button class="btn btn-danger" id="btnOpenExport">
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
                <label class="form-label small mb-1">Cari Nama / Kode / Merk</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Ketik untuk cari..." value="{{ $filterSearch }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Ruangan</label>
                <select name="ruangan_id" class="form-select form-select-sm">
                    <option value="">-- Semua Ruangan --</option>
                    @foreach($ruanganList as $r)
                        <option value="{{ $r->id }}" {{ $filterRuangan == $r->id ? 'selected' : '' }}>{{ $r->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Kondisi</label>
                <select name="kondisi" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($kondisiOptions as $k => $v)
                        <option value="{{ $k }}" {{ $filterKondisi == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.aset-fakultas.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="10"><input type="checkbox" id="checkAll"></th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Tahun</th>
                        <th class="text-center">Kondisi</th>
                        <th>Merk</th>
                        <th>Lokasi / Ruangan</th>
                        <th class="text-center" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asets as $item)
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" class="row-check" value="{{ $item->id }}">
                            </td>
                            <td><code class="small">{{ $item->kode_barang }}</code></td>
                            <td>
                                <div class="fw-semibold">{{ $item->nama_barang }}</div>
                                @if($item->deskripsi)
                                    <div class="text-muted small">{{ Str::limit($item->deskripsi, 60) }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->tahun_aset ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $badge = match($item->kondisi) {
                                        'Baik'         => 'success',
                                        'Rusak Ringan' => 'warning',
                                        'Rusak Berat'  => 'danger',
                                        default        => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $item->kondisi }}</span>
                            </td>
                            <td>{{ $item->merk ?? '-' }}</td>
                            <td>
                                @if($item->ruangan)
                                    <span class="badge bg-info text-dark">
                                        <i class="fas fa-door-open me-1"></i>{{ $item->ruangan->nama }}
                                    </span>
                                @else
                                    <span class="text-muted small">{{ Str::limit($item->lokasi_text, 40) ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @can('aset_fakultas_show')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.aset-fakultas.show', $item->id) }}" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                @can('aset_fakultas_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.aset-fakultas.edit', $item->id) }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('aset_fakultas_delete')
                                    <form action="{{ route('admin.aset-fakultas.destroy', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus aset ini?');" style="display:inline-block;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                Tidak ada data aset ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($asets->hasPages())
            <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Menampilkan {{ $asets->firstItem() }}–{{ $asets->lastItem() }} dari {{ $asets->total() }} aset
                </small>
                <div class="pagination-wrapper mt-3">
                    {{ $asets->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MODAL: Pilih Ruangan untuk Export DIR
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalExportDir" tabindex="-1" aria-labelledby="modalExportDirLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExportDirLabel">
                    <i class="fas fa-file-pdf me-2 text-danger"></i>Export Daftar Inventaris Ruang (DIR)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Pilih satu atau beberapa ruangan. Jika hanya satu ruangan dipilih, hasilnya langsung <strong>.pdf</strong>.
                    Jika lebih dari satu, semua dikemas dalam <strong>.zip</strong> (satu PDF per ruangan, masing-masing berisi tanda tangan).
                </p>

                {{-- Tombol Pilih/Hapus Semua --}}
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelectAll">
                        <i class="fas fa-check-double me-1"></i>Pilih Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnClearAll">
                        <i class="fas fa-times me-1"></i>Hapus Pilihan
                    </button>
                    <span class="ms-auto badge bg-secondary align-self-center" id="selectedCount">0 dipilih</span>
                </div>

                {{-- Daftar Ruangan --}}
                <div class="row g-2" id="ruanganCheckList">
                    @foreach($ruanganList as $r)
                        @php $jmlAset = $asetPerRuangan[$r->id] ?? 0; @endphp
                        <div class="col-12 col-md-6">
                            <label class="d-flex align-items-center border rounded px-3 py-2 gap-2 cursor-pointer {{ $jmlAset === 0 ? 'opacity-50' : '' }}"
                                   for="ruangan_exp_{{ $r->id }}"
                                   style="cursor:pointer; transition: background .15s;">
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnExportDir" disabled>
                    <i class="fas fa-download me-1"></i>
                    <span id="btnExportLabel">Export PDF</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Form tersembunyi untuk submit export --}}
<form id="formExportDir" action="{{ route('admin.aset-fakultas.export-zip') }}" method="POST" style="display:none;">
    @csrf
    <div id="hiddenRuanganInputs"></div>
    <input type="hidden" name="tanggal_ttd" id="hiddenTanggalTtd">
</form>

@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Buka modal export ──
    document.getElementById('btnOpenExport')?.addEventListener('click', function () {
        new bootstrap.Modal(document.getElementById('modalExportDir')).show();
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

        // Format tanggal hari ini untuk default value
        const today = new Date().toISOString().split('T')[0];

        Swal.fire({
            title: '<i class="fas fa-calendar-alt me-2"></i>Tanggal Tanda Tangan',
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
            confirmButtonText: '<i class="fas fa-file-pdf me-1"></i> Generate PDF',
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

            // Isi hidden inputs
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

            // Tutup modal & submit form
            bootstrap.Modal.getInstance(document.getElementById('modalExportDir')).hide();

            // Sedikit delay agar modal selesai menutup
            setTimeout(() => {
                document.getElementById('formExportDir').submit();
            }, 300);
        });
    });

    // ── Checkbox "Pilih Semua" di tabel ──
    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
});
</script>
@endsection
