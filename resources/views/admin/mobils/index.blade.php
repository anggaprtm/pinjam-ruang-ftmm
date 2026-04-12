@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-nowrap"><i class="fas fa-car me-2"></i> Daftar Kendaraan Dinas</h3>
    <div class="ms-auto">
        @can('mobil_create')
            <a class="btn btn-success shadow-sm" href="{{ route('admin.mobils.create') }}">
                <i class="fas fa-plus-circle me-2"></i> Tambah Mobil
            </a>
        @endcan
    </div>
</div>

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Mobil">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th><i class="fas fa-car-side me-1"></i> Nama Mobil</th>
                        <th class="text-center"><i class="fas fa-closed-captioning me-1"></i> Plat Nomor</th>
                        <th class="text-center"><i class="fas fa-palette me-1"></i> Warna</th>
                        <th class="text-center"><i class="fas fa-info-circle me-1"></i> Status</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs me-1"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mobils as $key => $mobil)
                        <tr data-entry-id="{{ $mobil->id }}">
                            <td></td> {{-- Checkbox --}}
                            <td data-label="Nama Mobil">
                                <div class="kegiatan-title-cell">{{ $mobil->nama_mobil }}</div>
                            </td>
                            <td data-label="Plat Nomor" class="text-center">
                                <span class="badge bg-dark shadow-sm px-3 py-2 fs-6">{{ $mobil->plat_nomor }}</span>
                            </td>
                            <td data-label="Warna" class="text-center">{{ $mobil->warna ?? '-' }}</td>
                            <td data-label="Status" class="text-center">
                                @if($mobil->status == 'tersedia')
                                    <span class="badge bg-success shadow-sm px-2 py-1">Tersedia</span>
                                @elseif($mobil->status == 'dipakai')
                                    <span class="badge bg-danger shadow-sm px-2 py-1">Sedang Dipakai</span>
                                @else
                                    <span class="badge bg-warning text-dark shadow-sm px-2 py-1">Maintenance</span>
                                @endif
                            </td>
                            <td data-label="Aksi" class="text-center actions-cell">
                                <div class="btn-group shadow-sm">
                                    @can('mobil_show')
                                        <a class="btn btn-sm btn-info" href="{{ route('admin.mobils.show', $mobil->id) }}" title="Detail">
                                            <i class="fas fa-eye text-white"></i>
                                        </a>
                                    @endcan
                                    @can('mobil_edit')
                                        <a class="btn btn-sm btn-success" href="{{ route('admin.mobils.edit', $mobil->id) }}" title="Edit">
                                            <i class="fas fa-edit text-white"></i>
                                        </a>
                                    @endcan
                                    @can('mobil_delete')
                                        <form action="{{ route('admin.mobils.destroy', $mobil->id) }}" method="POST" 
                                            class="form-delete" style="display: inline-block; margin:0;">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger border-start-0" title="Hapus" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                <i class="fas fa-trash text-white"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
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
        // Salin tombol default dari helper standar
        let dtButtons = getStandardDtButtons();
        
        // Tambahkan tombol hapus massal jika diizinkan
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
                    Swal.fire('Peringatan', 'Tidak ada data yang dipilih', 'warning')
                    return
                }

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data mobil yang dipilih akan dihapus!",
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
                            data: { ids: ids, _method: 'DELETE' }
                        }).done(function () { location.reload() })
                    }
                })
            }
        }
        let deleteIndex = dtButtons.length;
        dtButtons.push(deleteButton)
        @endcan

        // Inisialisasi DataTables
        let table = $('.datatable-Mobil').DataTable({
            buttons: dtButtons,
            language: {
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                lengthMenu: "Tampilkan _MENU_ data",
                search: "Cari:",
                paginate: {
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                },
                zeroRecords: "Tidak ada data ditemukan",
                emptyTable: "Tidak ada data tersedia",
                processing: "Memuat..."
            },   
            order: [[ 1, 'asc' ]], // Order by Nama Mobil
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
            dom: "<'dt-top-row'<'dt-top-left'l><'dt-top-center'B><'dt-top-right'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
        
        $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });

        // Kontrol tombol export/salin/hapus agar aktif jika ada baris yang dipilih
        table.on('select deselect', function () {
            let selectedRows = table.rows({ selected: true }).count();
            table.button(2).enable(selectedRows > 0); // Salin
            table.button(3).enable(selectedRows > 0); // CSV
            table.button(4).enable(selectedRows > 0); // Excel
            table.button(5).enable(selectedRows > 0); // PDF
            table.button(6).enable(selectedRows > 0); // Print
            if (typeof deleteIndex !== 'undefined') table.button(deleteIndex).enable(selectedRows > 0);
        });
    });
</script>

<script>
$(document).ready(function() {
    // Tangkap event submit pada form dengan class 'form-delete'
    $('.form-delete').on('submit', function(e) {
        e.preventDefault(); // Hentikan form agar tidak langsung submit
        let form = this;

        Swal.fire({
            title: 'Hapus kendaraan ini?',
            text: "Data mobil yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Lanjutkan submit form jika user klik 'Ya'
            }
        });
    });
});
</script>
@endsection