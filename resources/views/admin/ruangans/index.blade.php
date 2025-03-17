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
                        <th style="text-align:center;" width="10">

                        </th>
                        <th style="text-align:center;" width="15">
                            {{ trans('cruds.ruangan.fields.id') }}
                        </th>
                        <th style="text-align:center;">
                            {{ trans('cruds.ruangan.fields.nama') }}
                        </th>
                        <th style="text-align:center;">
                            {{ trans('cruds.ruangan.fields.deskripsi') }}
                        </th>
                        <th style="text-align:center;" width="100">
                            {{ trans('cruds.ruangan.fields.kapasitas') }}
                        </th>
                        <th style="text-align:center;" width="100">
                            Status
                        </th>
                        <th style="text-align:center;" width="100">
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
                            <td style="text-align:center;">
                                {{ $ruangan->kapasitas ?? '' }}
                            </td>
                            <td style="text-align:center;">
                                <form action="{{ route('admin.ruangans.toggle', $ruangan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-{{ $ruangan->is_active ? 'success' : 'danger' }}">
                                        {{ $ruangan->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
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
                                    <form id="delete-form-{{ $ruangan->id }}" action="{{ route('admin.ruangans.destroy', $ruangan->id) }}" method="POST" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete({{ $ruangan->id }})">
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
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);

        @can('ruangan_delete')
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.ruangans.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
                var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                    return $(entry).data('entry-id');
                });

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak ada data yang dipilih!',
                        text: 'Pilih setidaknya satu ruangan untuk dihapus.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Data yang dipilih akan dihapus secara permanen!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            html: '<b>Mohon tunggu</b>, data sedang diproses.<br><br><div class="spinner-border text-danger" role="status"><span class="sr-only">Loading...</span></div>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false
                        });

                        $.ajax({
                            headers: {'x-csrf-token': _token},
                            method: 'POST',
                            url: config.url,
                            data: { ids: ids, _method: 'DELETE' }
                        })
                        .done(function () {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Data telah dihapus.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .fail(function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Terjadi kesalahan saat menghapus data. Coba lagi nanti.',
                                confirmButtonText: 'OK'
                            });
                        });
                    }
                });
            }
        };
        dtButtons.push(deleteButton);
        @endcan

        $.extend(true, $.fn.dataTable.defaults, {
            orderCellsTop: true,
            order: [[1, 'desc']],
            pageLength: 100,
        });

        let table = $('.datatable-Ruangan:not(.ajaxTable)').DataTable({ buttons: dtButtons });

        $('a[data-toggle="tab"]').on('shown.bs.tab click', function (e) {
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });

    });
</script>

@endsection