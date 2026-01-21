@extends('layouts.admin')
@section('content')

<div class="d-flex align-items-center mb-4">
    <h3 class="font-weight-bold mb-0">Logbook Driver & Kendaraan</h3>

    <div class="ms-auto">
        @can('riwayat_perjalanan_create')
            <a class="btn btn-success" href="{{ route('admin.riwayat-perjalanan.create') }}">
                <i class="fas fa-plus-circle me-2"></i> Input Jalan / Booking
            </a>
        @endcan
    </div>
</div>

{{-- ========== TABLE ONGOING / ON DUTY ========== --}}
@if(isset($ongoing) && $ongoing->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-road me-2"></i> Sedang Berlangsung
            </h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="modern-table mb-0">
                    <thead>
                        <tr>
                            <th>KENDARAAN</th>
                            <th>DRIVER</th>
                            <th>TUJUAN</th>
                            <th>WAKTU BERANGKAT</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ongoing as $row)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <span class="icon-circle icon-circle-sm">
                                                <i class="fas fa-car"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ $row->mobil->nama_mobil ?? '-' }}</span>
                                            @if(!empty($row->mobil->plat_nomor))
                                                <span class="plate-badge">{{ $row->mobil->plat_nomor }}</span>
                                            @endif

                                        </div>
                                    </div>
                                </td>


                                <td>
                                    @php
                                        $driverName = $row->driver->name ?? '-';
                                        $initial = strtoupper(substr(trim($driverName), 0, 1));
                                    @endphp

                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <span class="user-initial">{{ $initial }}</span>
                                        </div>
                                        <div class="fw-semibold text-dark">{{ $driverName }}</div>
                                    </div>
                                </td>


                                <td>
                                    <div class="d-flex align-items-start">
                                        <div>
                                            <div class="text-dark fw-bold">{{ $row->tujuan ?? '-' }}</div>
                                            <small class="text-muted">{{ $row->keperluan ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>


                                <td>
                                    @php
                                        $waktu = $row->waktu_mulai
                                            ? \Carbon\Carbon::parse($row->getRawOriginal('waktu_mulai'))->format('d M Y H:i')
                                            : '-';
                                    @endphp

                                    <span class="fw-semibold text-dark">{{ $waktu }}</span>
                                </td>


                                <td class="text-center">
                                    {{-- ✅ SweetAlert handled --}}
                                    <form action="{{ route('admin.riwayat-perjalanan.selesaikan', $row->id) }}"
                                          method="POST"
                                          class="d-inline js-selesaikan-tugas">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i> Selesai
                                        </button>
                                    </form>

                                    @can('riwayat_perjalanan_edit')
                                        <a href="{{ route('admin.riwayat-perjalanan.edit', $row->id) }}"
                                           class="btn btn-sm btn-info ms-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif


{{-- ========== DATATABLE: JADWAL + RIWAYAT ========== --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-list-alt me-2"></i> Jadwal Mendatang & Riwayat
        </h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table ajaxTable datatable datatable-Riwayat w-100">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th><i class="fas fa-info-circle me-2"></i> STATUS</th>
                        <th><i class="fas fa-calendar-alt me-2"></i> JADWAL</th>
                        <th><i class="fas fa-car me-2"></i> KENDARAAN</th>
                        <th><i class="fas fa-user me-2"></i> DRIVER</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i> TUJUAN</th>
                        <th class="text-center" style="width: 170px;"><i class="fas fa-cogs me-2"></i> AKSI</th>
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

    // pastikan global token ada
    if (typeof window._token === 'undefined') {
        window._token = '{{ csrf_token() }}'
    }

    // ======================
    // DATATABLE + MASS DELETE
    // ======================
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);

    @can('riwayat_perjalanan_delete')
    let deleteButton = {
        text: 'Hapus pilihan',
        className: 'btn-danger',
        action: function (e, dt, node, config) {

            let ids = $.map(dt.rows({ selected: true }).data(), function (entry) {
                return entry.id
            });

            if (ids.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada data dipilih',
                    text: 'Silahkan pilih data yang ingin dihapus terlebih dahulu.',
                });
                return;
            }

            Swal.fire({
                title: 'Yakin hapus data terpilih?',
                text: `Total: ${ids.length} data`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
            }).then((result) => {

                if (!result.isConfirmed) return;
                console.log("MASS DESTROY URL:", "{{ route('admin.riwayat-perjalanan.massDestroy') }}");

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: 'DELETE',
                    url: "{{ route('admin.riwayat-perjalanan.massDestroy') }}",
                    data: { ids: ids }
                })
                .done(function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data terpilih berhasil dihapus.',
                        timer: 1200,
                        showConfirmButton: false
                    });

                    $('.datatable-Riwayat').DataTable().ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menghapus'
                    });
                });

            });
        }
    }
    dtButtons.push(deleteButton);
    @endcan

    let table = $('.datatable-Riwayat').DataTable({
        buttons: dtButtons,
        processing: true,
        serverSide: true,
        retrieve: true,
        aaSorting: [],
        ajax: "{{ route('admin.riwayat-perjalanan.index') }}",
        columns: [
            { data: 'placeholder', name: 'placeholder' },
            { data: 'status', name: 'status' },
            { data: 'waktu_mulai', name: 'waktu_mulai' },
            { data: 'kendaraan', name: 'mobil.nama_mobil' },
            { data: 'driver_display', name: 'driver.name' },
            { data: 'tujuan', name: 'tujuan' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        orderCellsTop: true,
        order: [[ 2, 'desc' ]],
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

    // ======================
    // SWEETALERT: DELETE / MULAI
    // ======================

    // Delete per row (datatable)
    $(document).on('submit', '.js-delete-riwayat', function(e) {
        e.preventDefault();
        let form = this;

        Swal.fire({
            title: 'Yakin hapus data ini?',
            text: 'Data yang dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Mulai perjalanan (datatable)
    $(document).on('submit', '.js-mulai-perjalanan', function(e) {
        e.preventDefault();
        let form = this;

        Swal.fire({
            title: 'Mulai perjalanan sekarang?',
            text: 'Status akan berubah menjadi On Duty.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, mulai',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Selesaikan tugas (ongoing table)
    $(document).on('submit', '.js-selesaikan-tugas', function(e) {
        e.preventDefault();
        let form = this;

        Swal.fire({
            title: 'Selesaikan tugas ini?',
            text: 'Status akan berubah menjadi Selesai.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, selesai',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

});
</script>
@endsection
