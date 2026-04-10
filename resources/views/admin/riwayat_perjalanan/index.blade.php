@extends('layouts.admin')

@section('styles')
<style>
/* ============================================================
   INDEX BLADE — LOGBOOK DRIVER (MODERN & RESPONSIVE)
   ============================================================ */

/* ----- CARDS & UTILITIES ----- */
.icon-circle-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #eff6ff;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    flex-shrink: 0;
}
.icon-circle-sm.green {
    background: #f0fdf4;
    color: #16a34a;
}
.vehicle-name {
    font-weight: 700;
    font-size: .88rem;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.plate-badge {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .5px;
    color: #475569;
    padding: .05rem .35rem;
    font-family: monospace;
}
.user-initial {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    font-weight: 700;
    font-size: .8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ----- KM SUMMARY CARD ----- */
.km-summary-card {
    border: none;
    border-radius: 14px;
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
    color: #fff;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(37,99,235,.25);
}
.km-summary-card .km-title { font-size: .72rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; opacity: .8; margin-bottom: .5rem; }
.km-summary-card .km-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
.km-summary-card .km-stat { text-align: center; background: rgba(255,255,255,.12); border-radius: 10px; padding: .6rem .5rem; }
.km-summary-card .km-stat .val { font-size: 1.3rem; font-weight: 800; line-height: 1.1; }
.km-summary-card .km-stat .lbl { font-size: .65rem; opacity: .75; margin-top: .15rem; }

/* ----- ONGOING SECTION (MOBILE CARDS) ----- */
.ongoing-header {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%) !important;
    border-bottom: none !important;
}
.ongoing-header h5, .ongoing-header i { color: #fff !important; margin: 0; }
.trip-list { display: flex; flex-direction: column; gap: 0; }
.trip-item {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.trip-item-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
.trip-vehicle { display: flex; align-items: center; gap: .5rem; flex: 1; min-width: 0; }
.trip-item-meta { display: flex; flex-wrap: wrap; gap: .35rem .75rem; font-size: .78rem; color: #6b7280; }
.trip-item-meta span { display: flex; align-items: center; gap: .3rem; }
.trip-destination { font-weight: 600; font-size: .85rem; color: #111827; }

/* ----- BADGE & TABLE STYLING ----- */
.badge-status-onduty  { background: #f0fdf4; color: #15803d; }
.km-badge {
    display: inline-flex; align-items: center; gap: .25rem;
    background: #fefce8; border: 1px solid #fde68a; border-radius: 6px;
    font-size: .72rem; font-weight: 700; color: #92400e; padding: .15rem .45rem;
}
.modern-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.modern-table thead tr th {
    background: #f8fafc; font-size: .75rem; font-weight: 700;
    letter-spacing: .5px; text-transform: uppercase; color: #64748b;
    padding: .75rem 1rem; border-bottom: 2px solid #e2e8f0;
}
.dt-top-row {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem;
    padding: 1rem; border-bottom: 1px solid #f3f4f6;
}

/* Responsive Table Toggle */
.table-desktop { display: none; }
@media (min-width: 768px) {
    .table-desktop { display: block; }
    .trip-list     { display: none; }
}
</style>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold text-nowrap"><i class="fas fa-road me-2"></i> Logbook Driver & Kendaraan</h3>
    @can('riwayat_perjalanan_create')
        <a class="btn btn-success" href="{{ route('admin.riwayat-perjalanan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> 
            <span class="d-none d-sm-inline">Input Jalan / Booking</span>
            <span class="d-inline d-sm-none">Input</span>
        </a>
    @endcan
</div>

{{-- KM Hari Ini --}}
@if(isset($kmHariIni))
<div class="km-summary-card">
    <div class="km-title"><i class="fas fa-tachometer-alt me-1"></i> Kilometer Hari Ini</div>
    <div class="km-grid">
        <div class="km-stat">
            <div class="val">{{ number_format($kmHariIni->km_awal ?? 0) }}</div>
            <div class="lbl">KM Awal</div>
        </div>
        <div class="km-stat">
            <div class="val">{{ $kmHariIni->km_akhir ? number_format($kmHariIni->km_akhir) : '—' }}</div>
            <div class="lbl">KM Akhir</div>
        </div>
        <div class="km-stat">
            <div class="val">
                @if($kmHariIni->km_akhir && $kmHariIni->km_awal)
                    +{{ number_format($kmHariIni->km_akhir - $kmHariIni->km_awal) }}
                @elseif($kmKemarinAwal && $kmHariIni->km_awal)
                    +{{ number_format($kmHariIni->km_awal - $kmKemarinAwal) }}
                @else
                    —
                @endif
            </div>
            <div class="lbl">Jarak Tempuh</div>
        </div>
    </div>
</div>
@endif

{{-- Section Ongoing --}}
@if(isset($ongoing) && $ongoing->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header ongoing-header p-3">
        <h5 class="m-0"><i class="fas fa-road me-2"></i> Sedang Berlangsung</h5>
    </div>
    <div class="card-body p-0">
        {{-- MOBILE LIST --}}
        <div class="trip-list">
            @foreach($ongoing as $row)
            @php
                $driverName = $row->driver->name ?? '-';
                $waktu = $row->waktu_mulai ? \Carbon\Carbon::parse($row->getRawOriginal('waktu_mulai'))->format('d M Y H:i') : '-';
            @endphp
            <div class="trip-item">
                <div class="trip-item-top">
                    <div class="trip-vehicle">
                        <span class="icon-circle-sm green"><i class="fas fa-car"></i></span>
                        <div>
                            <div class="vehicle-name">{{ $row->mobil->nama_mobil ?? '-' }}</div>
                            <span class="plate-badge">{{ $row->mobil->plat_nomor ?? '' }}</span>
                        </div>
                    </div>
                    <span class="badge-status badge-status-onduty">🚗 On Duty</span>
                </div>
                <div class="trip-destination mt-1">{{ $row->tujuan ?? '-' }}</div>
                <div class="trip-item-meta mt-1">
                    <span><i class="fas fa-user"></i> {{ $driverName }}</span>
                    <span><i class="fas fa-clock"></i> {{ $waktu }}</span>
                </div>
                <div class="mt-2">
                    <form action="{{ route('admin.riwayat-perjalanan.selesaikan', $row->id) }}" method="POST" class="d-inline js-selesaikan-tugas">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-success w-100 mb-1">
                            <i class="fas fa-check me-1"></i> Selesaikan Tugas
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="table-desktop">
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
                                    <span class="icon-circle-sm green me-2"><i class="fas fa-car"></i></span>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $row->mobil->nama_mobil ?? '-' }}</div>
                                        <span class="plate-badge">{{ $row->mobil->plat_nomor ?? '' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $row->driver->name ?? '-' }}</td>
                            <td>{{ $row->tujuan ?? '-' }}</td>
                            <td>{{ $row->waktu_mulai ?? '-' }}</td>
                            <td class="text-center">
                                <form action="{{ route('admin.riwayat-perjalanan.selesaikan', $row->id) }}" method="POST" class="d-inline js-selesaikan-tugas">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success">Selesai</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Datatable Riwayat --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="m-0"><i class="fas fa-list-alt me-2"></i> Jadwal Mendatang & Riwayat</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-Riwayat w-100">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>STATUS</th>
                        <th>JADWAL</th>
                        <th>KENDARAAN</th>
                        <th class="d-none d-md-table-cell">DRIVER</th>
                        <th>TUJUAN</th>
                        <th class="d-none d-md-table-cell">KM</th>
                        <th class="text-center" style="width:140px;">AKSI</th>
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

    @can('riwayat_perjalanan_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.riwayat-perjalanan.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
            let ids = $.map(dt.rows({ selected: true }).data(), function (entry) { return entry.id; });
            if (ids.length === 0) { Swal.fire('Peringatan', 'Tidak ada data dipilih', 'warning'); return; }
            Swal.fire({
                title: 'Yakin hapus terpilih?', icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#d33', confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({ headers: {'x-csrf-token': _token}, method: 'POST', url: config.url, data: { ids: ids, _method: 'DELETE' }})
                    .done(function () { $('.datatable-Riwayat').DataTable().ajax.reload(); });
                }
            });
        }
    });
    @endcan

    let table = $('.datatable-Riwayat').DataTable({
        buttons: dtButtons,
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.riwayat-perjalanan.index') }}",
        
        // 🔥 SOLUSI MOBILE: Injeksi data-label secara dinamis 🔥
        createdRow: function (row, data, dataIndex) {
            $(row).find('td:eq(1)').attr('data-label', 'Status');
            $(row).find('td:eq(2)').attr('data-label', 'Jadwal');
            $(row).find('td:eq(3)').attr('data-label', 'Kendaraan');
            $(row).find('td:eq(4)').attr('data-label', 'Driver');
            $(row).find('td:eq(5)').attr('data-label', 'Tujuan');
            $(row).find('td:eq(6)').attr('data-label', 'KM');
            $(row).find('td:eq(7)').attr('data-label', 'Aksi');
        },

        columns: [
            { data: 'placeholder', name: 'placeholder' },
            { data: 'status', name: 'status' },
            { data: 'waktu_mulai', name: 'waktu_mulai' },
            { data: 'kendaraan', name: 'mobil.nama_mobil' },
            { data: 'driver_display', name: 'driver.name', className: 'd-none d-md-table-cell' },
            { data: 'tujuan', name: 'tujuan' },
            { data: 'km_info', name: 'km_awal', className: 'd-none d-md-table-cell' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[ 2, 'desc' ]],
        pageLength: 50,
        select: { style: 'multi+shift', selector: 'td:first-child' },
        dom: "<'dt-top-row'<'dt-top-left'l><'dt-top-center'B><'dt-top-right'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // SweetAlert Handlers
    $(document).on('submit', '.js-selesaikan-tugas', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Selesaikan tugas?', icon: 'question', showCancelButton: true,
            confirmButtonText: 'Ya, selesai', confirmButtonColor: '#16a34a'
        }).then((r) => { if (r.isConfirmed) this.submit(); });
    });
});
</script>
@endsection