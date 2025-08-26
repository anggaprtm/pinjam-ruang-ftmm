@extends('layouts.admin')
@section('content')

{{-- Bagian Header & Filter --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">Daftar Kegiatan</h3>
    @can('kegiatan_create')
        <a class="btn btn-primary" href="{{ route('admin.kegiatan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Tambah Kegiatan
        </a>
    @endcan
</div>

<div class="filter-bar">
    <form action="{{ route('admin.kegiatan.index') }}" method="GET" class="d-flex align-items-center gap-2">
        <div class="form-group mb-0 flex-grow-1">
            <label for="tanggal_mulai" class="form-label fw-bold">Filter Tanggal Mulai:</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
        </div>
        <button type="submit" class="btn btn-primary align-self-end">Filter</button>
        <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-secondary align-self-end">Reset</a>
    </form>
</div>

{{-- Tabel Modern dengan Fungsionalitas DataTables --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Kegiatan">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th><i class="fas fa-clipboard-list"></i>Kegiatan</th>
                        <th><i class="fas fa-door-open"></i>Ruangan</th>
                        <th><i class="fas fa-calendar-alt"></i>Jadwal</th>
                        <th class="text-center"><i class="fas fa-info-circle"></i>Status</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kegiatan as $item)
                        <tr data-entry-id="{{ $item->id }}">
                            <td></td> {{-- Checkbox --}}
                            <td>
                                <div class="kegiatan-title-cell">{{ $item->nama_kegiatan }}</div>
                                <div class="d-flex align-items-center mt-1">
                                    {{-- PERUBAHAN 1: Avatar menjadi ikon --}}
                                    <div class="user-avatar"><i class="fas fa-user"></i></div>
                                    <div>
                                        <div class="kegiatan-sub-cell">{{ $item->user->name ?? '-' }}</div>
                                        {{-- PERUBAHAN 2: Menambahkan waktu pembuatan --}}
                                        <div class="creation-timestamp" title="{{ $item->created_at->format('d M Y, H:i:s') }}">
                                            Dibuat: {{ $item->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-ruangan">{{ $item->ruangan->nama ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="kegiatan-sub-cell">
                                    Mulai: {{ \Carbon\Carbon::parse($item->waktu_mulai)->translatedFormat('d M Y, H:i') }}
                                </div>
                                <div class="kegiatan-sub-cell">
                                    Selesai: {{ \Carbon\Carbon::parse($item->waktu_selesai)->translatedFormat('d M Y, H:i') }}
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = str_replace('_', '-', $item->status);
                                    $statusText = '';
                                    switch ($item->status) {
                                        case 'belum_disetujui': $statusText = 'Menunggu Operator'; break;
                                        case 'verifikasi_sarpras': $statusText = 'Menunggu Akademik'; break;
                                        case 'verifikasi_akademik': $statusText = 'Menunggu Sarpras'; break;
                                        case 'disetujui': $statusText = 'Disetujui'; break;
                                        case 'ditolak': $statusText = 'Ditolak'; break;
                                        default: $statusText = $item->status; break;
                                    }
                                @endphp
                                <span class="badge-status badge-status-{{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="text-center actions-cell">
                                @can('kegiatan_show')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.kegiatan.show', $item->id) }}" title="Detail"><i class="fas fa-eye"></i></a>
                                @endcan
                                @can('kegiatan_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.kegiatan.edit', $item->id) }}" title="Edit"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('kegiatan_delete')
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('admin.kegiatan.destroy', $item->id) }}" method="POST" style="display: inline-block;">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete({{ $item->id }})" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endcan
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
      let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
      
      @can('kegiatan_delete')
      let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
      let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.kegiatan.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
          var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
              return $(entry).data('entry-id')
          });

          if (ids.length === 0) {
            Swal.fire('Peringatan', 'Tidak ada data yang dipilih', 'warning')
            return
          }

          Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dipilih akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                headers: {'x-csrf-token': _token},
                method: 'POST',
                url: config.url,
                data: { ids: ids, _method: 'DELETE' }})
                .done(function () { location.reload() })
            }
          })
        }
      }
      dtButtons.push(deleteButton)
      @endcan

      $.extend(true, $.fn.dataTable.defaults, {
        orderCellsTop: true,
        // PERUBAHAN DI SINI: Baris 'order' dihapus agar mengikuti urutan dari server
        pageLength: 50,
      });
      let table = $('.datatable-Kegiatan:not(.ajaxTable)').DataTable({ buttons: dtButtons })
      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
          $($.fn.dataTable.tables(true)).DataTable()
              .columns.adjust();
      });
      
      $('.datatable-Kegiatan').on('draw.dt', function () {
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
    });
</script>
@endsection
