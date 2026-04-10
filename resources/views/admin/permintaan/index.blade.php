@extends('layouts.admin')
@section('content')

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold mb-0">Daftar Permintaan Layanan</h3>
    <div class="ms-auto">
        <a class="btn btn-success shadow-sm" href="{{ route('admin.permintaan-kegiatan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Ajukan Baru
        </a>
    </div>
</div>

{{-- FILTER BAR --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form action="" method="GET" class="row g-2 align-items-end" onsubmit="return false;">
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1 fw-semibold">Filter Tanggal Kegiatan</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i></span>
                    <input type="date" id="tanggal_mulai" class="form-control border-start-0 ps-0">
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="button" id="filter-btn" class="btn btn-sm btn-primary shadow-sm px-3">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <button type="button" id="reset-btn" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </button>
            </div>
        </form>
    </div>
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
                        <th><i class="fas fa-clock me-1"></i> Waktu Pelaksanaan</th>
                        <th class="text-center"><i class="fas fa-door-open me-1"></i> Ruang</th>
                        <th class="text-center"><i class="fas fa-utensils me-1"></i> Konsumsi</th>
                        <th class="text-center"><i class="fas fa-info-circle me-1"></i> Status</th>
                        <th class="text-center" style="width: 120px;"><i class="fas fa-cogs me-1"></i> Aksi</th>
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
    let dtButtons = getStandardDtButtons();

    let table = $('.datatable-Permintaan').DataTable({
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
                createdCell: function(td) { $(td).attr('data-label', 'Kegiatan'); }
            },
            { 
                data: 'tanggal_kegiatan', 
                name: 'tanggal_kegiatan',
                createdCell: function(td) { $(td).attr('data-label', 'Waktu'); } 
            },
            { 
                data: 'status_ruang', 
                name: 'status_ruang', 
                className: 'text-center',
                createdCell: function(td) { $(td).attr('data-label', 'Ruang'); }
            },
            { 
                data: 'status_konsumsi', 
                name: 'status_konsumsi', 
                className: 'text-center',
                createdCell: function(td) { $(td).attr('data-label', 'Konsumsi'); }
            },
            { 
                data: 'status_permintaan', 
                name: 'status_permintaan', 
                className: 'text-center',
                createdCell: function(td) { $(td).attr('data-label', 'Status'); }
            },
            { 
                data: 'actions', 
                name: 'actions', 
                className: 'text-center actions-cell', 
                orderable: false, 
                searchable: false,
                createdCell: function(td) { $(td).attr('data-label', 'Aksi'); }
            }
        ],
        orderCellsTop: true,
        order: [[ 1, 'desc' ]],
        pageLength: 25,
        columnDefs: [{
            orderable: false,
            className: 'select-checkbox',
            targets: 0
        }],
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        dom: "<'dt-top-row'<'dt-top-left'l><'dt-top-center'B><'dt-top-right'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // Kontrol tombol export agar aktif jika ada checkbox yg dicentang
    table.on('select deselect', function () {
        let selectedRows = table.rows({ selected: true }).count();
        table.button(2).enable(selectedRows > 0); // Salin
        table.button(3).enable(selectedRows > 0); // CSV
        table.button(4).enable(selectedRows > 0); // Excel
        table.button(5).enable(selectedRows > 0); // PDF
        table.button(6).enable(selectedRows > 0); // Cetak
    });

    // Tombol Filter
    $('#filter-btn').on('click', function() {
        table.draw();
    });

    // Tombol Reset
    $('#reset-btn').on('click', function() {
        $('#tanggal_mulai').val(''); 
        table.draw(); 
    });

    // ========== EVENT: TOMBOL HAPUS AJAX (SWEETALERT) ==========
    // Karena tabel pakai AJAX, event diikat ke document agar selalu aktif
    $(document).on('click', '.js-delete-btn', function (e) {
        e.preventDefault();
        let deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Batalkan Permintaan?',
            text: "Data permintaan yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = $('<form>', { 'method': 'POST', 'action': deleteUrl });
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
                $('body').append(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection