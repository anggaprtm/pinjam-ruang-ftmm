@extends('layouts.admin')
@section('content')
@can('kegiatan_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.kegiatans.create') }}">
                <i class="fas fa-fw fa-plus"></i> &nbsp Tambah Kegiatan
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        Daftar Kegiatan
    </div>
    <div class="d-flex justify-content-start mb-3 filt-keg">
        <form action="{{ route('admin.kegiatans.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="form-group mb-0">
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}" placeholder="dd/mm/yyyy">
            </div>
            <button type="submit" class="btn btn-primary ms-3">Filter</button>
            <a href="{{ route('admin.kegiatans.index') }}" class="btn btn-secondary ms-3">Reset Filter</a>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Kegiatan">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th width="15">
                            {{ trans('cruds.kegiatan.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.kegiatan.fields.ruangan') }}
                        </th>
                        <th width="240">
                            {{ trans('cruds.kegiatan.fields.nama_kegiatan') }}
                        </th>
                        <th>
                            {{ trans('cruds.kegiatan.fields.waktu_mulai') }}
                        </th>
                        <th>
                            {{ trans('cruds.kegiatan.fields.waktu_selesai') }}
                        </th>
                        <th>
                            Peminjam
                        </th>
                        <th>
                            Status
                        </th>
                        <th width="150">
                            Keterangan
                        </th>
                        <th width="210">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kegiatans as $key => $kegiatan)
                        <tr data-entry-id="{{ $kegiatan->id }}">
                            <td>
                                
                            </td>
                            <td>
                                {{ $kegiatan->id ?? '' }}
                            </td>
                            <td>
                                {{ $kegiatan->ruangan->nama ?? '' }}
                            </td>
                            <td>
                                {{ $kegiatan->nama_kegiatan ?? '' }}
                            </td>
                            <td>
                                {{ $kegiatan->waktu_mulai ?? '' }}
                            </td>
                            <td>
                                {{ $kegiatan->waktu_selesai ?? '' }}
                            </td>
                            <td>
                                {{ $kegiatan->user->name ?? '' }}
                            </td>
                            <td>
                                <!-- Menampilkan Status -->
                                @if($kegiatan->status == 'belum_disetujui')
                                    <span class="badge badge-warning">Belum Disetujui</span>
                                @elseif($kegiatan->status == 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($kegiatan->status == 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td> 
                            <td>
                                {{ $kegiatan->deskripsi ?? '' }}
                            </td>  
                            <td>
                                @can('kegiatan_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.kegiatans.show', $kegiatan->id) }}">
                                        <i class="fas fa-fw fa-search"></i>
                                    </a>
                                @endcan

                                @can('kegiatan_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.kegiatans.edit', $kegiatan->id) }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>
                                @endcan

                                @can('kegiatan_delete')
                                    <form action="{{ route('admin.kegiatans.destroy', $kegiatan->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-xs btn-danger"></i>
                                            <i class="fas fa-fw fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan

                                @can('kegiatan_edit_status')
                                    <!-- Dropdown untuk mengubah status -->
                                    <form action="{{ route('admin.kegiatans.updateStatus', $kegiatan->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="form-control form-control-sm">
                                            <option value="belum_disetujui" {{ $kegiatan->status == 'belum_disetujui' ? 'selected' : '' }}>Belum Disetujui</option>
                                            <option value="disetujui" {{ $kegiatan->status == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                            <option value="ditolak" {{ $kegiatan->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                        </select>
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
    url: "{{ route('admin.kegiatans.massDestroy') }}",
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
  let table = $('.datatable-Kegiatan:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})
</script>
@endsection