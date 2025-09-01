@extends('layouts.admin')
@section('content')

{{-- Bagian Header & Filter --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">Daftar Kegiatan</h3>
    @can('kegiatan_create')
        <a class="btn btn-success" href="{{ route('admin.kegiatan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Tambah Kegiatan
        </a>
    @endcan
</div>

{{-- Filter Bar dengan Tambahan Filter --}}
<div class="filter-bar">
    <form action="{{ route('admin.kegiatan.index') }}" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="tanggal_mulai" class="form-label fw-bold">Filter Tanggal Mulai:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
            </div>
                               
            @if(!auth()->user()->hasRole('User'))
            <div class="col-md-3">
                <label for="user_id" class="form-label fw-bold">Filter Peminjam:</label>
                <select name="user_id" id="user_id" class="form-control select2">
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <label for="ruangan_id" class="form-label fw-bold">Filter Ruangan:</label>
                <select name="ruangan_id" id="ruangan_id" class="form-control select2">
                    @foreach($ruangans as $id => $nama)
                        <option value="{{ $id }}" {{ request('ruangan_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Tabel Modern dengan Fungsionalitas DataTables --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Kegiatan">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th><i class="fas fa-clipboard-list"></i>Kegiatan</th>
                        <th><i class="fas fa-door-open"></i>Ruangan</th>
                        <th><i class="fas fa-calendar-alt"></i>Jadwal</th>
                        <th class="text-center"><i class="fas fa-info-circle"></i>Status</th>
                        @can('persetujuan_access')
                            <th class="text-center"><i class="fas fa-tasks"></i> Persetujuan</th>
                        @endcan
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                   
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- SATU MODAL UNTUK SEMUA AKSI PERSETUJUAN --}}
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form id="modalForm" method="POST" action=""> {{-- Action akan diisi oleh JS --}}
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Konfirmasi Aksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalActionInput" value=""> {{-- Value akan diisi oleh JS --}}
                    
                    <p id="modalBodyText">Apakah Anda yakin ingin melanjutkan aksi ini?</p>
                    
                    <div class="form-group">
                        <label id="modalNotesLabel" for="modalNotesTextarea">Catatan (opsional)</label>
                        <textarea name="notes" id="modalNotesTextarea" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="modalSubmitButton" class="btn btn-primary">Kirim</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection

@section('scripts')
@parent
<script>
$(function () {

        $(document).on('click', '.js-open-modal', function () {
        const actionType = $(this).data('action-type');
        const kegiatanId = $(this).data('id');

        let config = {};
        const baseUrl = "{{ url('admin/kegiatan') }}/" + kegiatanId + "/update-status";

        // Reset modal ke default
        $('#modalNotesTextarea').prop('required', false);
        $('#modalBodyText').show();

        switch (actionType) {
            case 'verifikasi_sarpras':
                config = {
                    title: 'Verifikasi Kegiatan',
                    bodyText: 'Lanjutkan untuk memverifikasi kegiatan ini?',
                    actionValue: 'next',
                    submitText: 'Kirim',
                    submitClass: 'btn-primary',
                    notesLabel: 'Catatan (opsional)'
                };
                break;
            case 'verifikasi_akademik':
                config = {
                    title: 'Verifikasi Akademik',
                    bodyText: 'Lanjutkan untuk memverifikasi kegiatan ini?',
                    actionValue: 'next',
                    submitText: 'Kirim & Verifikasi',
                    submitClass: 'btn-primary',
                    notesLabel: 'Catatan (opsional)'
                };
                break;
            case 'setujui':
                config = {
                    title: 'Setujui Kegiatan',
                    bodyText: 'Apakah Anda yakin ingin menyetujui kegiatan ini?',
                    actionValue: 'next',
                    submitText: 'Ya, Setujui',
                    submitClass: 'btn-success',
                    notesLabel: 'Catatan (opsional)'
                };
                break;
            case 'tolak':
                config = {
                    title: 'Tolak Kegiatan',
                    bodyText: 'Mohon isi alasan penolakan di bawah ini.',
                    actionValue: 'reject',
                    submitText: 'Tolak Kegiatan',
                    submitClass: 'btn-danger',
                    notesLabel: 'Alasan Penolakan (wajib diisi)'
                };
                // Jadikan catatan wajib untuk penolakan
                $('#modalNotesTextarea').prop('required', true);
                break;
        }

        // Terapkan konfigurasi ke modal
        $('#modalForm').attr('action', baseUrl);
        $('#modalTitle').text(config.title);
        $('#modalBodyText').text(config.bodyText);
        $('#modalActionInput').val(config.actionValue);
        $('#modalNotesLabel').text(config.notesLabel);
        $('#modalSubmitButton').text(config.submitText)
            .removeClass('btn-primary btn-success btn-danger')
            .addClass(config.submitClass);
        
        // Tampilkan modal
        $('#approvalModal').modal('show');
    });
    // 1. KITA PERTAHANKAN LOGIKA TOMBOL HAPUS MASSAL DARI SKRIP LAMA ANDA
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);
    
    @can('kegiatan_delete')
    let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
    let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.kegiatan.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                // Perubahan kecil: Ambil ID dari data baris, bukan dari 'data-entry-id'
                var rowData = dt.row(entry).data();
                return rowData.id;
            });

            if (ids.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada data yang dipilih', 'warning');
                return;
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
                        data: { ids: ids, _method: 'DELETE' }
                    }).done(function () { 
                        // Perbaikan: Gunakan ajax.reload() agar lebih efisien
                        table.ajax.reload(); 
                        Swal.fire('Berhasil!', 'Data telah dihapus.', 'success');
                    });
                }
            });
        }
    };
    dtButtons.push(deleteButton);
    @endcan

    // 2. KITA GUNAKAN INISIALISASI SERVER-SIDE YANG BARU
    let table = $('.datatable-Kegiatan').DataTable({
        buttons: dtButtons, // <- Tombol hapus massal dimasukkan di sini
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.kegiatan.index') }}",
            data: function (d) {
                // Menambahkan nilai dari form filter ke request
                d.tanggal_mulai = $('#tanggal_mulai').val();
                d.user_id = $('#user_id').val();
                d.ruangan_id = $('#ruangan_id').val();
            }
        },
        columns: [
            { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false, defaultContent: '' },
            { 
                data: 'nama_kegiatan', 
                name: 'nama_kegiatan',
                render: function(data, type, row) {
                    let user_name = row.user ? row.user.name : '-';
                    let created_at_human = row.created_at_human;
                    let created_at_title = row.created_at_title;
                    
                    return `<div class="kegiatan-title-cell">${data}</div><div class="d-flex align-items-center mt-1"><div class="user-avatar"><i class="fas fa-user"></i></div><div><div class="kegiatan-sub-cell">${user_name}</div><div class="creation-timestamp" title="${created_at_title}">Dibuat: ${created_at_human}</div></div></div>`;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Kegiatan'); // 👈 Tambahan untuk mobile
                }
            },
            { 
                data: 'ruangan.nama', 
                name: 'ruangan.nama',
                defaultContent: '-',
                render: function(data, type, row) {
                    return `<span class="badge-ruangan">${data || '-'}</span>`;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Ruangan'); // 👈 Tambahan untuk mobile
                }
            },
            {
                data: 'waktu_mulai',
                name: 'waktu_mulai',
                render: function(data, type, row) {
                    return `<div class="kegiatan-sub-cell">Mulai: ${row.waktu_mulai_formatted}</div><div class="kegiatan-sub-cell">Selesai: ${row.waktu_selesai_formatted}</div>`;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Jadwal'); // 👈 Tambahan untuk mobile
                }
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-center',
                render: function(data, type, row) {
                    let statusClass = data.replace(/_/g, '-');
                    let statusText = '';
                    switch (data) {
                        case 'belum_disetujui': statusText = 'Menunggu Verifikasi Operator'; break;
                        case 'verifikasi_sarpras': statusText = 'Menunggu Verifikasi Akademik'; break;
                        case 'verifikasi_akademik': statusText = 'Menunggu Verifikasi Sarpras'; break;
                        case 'disetujui': statusText = 'Disetujui'; break;
                        case 'ditolak': statusText = 'Ditolak'; break;
                        default: statusText = data; break;
                    }
                    return `<span class="badge-status badge-status-${statusClass}">${statusText}</span>`;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'Status'); // 👈 Tambahan untuk mobile
                }      
            },
            @can('persetujuan_access')
            { data: 'persetujuan', 
              name: 'persetujuan',
              className: 'text-center', 
              orderable: false, 
              searchable: false,
              createdCell: function(td, cellData, rowData, row, col) {
                $(td).attr('data-label', 'Persetujuan'); // 👈 Tambahan untuk mobile
              }
            },
            @endcan
            { data: 'actions', 
              name: 'actions', 
              className: 'text-center actions-cell', 
              orderable: false, 
              searchable: false,
              createdCell: function(td, cellData, rowData, row, col) {
                $(td).attr('data-label', 'Aksi'); // 👈 Tambahan untuk mobile
              }
            }
            
            
        ],
        orderCellsTop: true,
        pageLength: 10,
        columnDefs: [ {
            orderable: false,
            className: 'select-checkbox',
            targets:   0
        } ],
        select: {
            style:    'os',
            selector: 'td:first-child'
        },
    });

    // 3. KITA PERTAHANKAN KODE UNTUK PERBAIKAN TAB
    $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });
    
    // 4. KITA PERTAHANKAN KODE UNTUK PENYESUAIAN LAYOUT
    $('.datatable-Kegiatan').on('draw.dt', function () {
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

    // Listener untuk tombol filter
$('#filter-btn').on('click', function(e) {
    e.preventDefault();
    table.draw(); // Muat ulang tabel dengan data filter baru
});

// Listener untuk tombol reset (opsional, tapi sangat membantu)
$('#reset-btn').on('click', function(e) {
    e.preventDefault();
    // Reset nilai form
    $('#filter-form').trigger("reset");
    // Muat ulang tabel
    table.draw();
});

$(document).on('click', '.js-delete-btn', function (e) {
    e.preventDefault();
    const deleteUrl = $(this).data('url');

    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {'x-csrf-token': _token}, // Pastikan variabel _token ada
                method: 'POST', // Method tetap POST karena kita akan spoofing DELETE
                url: deleteUrl,
                data: { _method: 'DELETE' }
            })
            .done(function () { 
                table.ajax.reload(); // Muat ulang tabel
                Swal.fire('Berhasil!', 'Data telah dihapus.', 'success');
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Opsi: Tampilkan pesan error jika gagal
                Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
            });
        }
    });
});

});
</script>
@endsection