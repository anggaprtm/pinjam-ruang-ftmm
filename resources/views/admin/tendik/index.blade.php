@extends('layouts.admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="fas fa-users-cog me-2"></i> Manajemen Data Tendik</h3>
    <a class="btn btn-success" href="{{ route('admin.tendik.create') }}">
        <i class="fas fa-plus-circle me-2"></i> Tambah Tendik Baru
    </a>
</div>

@if(session('message'))
    <div class="alert alert-success shadow-sm">{{ session('message') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Tendik">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th><i class="fas fa-user-tie me-1"></i> Pegawai</th>
                        <th class="text-center"><i class="fas fa-id-badge me-1"></i> NIP / NIK</th>
                        <th class="text-center"><i class="fas fa-briefcase me-1"></i> Jabatan & Sub Bagian</th>
                        <th class="text-center"><i class="fas fa-info-circle me-1"></i> Status</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs me-1"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tendiks as $t)
                        <tr data-entry-id="{{ $t->id }}">
                            <td></td>
                            <td data-label="Pegawai">
                                <div class="kegiatan-title-cell fw-bold">{{ $t->tendikDetail->nama_lengkap ?? $t->name }}</div>
                                <div class="text-muted small">{{ $t->email }}</div>
                            </td>
                            <td data-label="ID" class="text-center fw-bold text-primary">
                                {{ $t->nip ?? '-' }}
                            </td>
                            <td data-label="Jabatan" class="text-center">
                                <div class="fw-bold text-dark">{{ $t->tendikDetail->nama_jabatan ?? '-' }}</div>
                                <div class="small text-muted">{{ $t->tendikDetail->sub_bagian ?? '-' }}</div>
                            </td>
                            <td data-label="Status" class="text-center">
                                @if($t->tendikDetail && $t->tendikDetail->status_kepegawaian)
                                    <span class="badge bg-info rounded-pill px-3">{{ $t->tendikDetail->status_kepegawaian }}</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Belum Diatur</span>
                                @endif
                            </td>
                            <td data-label="Aksi" class="text-center actions-cell">
                                <a class="btn btn-xs btn-info shadow-sm" href="{{ route('admin.tendik.show', $t->id) }}" title="Detail"><i class="fas fa-eye"></i></a>
                                <a class="btn btn-xs btn-warning shadow-sm" href="{{ route('admin.tendik.edit', $t->id) }}" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.tendik.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?');" style="display: inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger shadow-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    $(function () {
      let table = $('.datatable-Tendik').DataTable({
        buttons: getStandardDtButtons(),
        order: [[ 1, 'asc' ]],
        language: {                                                    // ← TAMBAH INI
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            lengthMenu: "Tampilkan _MENU_ data",
            search: "Cari:",
            paginate: {
                next: "Berikutnya",
                previous: "Sebelumnya"
            },
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada data tersedia",
            processing: "Memuat..."
        },   
        pageLength: 50,
        columnDefs: [{ orderable: false, className: 'select-checkbox', targets: 0 }, { orderable: false, targets: -1 }],
        select: { style: 'multi+shift', selector: 'td:first-child' },
        dom: "<'dt-top-row'<'dt-top-left'l><'dt-top-center d-none d-md-block'B><'dt-top-right'f>>" +
              "<'row'<'col-sm-12'tr>>" +
              "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
        
        $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });

        // Layout dihandle otomatis oleh dom config

        table.on('select deselect', function () {
            let selectedRows = table.rows({ selected: true }).count();
            table.button(2).enable(selectedRows > 0); // Salin
            table.button(3).enable(selectedRows > 0); // CSV
            table.button(4).enable(selectedRows > 0); // Excel
            table.button(5).enable(selectedRows > 0); // PDF
            table.button(6).enable(selectedRows > 0); // Print
            if (typeof deleteIndex !== 'undefined') table.button(deleteIndex).enable(selectedRows > 0);
        });
        });
</script>
@endsection