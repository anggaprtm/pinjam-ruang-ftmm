@extends('layouts.admin')
@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-nowrap"><i class="fas fa-calendar-alt me-2"></i> Agenda Fakultas</h3>
    <a class="btn btn-success" href="{{ route('admin.agenda-fakultas.create') }}">
        <i class="fas fa-plus-circle me-2"></i> Tambah Agenda
    </a>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="modern-table datatable datatable-AgendaFakultas">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th><i class="fas fa-calendar-day me-1"></i> Judul Agenda</th>
                        <th class="text-center"><i class="fas fa-tag me-1"></i> Kategori</th>
                        <th class="text-center"><i class="fas fa-calendar me-1"></i> Tanggal</th>
                        <th class="text-center"><i class="fas fa-hourglass-half me-1"></i> Sisa Waktu</th>
                        <th class="text-center"><i class="fas fa-tv me-1"></i> Signage</th>
                        <th class="text-center"><i class="fas fa-stopwatch me-1"></i> Countdown</th>
                        <th class="text-center" style="width: 120px;"><i class="fas fa-cogs me-1"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agendas as $item)
                        <tr data-entry-id="{{ $item->id }}">
                            <td></td>

                            {{-- Judul --}}
                            <td data-label="Judul">
                                <div class="d-flex align-items-center gap-2">
                                    {{-- Color swatch --}}
                                    <span class="d-inline-block rounded-circle flex-shrink-0"
                                          style="width:12px;height:12px;background:{{ $item->warna }};border:2px solid rgba(0,0,0,0.1);">
                                    </span>
                                    <div>
                                        <div class="kegiatan-title-cell">{{ $item->judul }}</div>
                                        @if($item->deskripsi)
                                            <div class="text-muted small">{{ Str::limit($item->deskripsi, 70) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td data-label="Kategori" class="text-center">
                                <span class="badge"
                                      style="background:{{ $item->warna }}25;color:{{ $item->warna }};border:1px solid {{ $item->warna }}50;font-size:0.75rem;">
                                    {{ $item->kategori }}
                                </span>
                            </td>

                            {{-- Tanggal --}}
                            <td data-label="Tanggal" class="text-center">
                                <div class="fw-semibold">
                                    {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                                </div>
                                @if($item->tanggal_selesai && $item->tanggal_selesai != $item->tanggal_mulai)
                                    <div class="text-muted small">
                                        s/d {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                                    </div>
                                @endif
                                @if(!$item->is_all_day && $item->waktu_mulai)
                                    <div class="text-muted small">
                                        <i class="fas fa-clock fa-xs me-1"></i>
                                        {{ $item->waktu_mulai }}{{ $item->waktu_selesai ? ' - '.$item->waktu_selesai : '' }}
                                    </div>
                                @endif
                            </td>

                            {{-- Sisa Waktu --}}
                            <td data-label="Sisa Waktu" class="text-center">
                                @if($item->is_ongoing)
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle fa-xs me-1"></i> Berlangsung
                                    </span>
                                @elseif($item->sisa_hari < 0)
                                    <span class="badge bg-secondary">Selesai</span>
                                @elseif($item->sisa_hari === 0)
                                    <span class="badge bg-warning text-dark">Hari ini</span>
                                @elseif($item->sisa_hari <= 7)
                                    <span class="badge bg-danger">{{ $item->sisa_hari }} hari lagi</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $item->sisa_hari }} hari lagi</span>
                                @endif
                            </td>

                            {{-- Toggle Signage --}}
                            <td data-label="Signage" class="text-center">
                                @if($item->tampil_di_signage)
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Tampil</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Tersembunyi</span>
                                @endif
                            </td>

                            {{-- Toggle Countdown --}}
                            <td data-label="Countdown" class="text-center">
                                @if($item->tampil_countdown)
                                    <span class="badge bg-warning text-dark"><i class="fas fa-stopwatch me-1"></i>Aktif</span>
                                @else
                                    <span class="badge bg-light text-muted">-</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td data-label="Aksi" class="text-center actions-cell">
                                <a class="btn btn-xs btn-success"
                                   href="{{ route('admin.agenda-fakultas.edit', $item->id) }}"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.agenda-fakultas.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus agenda ini?');"
                                      style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
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
    let dtButtons = getStandardDtButtons();

    let table = $('.datatable-AgendaFakultas').DataTable({
        buttons: dtButtons,
        language: {
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            lengthMenu: "Tampilkan _MENU_ data",
            search: "Cari:",
            paginate: { next: "Berikutnya", previous: "Sebelumnya" },
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada agenda tersedia",
            processing: "Memuat..."
        },
        order: [[ 3, 'asc' ]], // Sort by tanggal
        pageLength: 25,
        columnDefs: [
            { orderable: false, className: 'select-checkbox', targets: 0 },
            { orderable: false, searchable: false, targets: -1 }
        ],
        select: {
            style: 'multi+shift',
            selector: 'td:first-child'
        },
        dom: "<'dt-top-row'<'dt-top-left'l><'dt-top-center'B><'dt-top-right'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab click', function () {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });
});
</script>
@endsection