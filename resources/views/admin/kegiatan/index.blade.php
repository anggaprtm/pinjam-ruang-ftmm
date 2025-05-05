@extends('layouts.admin')
@section('content')
@can('kegiatan_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.kegiatan.create') }}">
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
        <form action="{{ route('admin.kegiatan.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="form-group mb-0">
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}" placeholder="dd/mm/yyyy">
            </div>
            <button type="submit" class="btn btn-primary ms-3">Filter</button>
            <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-secondary">Reset Filter</a>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Kegiatan">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th width="15" style="text-align:center;">
                            {{ trans('cruds.kegiatan.fields.id') }}
                        </th>
                        <th style="text-align:center;">
                            {{ trans('cruds.kegiatan.fields.ruangan') }}
                        </th>
                        <th width="240" style="text-align:center;">
                            {{ trans('cruds.kegiatan.fields.nama_kegiatan') }}
                        </th>
                        <th style="text-align:center;">
                            {{ trans('cruds.kegiatan.fields.waktu_mulai') }}
                        </th>
                        <th style="text-align:center;">
                            {{ trans('cruds.kegiatan.fields.waktu_selesai') }}
                        </th>
                        <th style="text-align:center;">
                            Peminjam
                        </th>
                        @can('status_access')
                        <th style="text-align:center;">
                            Status
                        </th>
                        @endcan
                        <!-- <th width="150">
                            Keterangan
                        </th> -->
                        @can('persetujuan_access')
                        <th style="text-align:center;">
                            Persetujuan
                        </th>
                        @endcan
                        <th style="text-align:center;">
                            Komentar Pemroses
                        </th>
                        <th style="text-align:center;">
                            Waktu Verif
                        </th>
                        <th width="80" style="text-align:center;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kegiatan as $key => $kegiatan)
                        <tr data-entry-id="{{ $kegiatan->id }}"
                            @if(\Carbon\Carbon::parse($kegiatan->created_at)->diffInHours(now()) < 24) 
                                style="background-color:rgba(255, 255, 153, 0.32);" 
                            @endif>
                            <td>

                            </td>
                            <td style="text-align:center;">
                                {{ $kegiatan->id ?? '' }}
                            </td>
                            <td style="text-align:center;">
                                {{ $kegiatan->ruangan->nama ?? '' }}
                            </td>
                            <td>
                                {{ $kegiatan->nama_kegiatan ?? '' }}
                            </td>
                            <td style="text-align:center;">
                                {{ $kegiatan->waktu_mulai ?? '' }}
                            </td>
                            <td style="text-align:center;">
                                {{ $kegiatan->waktu_selesai ?? '' }}
                            </td>
                            <td style="text-align:center;">
                                {{ $kegiatan->user->name ?? '' }}
                            </td>
                            @can('status_access')
                            <td style="text-align:center;">
                                @if($kegiatan->status == 'belum_disetujui')
                                    <span class="badge badge-warning">Menunggu Verif Operator</span>
                                @elseif($kegiatan->status == 'verifikasi_sarpras')
                                    <span class="badge badge-warning">Menunggu Verif Akademik</span>
                                @elseif($kegiatan->status == 'verifikasi_akademik')
                                    <span class="badge badge-warning">Menunggu Verif Sarpras</span>
                                @elseif($kegiatan->status == 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($kegiatan->status == 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                            @endcan
                            <!-- <td>
                                {{ $kegiatan->deskripsi ?? '' }}
                            </td>   -->
                            @can('persetujuan_access')
                            <td style="text-align:center;">
                                @can('kegiatan_edit_status')
                                    @if ($kegiatan->status == 'belum_disetujui')
                                        <!-- Tombol Verifikasi Sarpras -->
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalVerifikasiSarpras{{ $kegiatan->id }}">
                                            Verifikasi Kegiatan
                                        </button>
                                        <!-- Modal Verifikasi Sarpras -->
                                        <div class="modal fade" id="modalVerifikasiSarpras{{ $kegiatan->id }}" tabindex="-1" aria-labelledby="modalVerifikasiSarprasLabel{{ $kegiatan->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.kegiatan.updateStatus', $kegiatan->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalVerifikasiSarprasLabel{{ $kegiatan->id }}">Verifikasi Kegiatan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="next">
                                                            <div class="form-group">
                                                                <label for="notes">Masukkan catatan (opsional)</label>
                                                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Masukkan catatan di sini"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Kirim</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @elseif ($kegiatan->status == 'verifikasi_sarpras')
                                        <!-- Tombol Verifikasi Akademik -->
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalVerifikasiAkademik{{ $kegiatan->id }}">
                                            Verifikasi Akademik
                                        </button>

                                        <!-- Modal Verifikasi Akademik -->
                                        <div class="modal fade" id="modalVerifikasiAkademik{{ $kegiatan->id }}" tabindex="-1" aria-labelledby="modalVerifikasiAkademikLabel{{ $kegiatan->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.kegiatan.updateStatus', $kegiatan->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalVerifikasiAkademikLabel{{ $kegiatan->id }}">Verifikasi Akademik</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="next">
                                                            <div class="form-group">
                                                                <label for="notes">Masukkan catatan (opsional)</label>
                                                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Masukkan catatan di sini"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Kirim & Verifikasi    </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Tombol Penolakan -->
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalTolak{{ $kegiatan->id }}">
                                            Tolak Kegiatan
                                        </button>

                                        <!-- Modal Penolakan -->
                                        <div class="modal fade" id="modalTolak{{ $kegiatan->id }}" tabindex="-1" aria-labelledby="modalTolakLabel{{ $kegiatan->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.kegiatan.updateStatus', $kegiatan->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTolakLabel{{ $kegiatan->id }}">Tolak Kegiatan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="reject">
                                                            <div class="form-group">
                                                                <label for="notes">Masukkan alasan penolakan</label>
                                                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Masukkan alasan di sini"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @elseif ($kegiatan->status == 'verifikasi_akademik')
                                        <!-- Tombol Setujui -->
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalSetujui{{ $kegiatan->id }}">
                                            Setujui
                                        </button>

                                        <!-- Modal Setujui -->
                                        <div class="modal fade" id="modalSetujui{{ $kegiatan->id }}" tabindex="-1" aria-labelledby="modalSetujuiLabel{{ $kegiatan->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.kegiatan.updateStatus', $kegiatan->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalSetujuiLabel{{ $kegiatan->id }}">Setujui Kegiatan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="next">
                                                            <div class="form-group">
                                                                <label for="notes">Masukkan catatan (opsional)</label>
                                                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Masukkan catatan di sini"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success">Kirim</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Tombol Penolakan -->
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalTolak{{ $kegiatan->id }}">
                                            Tolak Kegiatan
                                        </button>

                                        <!-- Modal Penolakan -->
                                        <div class="modal fade" id="modalTolak{{ $kegiatan->id }}" tabindex="-1" aria-labelledby="modalTolakLabel{{ $kegiatan->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.kegiatan.updateStatus', $kegiatan->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTolakLabel{{ $kegiatan->id }}">Tolak Kegiatan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="reject">
                                                            <div class="form-group">
                                                                <label for="notes">Masukkan alasan penolakan</label>
                                                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Masukkan alasan di sini"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @elseif ($kegiatan->status == 'disetujui')
                                        <span class="badge badge-success">Kegiatan Disetujui</span>
                                    @elseif ($kegiatan->status == 'ditolak')
                                        <span class="badge badge-danger">Kegiatan Ditolak</span>
                                    @endif
                                @endcan
                            </td>
                            @endcan
                            <td>{{ $kegiatan->notes ?? '-' }}</td>
                            <td>
                                @if ($kegiatan->status == 'belum_disetujui')
                                    <span>Verif Operator: -</span>
                                @elseif ($kegiatan->status == 'verifikasi_sarpras')
                                    <span>Verif Operator: {{ $kegiatan->verifikasi_sarpras_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i:s') : '-' }}</span>
                                @elseif ($kegiatan->status == 'verifikasi_akademik')    
                                    <span>Verif Operator: {{ $kegiatan->verifikasi_sarpras_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i:s') : '-' }}</span><br>
                                    <span>Verif AMA: {{ $kegiatan->verifikasi_akademik_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_akademik_at)->format('d/m/y H:i:s') : '-' }}</span>
                                @elseif ($kegiatan->status == 'disetujui')
                                    <span>Verif  Operator: {{ $kegiatan->verifikasi_sarpras_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i:s') : '-' }}</span><br>
                                    <span>Verif AMA: {{ $kegiatan->verifikasi_akademik_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_akademik_at)->format('d/m/y H:i:s') : '-' }}</span><br>
                                    <span>Disetujui: {{ $kegiatan->disetujui_at ? \Carbon\Carbon::parse($kegiatan->disetujui_at)->format('d/m/y H:i:s') : '-' }}</span>
                                @elseif ($kegiatan->status == 'ditolak')
                                    <span>Ditolak: {{ $kegiatan->ditolak_at ? \Carbon\Carbon::parse($kegiatan->ditolak_at)->format('d/m/y H:i:s') : '-' }}</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @can('kegiatan_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.kegiatan.show', $kegiatan->id) }}  ">
                                        <i class="fas fa-fw fa-search"></i>
                                    </a>
                                @endcan

                                @can('kegiatan_edit')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.kegiatan.edit', $kegiatan->id) }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>
                                @endcan

                                @can('kegiatan_delete')
                                    <form id="delete-form-{{ $kegiatan->id }}" action="{{ route('admin.kegiatan.destroy', $kegiatan->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete({{ $kegiatan->id }})">
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

        @can('kegiatan_delete')
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.kegiatan.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
                var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                    return $(entry).data('entry-id');
                });

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak ada data yang dipilih!',
                        text: 'Pilih setidaknya satu kegiatan untuk dihapus.',
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
                            text: 'Mohon tunggu, data sedang dihapus.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
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

        let table = $('.datatable-Kegiatan:not(.ajaxTable)').DataTable({ buttons: dtButtons });

        $('a[data-toggle="tab"]').on('shown.bs.tab click', function (e) {
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });

    });
</script>

@endsection
