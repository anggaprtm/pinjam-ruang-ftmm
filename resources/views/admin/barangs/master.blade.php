@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold mb-0">Master Barang</h3>

    <div class="d-flex gap-2">
        @can('barang_create')
            <a class="btn btn-info" href="{{ route('admin.barangs.index') }}">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        @endcan

        @can('barang_create')
            <a class="btn btn-success" href="{{ route('admin.barangs.create') }}">
                <i class="fas fa-plus-circle me-2"></i> Tambah Barang
            </a>
        @endcan
    </div>
</div>


{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Barang">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th><i class="fas fa-box"></i> Nama Barang</th>
                        <th class="text-center"><i class="fas fa-layer-group"></i> Stok</th>
                        <th class="text-center"><i class="fas fa-align-left"></i> Deskripsi</th>
                        <th class="text-center actions-cell" style="width:140px;"><i class="fas fa-cogs"></i> Aksi</th>
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
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)

    let table = $('.datatable-Barang').DataTable({
        buttons: dtButtons,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.barangs.master') }}",
            data: function (d) {
                // kalau ada filter master barang, tambahin di sini nanti
            }
        },
        columns: [
            { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false, defaultContent: '' },

            { 
                data: 'nama_barang', 
                name: 'nama_barang',
                render: function(data, type, row) {
                    return `<div class="kegiatan-title-cell">${data || '-'}</div>`;
                },
                createdCell: function(td) {
                    $(td).attr('data-label', 'Nama Barang');
                }
            },

            {
                data: 'stok',
                name: 'stok',
                className: 'text-center',
                render: function(data, type, row) {
                    let stok = parseInt(data || 0);

                    let cls = 'badge-stok-aman';
                    let label = stok;

                    if (stok <= 0) {
                        cls = 'badge-stok-habis';
                        label = 'Habis';
                    } else if (stok <= 3) {
                        cls = 'badge-stok-menipis';
                        label = `Menipis (${stok})`;
                    } else {
                        cls = 'badge-stok-aman';
                        label = `${stok}`;
                    }

                    return `<span class="badge-stok ${cls}">${label}</span>`;
                },
                createdCell: function(td) {
                    $(td).attr('data-label', 'Stok');
                }
            },


            {
                data: 'deskripsi',
                name: 'deskripsi',
                defaultContent: '-',
                createdCell: function(td) {
                    $(td).attr('data-label', 'Deskripsi');
                }
            },

            { 
                data: 'actions',
                name: 'actions',
                className: 'text-center actions-cell',
                orderable: false,
                searchable: false,
                createdCell: function(td) {
                    $(td).attr('data-label', 'Aksi');
                }
            }
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

    $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    $('.datatable-Barang').on('draw.dt', function () {
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

});
</script>
@endsection
