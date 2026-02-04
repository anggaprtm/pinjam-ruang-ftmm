@extends('layouts.admin')
@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">Arsip Surat Undangan</h3>
    <a class="btn btn-success" href="{{ route('admin.surat-undangan.create') }}">
        <i class="fas fa-plus-circle me-2"></i> Buat Surat Baru
    </a>
</div>

{{-- Tabel Arsip --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Surat">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Nomor Surat & Perihal</th>
                        <th>Tanggal Surat</th>
                        <th>Tujuan (Yth.)</th>
                        <th>Agenda</th> {{-- KOLOM BARU --}}
                        <th>Penandatangan</th>
                        <th class="text-center" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data diisi via AJAX DataTables --}}
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

        let table = $('.datatable-Surat').DataTable({
            buttons: dtButtons,
            processing: true,
            serverSide: true,
            retrieve: true,
            aaSorting: [],
            ajax: "{{ route('admin.surat-undangan.index') }}",
            columns: [
                { data: 'placeholder', name: 'placeholder' },
                
                // 1. NOMOR & PERIHAL
                { 
                    data: 'nomor_surat', 
                    name: 'nomor_surat',
                    render: function(data, type, row) {
                        return '<div class="fw-bold text-primary">' + data + '</div>' +
                               '<div class="text-muted small">' + (row.hal_surat || '-') + '</div>';
                    }
                },
                
                // 2. TANGGAL
                { data: 'tanggal_surat', name: 'tanggal_surat' },
                
                // 3. TUJUAN
                { 
                    data: 'tujuan_surat', 
                    name: 'tujuan_surat',
                    render: function(data) {
                        try {
                            var list = JSON.parse(data);
                            if(list.length > 2) {
                                return list[0] + ', ' + list[1] + ', <span class="badge bg-secondary">+' + (list.length - 2) + ' lainnya</span>';
                            }
                            return list.join(', ');
                        } catch(e) {
                            return data;
                        }
                    }
                },

                // 4. AGENDA (KOLOM BARU)
                { 
                    data: 'agenda_acara', 
                    name: 'agenda_acara',
                    render: function(data) {
                        // Limit teks biar tabel gak kepanjangan kalau agendanya curhat
                        return data ? (data.length > 50 ? data.substr(0, 50) + '...' : data) : '-';
                    }
                },

                // 5. TTD
                { data: 'nama_penandatangan', name: 'nama_penandatangan' },
                
                // 6. AKSI
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[ 1, 'desc' ]],
            pageLength: 25,
            columnDefs: [{
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            }, {
                orderable: false,
                searchable: false,
                targets: -1
            }],
            select: {
                style:    'multi+shift',
                selector: 'td:first-child'
            },
            dom: 'lBfrtip'
        });

        $('.datatable-Surat').on('draw.dt', function () {
             // Logic layout tombol (biarkan default template)
        });
    });
</script>
@endsection