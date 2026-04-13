@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Arsip Surat Undangan</h4>
        <small class="text-muted">Generator & Arsip Surat Undangan Resmi FTMM</small>
    </div>
    <a href="{{ route('admin.surat-undangan.create') }}" class="btn btn-success shadow-sm">
        <i class="fas fa-plus-circle me-2"></i> Buat Surat Baru
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable-SuratUndangan" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th>Nomor & Perihal</th>
                        <th>Tanggal Surat</th>
                        <th>Tujuan (Yth.)</th>
                        <th>Agenda</th>
                        <th>Penandatangan</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    $('.datatable-SuratUndangan').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.surat-undangan.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            {
                data: 'nomor_surat',
                name: 'nomor_surat',
                render: function(data, type, row) {
                    return '<div class="fw-bold text-primary">' + data + '</div>'
                         + '<small class="text-muted">' + (row.hal_surat || 'Undangan') + '</small>';
                }
            },
            { data: 'tanggal_surat', name: 'tanggal_surat' },
            { data: 'tujuan_surat', name: 'tujuan_surat', orderable: false },
            { data: 'agenda_acara', name: 'agenda_acara', orderable: false },
            { data: 'nama_penandatangan', name: 'nama_penandatangan' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Memuat data...',
            emptyTable: 'Belum ada arsip surat undangan.',
            zeroRecords: 'Surat tidak ditemukan.',
        }
    });
});
</script>
@endsection