@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold"><i class="fas fa-chalkboard-teacher me-2"></i> Manajemen Data Dosen</h3>
    <a class="btn btn-success" href="{{ route('admin.dosen.create') }}">
        <i class="fas fa-plus-circle me-2"></i> Tambah Dosen Baru
    </a>
</div>

@if(session('message'))
    <div class="alert alert-success shadow-sm">{{ session('message') }}</div>
@endif

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Dosen">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th><i class="fas fa-user-tie me-1"></i> Dosen</th>
                        <th class="text-center"><i class="fas fa-id-card me-1"></i> ID (NIP/NIDN)</th>
                        <th class="text-center"><i class="fas fa-graduation-cap me-1"></i> Jabatan / Golongan</th>
                        <th class="text-center"><i class="fas fa-building me-1"></i> Homebase</th>
                        <th class="text-center"><i class="fas fa-info-circle me-1"></i> Status</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs me-1"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dosens as $d)
                        <tr data-entry-id="{{ $d->id }}">
                            <td></td> {{-- Checkbox Placeholder --}}
                            
                            <td data-label="Dosen">
                                <div class="kegiatan-title-cell fw-bold">{{ $d->dosenDetail->nama_lengkap_gelar ?? $d->name }}</div>
                                <div class="text-muted small">{{ $d->email }}</div>
                            </td>
                            
                            <td data-label="ID" class="text-center">
                                <div class="small"><span class="fw-bold">NIP:</span> {{ $d->nip ?? '-' }}</div>
                                <div class="small"><span class="fw-bold">NIDN:</span> {{ $d->dosenDetail->nidn ?? '-' }}</div>
                            </td>
                            
                            <td data-label="Jabatan" class="text-center">
                                <div>{{ $d->dosenDetail->jabatan_fungsional ?? '-' }}</div>
                                <div class="badge bg-light text-dark border">{{ $d->dosenDetail->pangkat_golongan ?? '-' }}</div>
                            </td>
                            
                            <td data-label="Homebase" class="text-center">
                                {{ $d->dosenDetail->homebase_prodi ?? '-' }}
                            </td>
                            
                            <td data-label="Status" class="text-center">
                                @php
                                    $status = $d->dosenDetail->status_keaktifan ?? 'Aktif';
                                    $badgeClass = 'bg-success';
                                    if($status == 'Tugas Belajar') $badgeClass = 'bg-info';
                                    elseif($status == 'Izin' || $status == 'Cuti') $badgeClass = 'bg-warning text-dark';
                                    elseif($status == 'Pensiun') $badgeClass = 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill px-3">{{ $status }}</span>
                            </td>
                            
                            <td data-label="Aksi" class="text-center actions-cell">
                                <a class="btn btn-xs btn-info shadow-sm" href="{{ route('admin.dosen.show', $d->id) }}" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a class="btn btn-xs btn-warning shadow-sm" href="{{ route('admin.dosen.edit', $d->id) }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.dosen.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus dosen ini? Data absensi terkait juga bisa terpengaruh.');" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger shadow-sm" title="Hapus"><i class="fas fa-trash"></i></button>
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
      let dtButtons = getStandardDtButtons();
      
      let table = $('.datatable-Dosen').DataTable({
        buttons: dtButtons,
        order: [[ 1, 'asc' ]],
        pageLength: 50,
        columnDefs: [
            { orderable: false, className: 'select-checkbox', targets: 0 }, 
            { orderable: false, searchable: false, targets: -1 }
        ],
        select: { style: 'multi+shift', selector: 'td:first-child' },
        dom: 'lBfrtip'
      });
      
      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      });

      $('.datatable-Dosen').on('draw.dt', function () {
          var wrapper = $(this).closest('.dataTables_wrapper');
          var length = wrapper.find('.dataTables_length');
          var filter = wrapper.find('.dataTables_filter');
          var buttons = wrapper.find('.dt-buttons');

          if (!wrapper.find('.dt-controls-row').length) {
              var controlsRow = $('<div class="dt-controls-row"></div>');
              var leftCol = $('<div class="dt-controls-left"></div>').append(length).append(buttons);
              var rightCol = $('<div class="dt-controls-right"></div>').append(filter);
              controlsRow.append(leftCol).append(rightCol);
              wrapper.prepend(controlsRow);
          }
      });

      table.draw();
      table.on('select deselect', function () {
          let selectedRows = table.rows({ selected: true }).count();
          table.button(2).enable(selectedRows > 0); // Salin
          table.button(3).enable(selectedRows > 0); // CSV
          table.button(4).enable(selectedRows > 0); // Excel
          table.button(5).enable(selectedRows > 0); // PDF
          table.button(6).enable(selectedRows > 0); // Print
      });
    });
</script>
@endsection