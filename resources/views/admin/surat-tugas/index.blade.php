@extends('layouts.admin')

@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-nowrap"><i class="fas fa-file-signature me-2"></i> Data Surat Tugas</h3>
    @can('surat_tugas_create')
        <a class="btn btn-success" href="{{ route('admin.surat-tugas.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Buat Surat Tugas
        </a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-SuratTugas" style="width:100%">
                <thead>
                    <tr>
                        <th width="10"></th> {{-- Checkbox --}}
                        <th width="5%">#</th>
                        <th><i class="fas fa-hashtag me-1"></i>Nomor & Perihal</th>
                        <th><i class="far fa-calendar-alt me-1"></i>Tanggal Surat</th>
                        <th><i class="fas fa-users me-1"></i>Yang Ditugaskan</th>
                        <th><i class="fas fa-info-circle me-1"></i>Keperluan</th>
                        <th><i class="fas fa-user-tie me-1"></i>Penandatangan</th>
                        <th class="text-center" style="width: 150px;"><i class="fas fa-cogs me-1"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    // 1. Persiapan Tombol Standar DataTables
    let dtButtons = getStandardDtButtons();
      
    // 2. Tambahkan Tombol Hapus Massal (Jika Diizinkan)
    @can('surat_tugas_delete')
    let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
    let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.surat-tugas.massDestroy') }}", // Pastikan route ini ada jika ingin fitur hapus massal
        className: 'btn-danger',
        action: function (e, dt, node, config) {
            var ids = $.map(dt.rows({ selected: true }).data(), function (entry) {
                return entry.id; // Mengambil ID dari data row server-side
            });

            if (ids.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada data yang dipilih', 'warning')
                return;
            }

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data arsip surat yang dipilih akan dihapus permanen!",
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
                        $('.datatable-SuratTugas').DataTable().ajax.reload();
                        Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                    });
                }
            })
        }
    }
    let deleteIndex = dtButtons.length;
    dtButtons.push(deleteButton);
    @endcan

    // 3. Inisialisasi DataTables
    let table = $('.datatable-SuratTugas').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.surat-tugas.index') }}",
        buttons: dtButtons,
        columns: [
            { data: 'placeholder', name: 'placeholder', orderable: false, searchable: false, defaultContent: '' }, // Checkbox
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nomor_surat', name: 'nomor_surat' },
            { data: 'tanggal_surat', name: 'tanggal_surat' },
            { data: 'pegawai_list', name: 'pegawai_list', orderable: false },
            { data: 'isi_tugas', name: 'isi_tugas', orderable: false },
            { data: 'nama_penandatangan', name: 'nama_penandatangan' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[ 2, 'desc' ]], // Urutkan default by nomor_surat / waktu terbaru
        pageLength: 50,
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
            emptyTable: "Belum ada data surat tugas",
            processing: '<i class="fas fa-circle-notch fa-spin me-2"></i>Memuat...'
        },   
        columnDefs: [
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
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
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    table.on('select deselect', function () {
        let selectedRows = table.rows({ selected: true }).count();
        table.button(2).enable(selectedRows > 0); // Salin
        table.button(3).enable(selectedRows > 0); // CSV
        table.button(4).enable(selectedRows > 0); // Excel
        table.button(5).enable(selectedRows > 0); // PDF
        table.button(6).enable(selectedRows > 0); // Print
        if (typeof deleteIndex !== 'undefined') table.button(deleteIndex).enable(selectedRows > 0);
    });

    // 4. Tangkap event hapus satuan (Single Delete) via Delegasi
    // Gunakan document.on agar bekerja pada elemen yang di-render server-side
    $(document).on('submit', '.form-delete', function(e) {
        e.preventDefault(); 
        let form = this;

        Swal.fire({
            title: 'Hapus arsip ini?',
            text: "Data surat tugas yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); 
            }
        });
    });
});

// 5. Fungsi SweetAlert untuk Input Nomor Surat via AJAX
function openNomorModal(id, currentNomor) {
    Swal.fire({
        title: 'Input Nomor Surat',
        html: '<p class="text-muted small">Masukkan nomor surat resmi yang didapatkan dari e-office Universitas.</p>',
        input: 'text',
        inputValue: currentNomor || '',
        inputPlaceholder: 'Contoh: 1285/B/DST/UN3.FTMM/KP.10.00/2026',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-save me-1"></i> Simpan',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value.trim()) {
                return 'Nomor surat tidak boleh kosong!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                // Sesuaikan URL ini dengan struktur route kamu. Default-nya biasanya seperti ini:
                url: "/admin/surat-tugas/" + id + "/update-nomor", 
                type: 'POST', // atau PATCH, tergantung settingan route kamu
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    nomor_surat: result.value
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Nomor surat berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    // Reload tabel otomatis
                    $('.datatable-SuratTugas').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan nomor surat.', 'error');
                }
            });
        }
    });
}
</script>
@endsection