@extends('layouts.admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold"><i class="fas fa-users-cog me-2"></i> Manajemen Data Tendik</h3>
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
                            <td data-label="ID" class="text-center">
                                <div class="small"><span class="fw-bold">NIP:</span> {{ $t->nip ?? '-' }}</div>
                                <div class="small"><span class="fw-bold">NIK:</span> {{ $t->tendikDetail->nik ?? '-' }}</div>
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
        pageLength: 50,
        columnDefs: [{ orderable: false, className: 'select-checkbox', targets: 0 }, { orderable: false, targets: -1 }],
        select: { style: 'multi+shift', selector: 'td:first-child' },
        dom: 'lBfrtip'
      });
      $('.datatable-Tendik').on('draw.dt', function () {
          var wrapper = $(this).closest('.dataTables_wrapper');
          if (!wrapper.find('.dt-controls-row').length) {
              var controlsRow = $('<div class="dt-controls-row"></div>');
              controlsRow.append($('<div class="dt-controls-left"></div>').append(wrapper.find('.dataTables_length')).append(wrapper.find('.dt-buttons')));
              controlsRow.append($('<div class="dt-controls-right"></div>').append(wrapper.find('.dataTables_filter')));
              wrapper.prepend(controlsRow);
          }
      });
      table.draw();
    });
</script>
@endsection