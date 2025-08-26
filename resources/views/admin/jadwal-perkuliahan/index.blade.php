@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">Daftar Jadwal Perkuliahan</h3>
    @can('kuliah_create')
        <a class="btn btn-success" href="{{ route('admin.jadwal-perkuliahan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Tambah Data
        </a>
    @endcan
</div>

{{-- Filter Bar & Import --}}
<div class="filter-bar">
    <div class="row g-3">
        {{-- Filter Hari --}}
        <div class="col-md-6">
            <form action="{{ route('admin.jadwal-perkuliahan.index') }}" method="GET" class="d-flex align-items-end gap-2">
                <div class="flex-grow-1">
                    <label for="hari" class="form-label fw-bold">Filter Hari:</label>
                    <select name="hari" id="hari" class="form-control select2">
                        <option value="">-- Semua Hari --</option>
                        @php
                            $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                        @endphp
                        @foreach ($daftarHari as $hari)
                            <option value="{{ $hari }}" {{ request('hari') == $hari ? 'selected' : '' }}>
                                {{ $hari }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.jadwal-perkuliahan.index') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        {{-- Fitur Import --}}
        <div class="col-md-6">
            @can('kuliah_create')
            <form action="{{ route('admin.jadwal-perkuliahan.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-end gap-2">
                @csrf
                <div class="flex-grow-1">
                    <label for="import_file" class="form-label fw-bold">Import dari Excel:</label>
                    <input type="file" name="file" id="import_file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-import"></i> Import
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>


{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-jadwals">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th><i class="fas fa-book"></i>Mata Kuliah</th>
                        <th><i class="fas fa-chalkboard-teacher"></i>Prodi & Tipe</th>
                        <th><i class="fas fa-door-open"></i>Ruangan</th>
                        <th><i class="fas fa-calendar-day"></i>Jadwal Hari & Jam</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwals as $jadwal)
                        <tr data-entry-id="{{ $jadwal->id }}">
                            <td></td>
                            <td>
                                <div class="kegiatan-title-cell">{{ $jadwal->mata_kuliah }}</div>
                                <div class="kegiatan-sub-cell">ID: {{ $jadwal->id }}</div>
                            </td>
                            <td>
                                <div class="kegiatan-sub-cell">{{ $jadwal->program_studi }}</div>
                                <span class="badge-ruangan">{{ $jadwal->tipe }}</span>
                            </td>
                            <td>
                                <span class="badge-ruangan">{{ $jadwal->ruangan->nama ?? '' }}</span>
                            </td>
                            <td>
                                <div class="kegiatan-sub-cell"><strong>{{ $jadwal->hari }}</strong></div>
                                <div class="kegiatan-sub-cell">
                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                </div>
                            </td>
                            <td class="text-center actions-cell">
                                @can('kuliah_show')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.jadwal-perkuliahan.show', $jadwal->id) }}" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                @can('kuliah_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.jadwal-perkuliahan.edit', $jadwal->id) }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('kuliah_delete')
                                    <form action="{{ route('admin.jadwal-perkuliahan.destroy', $jadwal->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
      @can('kuliah_delete')
      let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
      let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.jadwal-perkuliahan.massDestroy') }}",
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

      let table = $('.datatable-jadwals').DataTable({
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

      $('.datatable-jadwals').on('draw.dt', function () {
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
