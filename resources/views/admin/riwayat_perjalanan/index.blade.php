@extends('layouts.admin')

@section('styles')
<style>
/* ============================================================
   INDEX BLADE — LOGBOOK DRIVER (MODERN & RESPONSIVE)
   ============================================================ */

/* ----- CARDS & UTILITIES ----- */
.icon-circle-sm {
    width: 32px; height: 32px; border-radius: 50%;
    background: #eff6ff; color: #2563eb; display: flex;
    align-items: center; justify-content: center; font-size: .8rem; flex-shrink: 0;
}
.icon-circle-sm.green { background: #f0fdf4; color: #16a34a; }
.vehicle-name { font-weight: 700; font-size: .88rem; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.plate-badge {
    display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 4px; font-size: .7rem; font-weight: 700; letter-spacing: .5px;
    color: #475569; padding: .05rem .35rem; font-family: monospace;
}
.user-initial {
    width: 30px; height: 30px; border-radius: 50%; background: #2563eb; color: #fff;
    font-weight: 700; font-size: .8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ----- MASTER ODOMETER CARD ----- */
.km-summary-card {
    border: none; border-radius: 14px;
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
    color: #fff; padding: 1.1rem 1.25rem; margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(37,99,235,.25);
}
.km-summary-card .km-title { font-size: .72rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; opacity: .8; margin-bottom: .5rem; }
.km-summary-card .km-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
.km-summary-card .km-stat { text-align: center; background: rgba(255,255,255,.12); border-radius: 10px; padding: .6rem .5rem; }
.km-summary-card .km-stat .val { font-size: 1.3rem; font-weight: 800; line-height: 1.1; }
.km-summary-card .km-stat .lbl { font-size: .65rem; opacity: .75; margin-top: .15rem; }

/* ----- SWIPEABLE TABS UNTUK MOBILE ----- */
.nav-tabs.swipeable-tabs {
    flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; white-space: nowrap;
    border-bottom: 2px solid #e2e8f0; padding-bottom: 2px;
}
.nav-tabs.swipeable-tabs::-webkit-scrollbar { display: none; }

/* ----- ONGOING SECTION (MOBILE CARDS) ----- */
.ongoing-header { background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%) !important; border-bottom: none !important; }
.ongoing-header h5, .ongoing-header i { color: #fff !important; margin: 0; }
.trip-list { display: flex; flex-direction: column; gap: 0; }
.trip-item { padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6; display: flex; flex-direction: column; gap: .5rem; }
.trip-item-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
.trip-vehicle { display: flex; align-items: center; gap: .5rem; flex: 1; min-width: 0; }
.trip-item-meta { display: flex; flex-wrap: wrap; gap: .35rem .75rem; font-size: .78rem; color: #6b7280; }
.trip-item-meta span { display: flex; align-items: center; gap: .3rem; }
.trip-destination { font-weight: 600; font-size: .85rem; color: #111827; }

/* ----- BADGE & TABLE STYLING ----- */
.badge-status-onduty  { background: #f0fdf4; color: #15803d; border-radius: 20px; padding: 4px 10px; font-weight: bold; font-size: 0.75rem;}
.badge-status-booking { background: #eff6ff; color: #1d4ed8; border-radius: 20px; padding: 4px 10px; font-weight: bold; font-size: 0.75rem;}
.badge-status-selesai { background: #f1f5f9; color: #475569; border-radius: 20px; padding: 4px 10px; font-weight: bold; font-size: 0.75rem;}

.km-badge {
    display: inline-flex; align-items: center; gap: .25rem;
    background: #fefce8; border: 1px solid #fde68a; border-radius: 6px;
    font-size: .72rem; font-weight: 700; color: #92400e; padding: .15rem .45rem;
}
.km-selisih-badge {
    display: inline-flex; align-items: center; gap: .25rem;
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;
    font-size: .72rem; font-weight: 700; color: #16a34a; padding: .15rem .45rem;
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

/* ============================================================
   🔥 MAGIC: RESPONSIVE TABLE TO CARDS (KHUSUS MOBILE) 🔥
   ============================================================ */
.table-desktop { display: none; }

@media (min-width: 768px) {
    .table-desktop { display: block; }
    .trip-list     { display: none; }
}

@media (max-width: 767.98px) {
    /* Sembunyikan header tabel */
    .modern-table thead { display: none; }
    
    /* Ubah baris jadi card */
    .modern-table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        background: #fff;
    }
    
    /* Ubah sel jadi flexbox (Label di kiri, Value di kanan) */
    .modern-table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0.5rem;
        border-bottom: 1px solid #f8fafc;
        text-align: right;
        font-size: 0.85rem;
    }
    
    .modern-table tbody td:last-child { 
        border-bottom: none; 
        justify-content: center; /* Tombol aksi di tengah */
        padding-top: 1rem;
    }
    
    /* Munculkan label dari atribut data-label */
    .modern-table tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #64748b;
        font-size: 0.7rem;
        text-transform: uppercase;
        text-align: left;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    /* Sembunyikan checkbox mass-delete di mobile agar tidak memakan ruang */
    .modern-table tbody td.select-checkbox,
    .modern-table tbody td:first-child:empty {
        display: none !important;
    }

    /* Perbaiki layout pagination & search DataTables di mobile */
    .dt-top-row { flex-direction: column; gap: 1rem; align-items: stretch !important; }
    .dt-top-left, .dt-top-right, .dt-top-center { width: 100%; text-align: center; }
    .dt-top-right input { width: 100% !important; margin: 0; }
    
    /* KM Grid agar tidak terlalu sempit di HP kecil */
    .km-summary-card .km-stat { padding: 0.5rem 0.2rem; }
    .km-summary-card .km-stat .val { font-size: 1.1rem; }
    .km-summary-card .km-stat .lbl { font-size: 0.6rem; line-height: 1.2; }
}
</style>
@endsection

@section('content')

{{-- HEADER & BUTTONS --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h3 class="fw-bold text-nowrap mb-0"><i class="fas fa-road me-2"></i> Logbook Kendaraan</h3>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-warning shadow-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalBbm">
            <i class="fas fa-gas-pump me-1"></i> <span class="d-none d-sm-inline">Isi Bensin</span>
        </button>
        @can('riwayat_perjalanan_create')
            <a class="btn btn-success shadow-sm fw-bold" href="{{ route('admin.riwayat-perjalanan.create') }}">
                <i class="fas fa-car-side me-1"></i> <span class="d-none d-sm-inline">Input Jalan</span>
            </a>
        @endcan
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success shadow-sm rounded-3"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
@endif

{{-- MASTER ODOMETER CARD --}}
<div class="km-summary-card mb-4">
    <div class="km-title"><i class="fas fa-tachometer-alt me-1"></i> RIWAYAT ODOMETER KENDARAAN</div>
    <div class="km-grid">
        <div class="km-stat">
            <div class="val">{{ $kmTerakhir ? number_format($kmTerakhir->km) : '0' }} <small style="font-size:0.6em;">KM</small></div>
            <div class="lbl">Angka Odometer Saat Ini</div>
        </div>
        <div class="km-stat">
            <div class="val" style="font-size: 1rem; line-height: 1.3; margin-top: 5px;">
                {{ $kmTerakhir ? \Carbon\Carbon::parse($kmTerakhir->waktu)->translatedFormat('d M y, H:i') : '-' }}
            </div>
            <div class="lbl">Update Terakhir: <strong class="text-warning">{{ $kmTerakhir->sumber ?? '-' }}</strong></div>
        </div>
        <div class="km-stat">
            <div class="val">
                @if($kmTerakhir && $kmSebelumnya)
                    <span class="text-warning">+{{ number_format($kmTerakhir->km - $kmSebelumnya->km) }} <small style="font-size:0.6em;">KM</small></span>
                @else
                    —
                @endif
            </div>
            <div class="lbl">Selisih dr Data Sebelumnya</div>
        </div>
    </div>
</div>

{{-- SWIPEABLE TABS NAVIGASI --}}
<ul class="nav nav-tabs swipeable-tabs fw-bold mb-3" id="logbookTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active text-dark" data-bs-toggle="tab" data-bs-target="#tab-perjalanan" type="button">
            <i class="fas fa-route me-1 text-primary"></i> Riwayat Perjalanan
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" data-bs-toggle="tab" data-bs-target="#tab-bbm" type="button">
            <i class="fas fa-gas-pump me-1 text-warning"></i> Riwayat Isi Bensin
        </button>
    </li>
</ul>

{{-- TAB KONTEN --}}
<div class="tab-content">
    {{-- TAB 1: PERJALANAN --}}
    <div class="tab-pane fade show active" id="tab-perjalanan">
        
        {{-- Section Ongoing (Status Sedang Jalan) --}}
        @if(isset($ongoing) && $ongoing->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header ongoing-header p-3">
                <h5 class="m-0"><i class="fas fa-car-side me-2"></i> Sedang Berlangsung</h5>
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
                            <span class="badge-status-onduty">🚗 On Duty</span>
                        </div>
                        <div class="trip-destination mt-1">{{ $row->tujuan ?? '-' }}</div>
                        <div class="trip-item-meta mt-1">
                            <span><i class="fas fa-user"></i> {{ $driverName }}</span>
                            <span><i class="fas fa-clock"></i> {{ $waktu }}</span>
                        </div>
                        <div class="mt-2">
                            <form action="{{ route('admin.riwayat-perjalanan.selesaikan', $row->id) }}" method="POST" class="d-inline js-selesaikan-tugas">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success w-100 mb-1 fw-bold">
                                    <i class="fas fa-check-circle me-1"></i> Selesaikan Tugas
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
                                            <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="fas fa-check"></i> Selesai</button>
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

        {{-- Datatable Riwayat Perjalanan --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-3">
                <h5 class="m-0 text-primary fw-bold"><i class="fas fa-history me-2"></i> Riwayat & Jadwal</h5>
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
                                <th>DRIVER</th>
                                <th>TUJUAN</th>
                                <th>KM</th>
                                <th class="text-center" style="width:140px;">AKSI</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: RIWAYAT BBM --}}
    <div class="tab-pane fade" id="tab-bbm">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-warning fw-bold text-dark"><i class="fas fa-gas-pump me-2"></i> Riwayat Isi Bensin</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="modern-table w-100">
                        <thead>
                            <tr>
                                <th>TANGGAL & WAKTU</th>
                                <th>KM ODOMETER</th>
                                <th>BIAYA PENGISIAN</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($riwayatBbm as $bbm)
                            <tr>
                                <td data-label="Waktu">{{ \Carbon\Carbon::parse($bbm->tanggal)->translatedFormat('d F Y, H:i') }}</td>
                                <td data-label="KM Odometer"><span class="km-badge"><i class="fas fa-tachometer-alt me-1"></i>{{ number_format($bbm->km_odometer) }}</span></td>
                                <td data-label="Biaya">{{ $bbm->biaya ? 'Rp ' . number_format($bbm->biaya, 0, ',', '.') : '-' }}</td>
                                <td data-label="Aksi" class="text-center">
                                    <form action="{{ route('admin.riwayat-perjalanan.destroyBbm', $bbm->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data bensin ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($riwayatBbm->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada riwayat pengisian bensin tercatat.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INPUT BBM --}}
<div class="modal fade" id="modalBbm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-gas-pump me-2"></i> Catat Isi Bensin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.riwayat-perjalanan.storeBbm') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Waktu Pengisian</label>
                        <input type="datetime-local" name="tanggal" class="form-control" required value="{{ date('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-primary">Angka KM Odometer Saat Ini</label>
                        <input type="number" name="km_odometer" class="form-control border-primary" placeholder="Contoh: 45200" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold small">Biaya (Rupiah) <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="number" name="biaya" class="form-control" placeholder="Contoh: 150000">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-1"></i> Simpan Data</button>
                </div>
            </form>
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
        
        // 🔥 INJEKSI DATA-LABEL UNTUK MOBILE CARD 🔥
        createdRow: function (row, data, dataIndex) {
            $(row).find('td:eq(1)').attr('data-label', 'Status');
            $(row).find('td:eq(2)').attr('data-label', 'Jadwal');
            $(row).find('td:eq(3)').attr('data-label', 'Kendaraan');
            $(row).find('td:eq(4)').attr('data-label', 'Driver');
            $(row).find('td:eq(5)').attr('data-label', 'Tujuan');
            $(row).find('td:eq(6)').attr('data-label', 'Kilometer');
            $(row).find('td:eq(7)').attr('data-label', ''); // Aksi tidak perlu label
        },

        columns: [
            { data: 'placeholder', name: 'placeholder' },
            { data: 'status', name: 'status' },
            { data: 'waktu_mulai', name: 'waktu_mulai' },
            { data: 'kendaraan', name: 'mobil.nama_mobil' },
            { data: 'driver_display', name: 'driver.name' },
            { data: 'tujuan', name: 'tujuan' },
            { data: 'km_info', name: 'km_awal' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[ 2, 'desc' ]],
        pageLength: 25,
        select: { style: 'multi+shift', selector: 'td:first-child' },
        language: {                                                    // ← TAMBAH INI
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
        dom: "<'dt-top-row'<'dt-top-left'l><'dt-top-center d-none d-md-block'B><'dt-top-right'f>>" +
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