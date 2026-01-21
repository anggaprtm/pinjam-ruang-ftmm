@extends('layouts.admin')
@section('content')

{{-- Header --}}
<div class="d-flex align-items-center mb-4">
    <h3 class="font-weight-bold mb-0">Daftar Kendaraan Dinas</h3>
    <div class="ms-auto">
        @can('mobil_create')
            <a class="btn btn-success" href="{{ route('admin.mobils.create') }}">
                <i class="fas fa-plus-circle me-2"></i> Tambah Mobil
            </a>
        @endcan
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Mobil">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Nama Mobil</th>
                        <th>Plat Nomor</th>
                        <th>Warna</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mobils as $key => $mobil)
                        <tr data-entry-id="{{ $mobil->id }}">
                            <td></td>
                            <td class="fw-bold">{{ $mobil->nama_mobil }}</td>
                            <td><span class="badge bg-dark">{{ $mobil->plat_nomor }}</span></td>
                            <td>{{ $mobil->warna ?? '-' }}</td>
                            <td class="text-center">
                                @if($mobil->status == 'tersedia')
                                    <span class="badge bg-success">Tersedia</span>
                                @elseif($mobil->status == 'dipakai')
                                    <span class="badge bg-danger">Sedang Dipakai</span>
                                @else
                                    <span class="badge bg-warning text-dark">Maintenance</span>
                                @endif
                            </td>
                            <td class="text-center actions-cell">
                                @can('mobil_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.mobils.show', $mobil->id) }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan

                                @can('mobil_edit')
                                    <a class="btn btn-xs btn-info text-white" href="{{ route('admin.mobils.edit', $mobil->id) }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan

                                @can('mobil_delete')
                                    <form action="{{ route('admin.mobils.destroy', $mobil->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-xs btn-danger">
                                            <i class="fas fa-trash-alt"></i>
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
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);
        
        @can('mobil_delete')
            let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
            let deleteButton = {
                text: deleteButtonTrans,
                url: "{{ route('admin.mobils.massDestroy') }}",
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
                            data: { ids: ids, _method: 'DELETE' }
                        }).done(function () { location.reload() })
                    }
                }
            }
            dtButtons.push(deleteButton)
        @endcan

        $.extend(true, $.fn.dataTable.defaults, {
            orderCellsTop: true,
            order: [[ 1, 'asc' ]], // Order by Nama Mobil
            pageLength: 25,
        });
        
        $('.datatable-Mobil:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });
    })
</script>
@endsection