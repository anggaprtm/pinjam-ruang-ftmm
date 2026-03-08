@extends('layouts.admin')
@section('content')
@php
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Diajukan',
        'on_verification' => 'Dalam Verifikasi',
        'need_revision' => 'Memerlukan Revisi',
        'approved_final' => 'Disetujui Final',
        'issued' => 'Diterbitkan',
        'cancelled' => 'Dibatalkan',
    ];
@endphp

<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">SIK Proker Ormawa</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Ajukan SIK
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total Pengajuan</div>
                <div class="h4 mb-0">{{ $applications->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Dalam Verifikasi</div>
                <div class="h4 mb-0">{{ $applications->where('status_sik', 'on_verification')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Perlu Revisi</div>
                <div class="h4 mb-0">{{ $applications->where('status_sik', 'need_revision')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">SIK Terbit</div>
                <div class="h4 mb-0">{{ $applications->where('status_sik', 'issued')->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle datatable datatable-Sik">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ormawa</th>
                    <th>Proker</th>
                    <th>Status</th>
                    <th>Timeline Final</th>
                    <th>No SIK e-office</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->ormawa->nama ?? '-' }}</td>
                        <td>{{ $item->judul_final_kegiatan }}</td>
                        <td>
                            <span class="badge bg-{{ $item->status_sik === 'issued' ? 'success' : ($item->status_sik === 'need_revision' ? 'warning text-dark' : ($item->status_sik === 'cancelled' ? 'danger' : 'secondary')) }}">
                                {{ $statusLabels[$item->status_sik] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $item->status_sik)) }}
                            </span>
                        </td>
                        <td>{{ optional($item->timeline_mulai_final)->format('d M Y') }} - {{ optional($item->timeline_selesai_final)->format('d M Y') }}</td>
                        <td>{{ $item->nomor_sik_eoffice ?? '-' }}</td>
                        <td>{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.sik.show', $item->id) }}" class="btn btn-xs btn-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($item->status_sik === 'need_revision')
                                <a href="{{ route('admin.sik.edit', $item->id) }}" class="btn btn-xs btn-warning" title="Revisi Pengajuan">
                                    <i class="fas fa-pen"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada pengajuan SIK.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    const table = $('.datatable-Sik').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'excel', className: 'btn btn-sm btn-outline-success' },
            { extend: 'csv', className: 'btn btn-sm btn-outline-primary' },
            { extend: 'print', className: 'btn btn-sm btn-outline-dark' }
        ],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            zeroRecords: 'Tidak ada data yang cocok',
        }
    });

    table.on('draw', function () {
        const wrapper = $('.datatable-Sik').closest('.dataTables_wrapper');
        const length = wrapper.find('.dataTables_length');
        const filter = wrapper.find('.dataTables_filter');
        const buttons = wrapper.find('.dt-buttons');

        if (!wrapper.find('.dt-top-bar').length) {
            wrapper.prepend('<div class="dt-top-bar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"></div>');
        }

        const topBar = wrapper.find('.dt-top-bar');
        topBar.empty().append(length).append(buttons).append(filter);
    });

    table.draw(false);
});
</script>
@endsection
