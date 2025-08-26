@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">{{ trans('cruds.permission.title') }}</h3>
    @can('permission_create')
        <a class="btn btn-primary" href="{{ route('admin.permissions.create') }}">
            <i class="fas fa-plus-circle me-2"></i> {{ trans('global.add') }} {{ trans('cruds.permission.title_singular') }}
        </a>
    @endcan
</div>

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Permission">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th width="50"><i class="fas fa-id-card"></i>{{ trans('cruds.permission.fields.id') }}</th>
                        <th><i class="fas fa-unlock-alt"></i>{{ trans('cruds.permission.fields.title') }}</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $key => $permission)
                        <tr data-entry-id="{{ $permission->id }}">
                            <td></td> {{-- Checkbox --}}
                            <td>{{ $permission->id ?? '' }}</td>
                            <td>
                                <div class="kegiatan-title-cell">{{ $permission->title ?? '' }}</div>
                            </td>
                            <td class="text-center actions-cell">
                                @can('permission_show')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.permissions.show', $permission->id) }}" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                @can('permission_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.permissions.edit', $permission->id) }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('permission_delete')
                                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
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
      @can('permission_delete')
      let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
      let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.permissions.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
          var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
              return $(entry).data('entry-id')
          });

          if (ids.length === 0) {
            Swal.fire('Peringatan', '{{ trans('global.datatables.zero_selected') }}', 'warning')
            return
          }

          Swal.fire({
            title: '{{ trans('global.areYouSure') }}',
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

      // Inisialisasi DataTables dengan semua konfigurasi yang diperlukan
      let table = $('.datatable-Permission').DataTable({
        buttons: dtButtons,
        order: [[ 1, 'asc' ]],
        pageLength: 50,
        // PERBAIKAN: Menambahkan columnDefs dan select untuk fungsionalitas checkbox
        columnDefs: [
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            }, 
            {
                orderable: false,
                searchable: false,
                targets: -1
            }
        ],
        select: {
            style: 'multi+shift',
            selector: 'td:first-child'
        },
        // PERBAIKAN: Secara eksplisit mendefinisikan DOM untuk menampilkan semua kontrol
        dom: 'lBfrtip'
      });
      
      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
          $($.fn.dataTable.tables(true)).DataTable()
              .columns.adjust();
      });

      // Atur ulang tata letak kontrol DataTables
      $('.datatable-Permission').on('draw.dt', function () {
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
