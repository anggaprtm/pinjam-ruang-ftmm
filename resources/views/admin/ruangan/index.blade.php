@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">{{ trans('cruds.ruangan.title') }}</h3>
    @can('ruangan_create')
        <a class="btn btn-success" href="{{ route('admin.ruangan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> {{ trans('global.add') }} {{ trans('cruds.ruangan.title_singular') }}
        </a>
    @endcan
</div>

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Ruangan">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th><i class="fas fa-door-open"></i>{{ trans('cruds.ruangan.fields.nama') }}</th>
                        <th class="text-center"><i class="fas fa-users"></i>{{ trans('cruds.ruangan.fields.kapasitas') }}</th>
                        <th class="text-center"><i class="fas fa-building"></i> Lantai</th>
                        <th class="text-center"><i class="fas fa-info-circle"></i>Status</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ruangan as $key => $item)
                        <tr data-entry-id="{{ $item->id }}">
                            <td></td> {{-- Checkbox --}}
                            <td data-label="Ruangan">
                                <div class="kegiatan-title-cell">{{ $item->nama ?? '' }}</div>
                                <div class="text-muted small">{{ Str::limit($item->deskripsi, 75) ?? '' }}</div>
                            </td>
                            <td data-label="Kapasitas" class="text-center">
                                <span class="badge-ruangan">{{ $item->kapasitas ?? '' }} Orang</span>
                            </td>
                            <td data-label="Lantai" class="text-center">
                                <span class="badge bg-info">Lantai {{ $item->lantai ?? '' }}</span>
                            </td>
                            <td data-label="Status" class="text-center">
                                {{-- PERUBAHAN DI SINI: Mengganti badge dengan toggle switch --}}
                                <form action="{{ route('admin.ruangan.toggle', $item->id) }}" method="POST" class="toggle-switch-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="toggle-switch">
                                        <input type="checkbox" {{ $item->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td data-label="Aksi" class="text-center actions-cell">
                                @can('ruangan_show')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.ruangan.show', $item->id) }}" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                @can('ruangan_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.ruangan.edit', $item->id) }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('ruangan_delete')
                                    <form action="{{ route('admin.ruangan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
      // Salin tombol default dari global config
      let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
      
      // Tambahkan tombol hapus massal jika diizinkan
      @can('ruangan_delete')
      let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
      let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.ruangan.massDestroy') }}",
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

      // Inisialisasi DataTables dengan semua konfigurasi yang diperlukan
      let table = $('.datatable-Ruangan').DataTable({
        buttons: dtButtons,
        order: [[ 1, 'asc' ]],
        pageLength: 50,
        // PERBAIKAN: Menambahkan kembali columnDefs dan select untuk fungsionalitas checkbox
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

      // Atur ulang tata letak kontrol DataTables agar rapi
      $('.datatable-Ruangan').on('draw.dt', function () {
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

      // Panggil draw untuk menerapkan tata letak saat pertama kali dimuat
      table.draw();
    });
</script>
@endsection
