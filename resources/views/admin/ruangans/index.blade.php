@extends('layouts.admin')
@section('content')
@can('ruangan_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.ruangans.create') }}">
                <i class="fas fa-fw fa-plus"></i> &nbsp Tambah Data
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        Daftar Ruangan
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Ruangan">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th width="15">
                            {{ trans('cruds.ruangan.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.ruangan.fields.nama') }}
                        </th>
                        <th>
                            {{ trans('cruds.ruangan.fields.deskripsi') }}
                        </th>
                        <th>
                            {{ trans('cruds.ruangan.fields.kapasitas') }}
                        </th>
                        <th width="100">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ruangans as $key => $ruangan)
                        <tr data-entry-id="{{ $ruangan->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $ruangan->id ?? '' }}
                            </td>
                            <td>
                                {{ $ruangan->nama ?? '' }}
                            </td>
                            <td>
                                {{ $ruangan->deskripsi ?? '' }}
                            </td>
                            <td>
                                {{ $ruangan->kapasitas ?? '' }}
                            </td>
                            <td>
                                @can('ruangan_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.ruangans.show', $ruangan->id) }}">
                                        <i class="fas fa-fw fa-search"></i>
                                    </a>
                                @endcan

                                @can('ruangan_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.ruangans.edit', $ruangan->id) }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>
                                @endcan

                                @can('ruangan_delete')
                                    <form action="{{ route('admin.ruangans.destroy', $ruangan->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-xs btn-danger"></i>
                                            <i class="fas fa-fw fa-trash"></i>
                                        </button>
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
@can('ruangan_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.ruangans.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-Ruangan:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection