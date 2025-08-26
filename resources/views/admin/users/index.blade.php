@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">{{ trans('cruds.user.title') }}</h3>
    @can('user_create')
        <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
            <i class="fas fa-plus-circle me-2"></i> {{ trans('global.add') }} {{ trans('cruds.user.title_singular') }}
        </a>
    @endcan
</div>

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-User">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th><i class="fas fa-user"></i>{{ trans('cruds.user.fields.name') }}</th>
                        <th><i class="fas fa-envelope"></i>{{ trans('cruds.user.fields.email') }}</th>
                        <th class="text-center"><i class="fas fa-check-circle"></i>{{ trans('cruds.user.fields.email_verified_at') }}</th>
                        <th><i class="fas fa-briefcase"></i>{{ trans('cruds.user.fields.roles') }}</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $key => $user)
                        <tr data-entry-id="{{ $user->id }}">
                            <td></td> {{-- Checkbox --}}
                            <td>
                                <div class="kegiatan-title-cell">{{ $user->name ?? '' }}</div>
                                <div class="kegiatan-sub-cell">ID: {{ $user->id ?? '' }}</div>
                            </td>
                            <td>
                                <div class="kegiatan-sub-cell">{{ $user->email ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                @if($user->email_verified_at)
                                    <span class="badge-status badge-status-aktif">Terverifikasi</span>
                                @else
                                    <span class="badge-status badge-status-tidak-aktif">Belum</span>
                                @endif
                            </td>
                            <td>
                                <div class="permission-badges-container">
                                    @foreach($user->roles as $key => $item)
                                        <span class="badge badge-permission">{{ $item->title }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center actions-cell">
                                @can('user_show')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.users.show', $user->id) }}" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                @can('user_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.users.edit', $user->id) }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('user_delete')
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
      @can('user_delete')
      let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
      let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.users.massDestroy') }}",
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

      let table = $('.datatable-User').DataTable({
        buttons: dtButtons,
        order: [[ 1, 'asc' ]],
        pageLength: 50,
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
        dom: 'lBfrtip'
      });
      
      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
          $($.fn.dataTable.tables(true)).DataTable()
              .columns.adjust();
      });

      $('.datatable-User').on('draw.dt', function () {
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
