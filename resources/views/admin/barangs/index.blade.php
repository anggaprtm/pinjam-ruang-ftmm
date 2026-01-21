@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">Rekap Peminjaman Barang</h3>
    @can('barang_create')
        <a class="btn btn-success" href="{{ route('admin.barangs.master') }}">
            Master Barang <i class="fas fa-arrow-right me-2"></i>
        </a>
    @endcan
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group" aria-label="Toggle Mode">
        <button type="button" class="btn btn-outline-info" id="btnModeBarang" data-mode="barang">
            <i class="fas fa-box"></i> Per Barang
        </button>
        <button type="button" class="btn btn-outline-info" id="btnModeKegiatan" data-mode="kegiatan">
            <i class="fas fa-calendar"></i> Per Kegiatan
        </button>
    </div>
</div>


{{-- Rekap Peminjaman --}}
<div class="filter-bar">
    <form id="formFilterBarang" onsubmit="return false;">
        <div class="row g-3 align-items-end">

            {{-- Filter Kegiatan --}}
            <div class="col-md-4">
                <label for="filter_kegiatan_id" class="form-label fw-bold">Filter Kegiatan:</label>
                <select id="filter_kegiatan_id" class="form-control select2">
                    <option value="">-- Semua Kegiatan --</option>
                    @foreach($kegiatans as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kegiatan }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Barang --}}
            <div class="col-md-4">
                <label for="filter_barang_id" class="form-label fw-bold">Filter Barang:</label>
                <select id="filter_barang_id" class="form-control select2">
                    <option value="">-- Semua Barang --</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_barang }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Peminjam --}}
            <div class="col-md-3">
                <label for="filter_user_id" class="form-label fw-bold">Filter Peminjam:</label>
                <select id="filter_user_id" class="form-control select2">
                    <option value="">-- Semua Peminjam --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol --}}
            <div class="col-md-1">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary w-100" id="btnApplyFilter">
                        Filter
                    </button>
                    <button type="button" class="btn btn-secondary w-100" id="btnResetFilter">
                        Reset
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
                {{-- MODE: Per Barang --}}
        <div id="wrapModeBarang">
            <div class="table-responsive">
                <table class="modern-table datatable datatable-BarangDipinjam w-100">
                    <thead>
                        <tr>
                            <th width="10"></th>
                            <th><i class="fas fa-box"></i> Nama Barang</th>
                            <th class="text-center"><i class="fas fa-sort-numeric-up"></i> Jumlah</th>
                            <th><i class="fas fa-calendar-alt"></i> Kegiatan</th>
                            <th><i class="fas fa-user"></i> Peminjam</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        {{-- MODE: Per Kegiatan --}}
        <div id="wrapModeKegiatan" style="display:none;">
            <div class="table-responsive">
                <table class="modern-table datatable datatable-KegiatanDipinjam w-100">
                    <thead>
                        <tr>
                            <th width="10"></th>
                            <th><i class="fas fa-calendar-alt"></i> Kegiatan</th>
                            <th><i class="fas fa-box"></i> Barang Dipinjam</th>
                            <th class="text-center"><i class="fas fa-list"></i> Total Item</th>
                            <th><i class="fas fa-user"></i> Peminjam</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>     
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {

    // ========== INIT SELECT2 ==========
    $('.select2').select2({ width: '100%' });

    // ========== VARIABLES ==========
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);

    let currentMode = 'barang'; // default
    let tableBarang = null;
    let tableKegiatan = null;

    // ========== HELPER: BUILD CONTROL ROW ==========
    function buildControlsRow(tableSelector) {
        let wrapper = $(tableSelector).closest('.dataTables_wrapper');
        let length = wrapper.find('.dataTables_length');
        let filter = wrapper.find('.dataTables_filter');
        let buttons = wrapper.find('.dt-buttons');

        // supaya gak dobel
        if (!wrapper.find('.dt-controls-row').length) {
            let controlsRow = $('<div class="dt-controls-row"></div>');
            let leftCol = $('<div class="dt-controls-left"></div>').append(length).append(buttons);
            let rightCol = $('<div class="dt-controls-right"></div>').append(filter);
            controlsRow.append(leftCol).append(rightCol);
            wrapper.prepend(controlsRow);
        }
    }

    // ========== DATATABLE: MODE PER BARANG ==========
    function initTableBarang() {
        if (tableBarang) return;

        tableBarang = $('.datatable-BarangDipinjam').DataTable({
            buttons: dtButtons,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.barangs.index') }}",
                data: function (d) {
                    d.mode = 'barang';
                    d.kegiatan_id = $('#filter_kegiatan_id').val();
                    d.barang_id   = $('#filter_barang_id').val();
                    d.user_id     = $('#filter_user_id').val();
                }
            },
            columns: [
                { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false, defaultContent: '' },

                {
                    data: 'nama_barang',
                    name: 'barangs.nama_barang',
                    render: function(data) {
                        return `<div class="kegiatan-title-cell">${data || '-'}</div>`;
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Nama Barang');
                    }
                },

                {
                    data: 'jumlah',
                    name: 'barang_kegiatan.jumlah',
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="badge-stok badge-stok-aman">${data ?? 0}</span>`;
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Jumlah');
                    }
                },

                {
                    data: 'nama_kegiatan',
                    name: 'kegiatan.nama_kegiatan',
                    render: function(data, type, row) {
                        let kegiatan = data || '-';

                        if (row.kegiatan_id) {
                            kegiatan = `
                                <a href="${row.kegiatan_url}" class="text-decoration-none fw-bold">
                                    ${kegiatan}
                                </a>
                            `;
                        } else {
                            kegiatan = `<div class="fw-bold">${kegiatan}</div>`;
                        }

                        return `
                            <div class="kegiatan-title-cell">${kegiatan}</div>
                            <div class="kegiatan-sub-cell">Mulai: ${row.waktu_mulai_formatted ?? '-'}</div>
                            <div class="kegiatan-sub-cell">Selesai: ${row.waktu_selesai_formatted ?? '-'}</div>
                        `;
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Kegiatan');
                    }
                },

                {
                    data: 'nama_peminjam',
                    name: 'users.name',
                    render: function(data) {
                        return data || '-';
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Peminjam');
                    }
                },
            ],

            orderCellsTop: true,
            pageLength: 10,

            columnDefs: [{
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            }],

            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
        });

        // rapihin UI control row
        $('.datatable-BarangDipinjam').on('draw.dt', function () {
            buildControlsRow('.datatable-BarangDipinjam');
        });

        buildControlsRow('.datatable-BarangDipinjam');
    }

    // ========== DATATABLE: MODE PER KEGIATAN ==========
    function initTableKegiatan() {
        if (tableKegiatan) return;

        tableKegiatan = $('.datatable-KegiatanDipinjam').DataTable({
            buttons: dtButtons,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.barangs.index') }}",
                data: function (d) {
                    d.mode = 'kegiatan';
                    d.kegiatan_id = $('#filter_kegiatan_id').val();
                    d.barang_id   = $('#filter_barang_id').val();
                    d.user_id     = $('#filter_user_id').val();
                }
            },
            columns: [
                { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false, defaultContent: '' },

                {
                    data: 'nama_kegiatan',
                    name: 'kegiatan.nama_kegiatan',
                    render: function(data, type, row) {
                        let kegiatan = data || '-';

                        if (row.kegiatan_id) {
                            kegiatan = `
                                <a href="${row.kegiatan_url}" class="text-decoration-none fw-bold">
                                    ${kegiatan}
                                </a>
                            `;
                        } else {
                            kegiatan = `<div class="fw-bold">${kegiatan}</div>`;
                        }

                        return `
                            <div class="kegiatan-title-cell">${kegiatan}</div>
                            <div class="kegiatan-sub-cell">Mulai: ${row.waktu_mulai_formatted ?? '-'}</div>
                            <div class="kegiatan-sub-cell">Selesai: ${row.waktu_selesai_formatted ?? '-'}</div>
                        `;
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Kegiatan');
                    }
                },

                {
                    data: 'daftar_barang',
                    name: 'barangs.nama_barang',
                    render: function(data) {
                        return `<div style="white-space:normal;">${data || '-'}</div>`;
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Barang Dipinjam');
                    }
                },

                {
                    data: 'total_item',
                    name: 'total_item',
                    searchable: false,
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="badge-stok badge-stok-aman">${data ?? 0}</span>`;
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Total Item');
                    }
                },

                {
                    data: 'nama_peminjam',
                    name: 'users.name',
                    render: function(data) {
                        return data || '-';
                    },
                    createdCell: function(td) {
                        $(td).attr('data-label', 'Peminjam');
                    }
                },
            ],

            orderCellsTop: true,
            pageLength: 10,

            columnDefs: [{
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            }],

            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
        });

        $('.datatable-KegiatanDipinjam').on('draw.dt', function () {
            buildControlsRow('.datatable-KegiatanDipinjam');
        });

        buildControlsRow('.datatable-KegiatanDipinjam');
    }

    // ========== APPLY MODE (SHOW / HIDE TABLES) ==========
    function applyModeUI(mode) {
        currentMode = mode;

        if (mode === 'barang') {
            $('#wrapModeBarang').show();
            $('#wrapModeKegiatan').hide();

            $('#btnModeBarang').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btnModeKegiatan').removeClass('btn-primary').addClass('btn-outline-primary');

            initTableBarang();
            tableBarang.columns.adjust();
            tableBarang.ajax.reload();

        } else {
            $('#wrapModeBarang').hide();
            $('#wrapModeKegiatan').show();

            $('#btnModeKegiatan').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btnModeBarang').removeClass('btn-primary').addClass('btn-outline-primary');

            initTableKegiatan();
            tableKegiatan.columns.adjust();
            tableKegiatan.ajax.reload();
        }
    }

    // ========== EVENT: TOGGLE MODE ==========
    $('#btnModeBarang').on('click', function () {
        applyModeUI('barang');
    });

    $('#btnModeKegiatan').on('click', function () {
        applyModeUI('kegiatan');
    });

    // ========== EVENT: FILTER APPLY ==========
    $('#btnApplyFilter').on('click', function () {
        if (currentMode === 'barang') {
            initTableBarang();
            tableBarang.ajax.reload();
        } else {
            initTableKegiatan();
            tableKegiatan.ajax.reload();
        }
    });

    // ========== EVENT: AUTO FILTER ON CHANGE ==========
    $('#filter_kegiatan_id, #filter_barang_id, #filter_user_id').on('change', function () {
        if (currentMode === 'barang') {
            if (tableBarang) tableBarang.ajax.reload();
        } else {
            if (tableKegiatan) tableKegiatan.ajax.reload();
        }
    });

    // ========== EVENT: RESET FILTER ==========
    $('#btnResetFilter').on('click', function () {
        $('#filter_kegiatan_id').val('').trigger('change');
        $('#filter_barang_id').val('').trigger('change');
        $('#filter_user_id').val('').trigger('change');

        if (currentMode === 'barang') {
            if (tableBarang) tableBarang.ajax.reload();
        } else {
            if (tableKegiatan) tableKegiatan.ajax.reload();
        }
    });

    // ========== TAB SUPPORT (kalau ada tab bootstrap) ==========
    $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    // ========== INITIAL LOAD ==========
    applyModeUI('barang');

});
</script>

@endsection
