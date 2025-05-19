@extends('layouts.admin')
@section('content')
@can('kuliah_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.jadwal-perkuliahan.create') }}">
                <i class="fas fa-fw fa-plus"></i> &nbsp Tambah Data
            </a>
        </div>
    </div>

    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12 d-flex align-items-center">
            <form action="{{ route('admin.jadwal-perkuliahan.import') }}" method="POST" enctype="multipart/form-data" class="form-inline d-flex gap-2">
                @csrf
                <div class="input-group" style="max-width: 300px;">
                    <input type="file" name="file" class="form-control form-control-sm" required>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-file-import"></i> Import Data Perkuliahan
                </button>
                {{-- <a class="btn btn-info" href="{{ route('admin.jadwal-perkuliahan.template') }}">
                    <i class="fas fa-download"></i> &nbsp; Download Template Excel
                </a>                 --}}
            </form>
        </div>
    </div>

@endcan
<div class="card">
    <div class="card-header">
        Daftar Jadwal Perkuliahan [Semester Genap 2024/2025]
    </div>
    <div class="d-flex justify-content-start mb-3 filt-keg">
        <form action="{{ route('admin.jadwal-perkuliahan.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="form-group mb-0">
                <select name="hari" id="hari" class="form-control select2">
                    <option value="">-- Pilih Hari --</option>
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
            <button type="submit" class="btn btn-primary ms-3">Filter</button>
            <a href="{{ route('admin.jadwal-perkuliahan.index') }}" class="btn btn-secondary">Reset Filter</a>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-jadwals">
                <thead>
                    <tr>
                        <th style="text-align:center;" width="10"></th>
                        <th style="text-align:center;" width="15">ID</th>
                        <th style="text-align:center;">Tipe</th>
                        <th style="text-align:center;">Mata Kuliah</th>
                        <th style="text-align:center;">Prodi</th>
                        <th style="text-align:center;">Ruangan</th>
                        <th style="text-align:center;">Hari</th>
                        <th style="text-align:center;">Waktu Mulai</th>
                        <th style="text-align:center;">Waktu Selesai</th>
                        <th style="text-align:center;">Berlaku Mulai</th>
                        <th style="text-align:center;">Berlaku Sampai</th>
                        <th style="text-align:center;" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwals as $jadwal)
                        <tr data-entry-id="{{ $jadwal->id }}">
                            <td>

                            </td>
                            <td style="text-align:center;">
                                {{ $jadwal->id }}
                            </td>
                            <td style="text-align:center;">
                                {{ $jadwal->tipe }}
                            </td>
                            <td style="text-align:center;">
                                {{ $jadwal->mata_kuliah }}
                            </td>
                            <td style="text-align:center;">
                                {{ $jadwal->program_studi }}
                            </td>
                            <td style="text-align:center;">
                                {{ $jadwal->ruangan->nama ?? '' }}
                            </td>
                            <td style="text-align:center;">
                                {{ $jadwal->hari }}
                            </td>
                            <td style="text-align:center;">
                                {{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') }}
                            </td>
                            <td style="text-align:center;">
                                {{ \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H:i') }}
                            </td>
                            <td style="text-align:center;">
                                {{ \Carbon\Carbon::parse($jadwal->berlaku_mulai)->format('j F Y') }}
                            </td>
                            <td style="text-align:center;">
                                {{ \Carbon\Carbon::parse($jadwal->berlaku_sampai)->format('j F Y') }}
                            </td>
                            <td style="text-align:center;">
                                @can('kuliah_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.jadwal-perkuliahan.show', $jadwal->id) }}">
                                        <i class="fas fa-fw fa-search"></i>
                                    </a>
                                @endcan

                                @can('kuliah_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.jadwal-perkuliahan.edit', $jadwal->id) }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>
                                @endcan

                                @can('kuliah_delete')
                                    <form id="delete-form-{{ $jadwal->id }}" action="{{ route('admin.jadwal-perkuliahan.destroy', $jadwal->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete({{ $jadwal->id }})">
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
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.jadwal-perkuliahan.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
                var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                    return $(entry).data('entry-id');
                });

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak ada data yang dipilih!',
                        text: 'Pilih setidaknya satu jadwal untuk dihapus.',
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

        $.extend(true, $.fn.dataTable.defaults, {
            orderCellsTop: true,
            order: [[1, 'desc']],
            pageLength: 50,
        });

        let table = $('.datatable-jadwals:not(.ajaxTable)').DataTable({ buttons: dtButtons });

        $('a[data-toggle="tab"]').on('shown.bs.tab click', function (e) {
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });

    });
</script>
@endsection
