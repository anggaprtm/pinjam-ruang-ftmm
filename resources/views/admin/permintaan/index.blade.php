@extends('layouts.admin')
@section('content')

{{-- HEADER --}}
<div class="d-flex align-items-center mb-4">
    <h3 class="font-weight-bold mb-0">Daftar Permintaan Layanan</h3>
    <div class="ms-auto">
        <a class="btn btn-success" href="{{ route('admin.permintaan-kegiatan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Ajukan Baru
        </a>
    </div>
</div>

{{-- FILTER BAR --}}
<div class="filter-bar mb-4">
    <form action="" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Filter Tanggal:</label>
                <input type="date" id="tanggal_mulai" class="form-control">
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button type="button" id="filter-btn" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <button type="button" id="reset-btn" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- TABEL MODERN --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Permintaan w-100">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th><i class="fas fa-clipboard-list me-1"></i> Kegiatan</th>
                        <th><i class="fas fa-calendar-alt me-1"></i> Waktu Pelaksanaan</th>
                        <th class="text-center"><i class="fas fa-door-open me-1"></i> Ruang</th>
                        <th class="text-center"><i class="fas fa-utensils me-1"></i> Konsumsi</th>
                        <th class="text-center"><i class="fas fa-info-circle me-1"></i> Status</th>
                        <th class="text-center" width="100"><i class="fas fa-cogs me-1"></i> Aksi</th>
                    </tr>
                </thead>
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
    
    // Tombol Salin/Excel/dll (Copy dari Kegiatan jika perlu)
    // dtButtons.push(...) 

    let table = $('.datatable-Permintaan').DataTable({
        buttons: dtButtons,
        processing: true,
        serverSide: true,
        retrieve: true,
        aaSorting: [],
        ajax: {
            url: "{{ route('admin.permintaan-kegiatan.index') }}",
            data: function (d) {
                d.tanggal_mulai = $('#tanggal_mulai').val();
            }
        },
        columns: [
            { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false },
            { 
                data: 'nama_kegiatan', 
                name: 'nama_kegiatan',
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Kegiatan');
                }
            },
            { 
                data: 'tanggal_kegiatan', 
                name: 'tanggal_kegiatan',
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Waktu');
                } 
            },
            { 
                data: 'status_ruang', 
                name: 'status_ruang', 
                className: 'text-center',
                // Render sudah dihandle Controller (mengeluarkan HTML string)
                // Kita cuma perlu createdCell untuk mobile label
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Ruang');
                }
            },
            { 
                data: 'status_konsumsi', 
                name: 'status_konsumsi', 
                className: 'text-center',
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Konsumsi');
                }
            },
            { 
                data: 'status_permintaan', 
                name: 'status_permintaan', 
                className: 'text-center',
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Status');
                }
            },
            { 
                data: 'actions', 
                name: 'actions', 
                className: 'text-center', 
                orderable: false, 
                searchable: false,
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Aksi');
                }
            }
        ],
        orderCellsTop: true,
        order: [[ 1, 'desc' ]],
        pageLength: 10,
    });

    $('#filter-btn').on('click', function() {
    table.draw();
    });

    // Tombol Reset
    $('#reset-btn').on('click', function() {
        $('#tanggal_mulai').val(''); // Kosongkan input tanggal
        table.draw(); // Reload tabel tanpa filter
    });
});
</script>
@endsection