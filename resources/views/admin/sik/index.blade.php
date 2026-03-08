@extends('layouts.admin')
@section('content')
@php
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Diajukan',
        'on_verification' => 'Proses Pengajuan',
        'need_revision' => 'Perlu Revisi',
        'approved_final' => 'Disetujui Final',
        'issued' => 'Sudah Terbit SIK',
        'cancelled' => 'Dibatalkan',
    ];
    $urgencyClass = function ($dueAt) {
        if (empty($dueAt)) return 'bg-light text-dark';
        $hours = now()->diffInHours(\Carbon\Carbon::parse($dueAt), false);
        if ($hours < 0) return 'bg-danger text-white';
        if ($hours <= 24) return 'bg-warning text-dark';
        return 'bg-info text-dark';
    };
@endphp

@if(($mode ?? 'verifikator') === 'ormawa')
    <div class="d-flex align-items-center mb-3">
        <h3 class="mb-0">Proker & Pengajuan SIK Ormawa</h3>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sik.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Cari Proker</label>
                    <input type="text" class="form-control" name="q" value="{{ $search ?? '' }}" placeholder="Nama proker / kode / ormawa">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-control">
                        <option value="">Semua Tahun</option>
                        @foreach(($availableYears ?? []) as $yr)
                            <option value="{{ $yr }}" @selected((int)($selectedYear ?? 0) === (int)$yr)>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Terapkan</button>
                    <a href="{{ route('admin.sik.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('admin.sik.create') }}" class="btn btn-success ms-auto">Ajukan SIK Baru</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse(($programItems ?? collect()) as $item)
            @php
                $app = $item->sikApplication;
                $status = $app?->status_sik;
                $statusText = $app ? ($statusLabels[$status] ?? strtoupper($status)) : 'Belum Diajukan';
                $statusClass = ! $app ? 'bg-light text-dark' : ($status === 'issued' ? 'bg-success' : ($status === 'need_revision' ? 'bg-warning text-dark' : 'bg-secondary'));
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 proker-card-modern">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                            <small class="text-muted">{{ $item->plan->tahun ?? '-' }}</small>
                        </div>
                        <h5 class="mb-1">{{ $item->nama_rencana }}</h5>
                        <div class="text-muted small mb-2">{{ $item->plan->ormawa->nama ?? '-' }}</div>
                        <div class="small">{{ optional($item->timeline_mulai_rencana)->format('d M Y') ?? '-' }} - {{ optional($item->timeline_selesai_rencana)->format('d M Y') ?? '-' }}</div>
                        <div class="mt-auto pt-3 d-flex flex-wrap gap-2">
                            @if($app)
                                <a href="{{ route('admin.sik.show', $app->id) }}" class="btn btn-sm btn-info">Detail Pengajuan</a>
                                @if($status === 'need_revision')
                                    <a href="{{ route('admin.sik.edit', $app->id) }}" class="btn btn-sm btn-warning">Revisi</a>
                                @endif
                                @if($status === 'issued')
                                    <a href="{{ route('admin.cariRuang', ['sik_application_id' => $app->id]) }}" class="btn btn-sm btn-success">Peminjaman Ruangan</a>
                                @endif
                            @else
                                <a href="{{ route('admin.sik.create', ['program_item_id' => $item->id, 'tahun' => $item->plan->tahun ?? null]) }}" class="btn btn-sm btn-primary">Ajukan SIK</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">Belum ada proker published sesuai filter.</div>
            </div>
        @endforelse
    </div>
@else
    <div class="d-flex align-items-center mb-3">
        <h3 class="mb-0">Antrian Verifikasi SIK</h3>
        <div class="ms-auto">
            <a href="{{ route('admin.sik.create') }}" class="btn btn-success"><i class="fas fa-plus-circle me-1"></i> Ajukan SIK</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sik.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Ormawa</label>
                    <select name="filter_ormawa" class="form-control">
                        <option value="">Semua Ormawa</option>
                        @foreach(($ormawaOptions ?? collect()) as $ormawaId => $ormawaName)
                            <option value="{{ $ormawaId }}" @selected((string)($selectedOrmawa ?? '') === (string)$ormawaId)>{{ $ormawaName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Pengajuan</label>
                    <select name="status_sik" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach($statusLabels as $statusKey => $statusText)
                            <option value="{{ $statusKey }}" @selected(request('status_sik') === $statusKey)>{{ $statusText }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                    <a href="{{ route('admin.sik.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach(($ormawaCards ?? collect()) as $ormawaName => $meta)
            @php
                $due = !empty($meta['nearest_due']) ? \Carbon\Carbon::parse($meta['nearest_due']) : null;
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-bold mb-2">{{ $ormawaName }}</div>
                        <div class="small text-muted">Perlu Verifikasi: {{ $meta['on_verification'] }}</div>
                        <div class="small text-muted">Perlu Revisi: {{ $meta['need_revision'] }}</div>
                        <div class="small text-muted">Sudah Terbit: {{ $meta['issued'] }}</div>
                        @if($due)
                            <div class="mt-2">
                                <span class="badge {{ $urgencyClass($meta['nearest_due']) }}">SLA terdekat: {{ $due->format('d M Y H:i') }}</span>
                            </div>
                            <div class="small mt-1 {{ $due->isPast() ? 'text-danger' : 'text-muted' }}">
                                {{ $due->isPast() ? 'Melewati SLA' : 'Sisa ' . now()->diffForHumans($due, ['parts' => 2, 'short' => true]) }}
                            </div>
                        @endif

                        @if(!empty($meta['nearest_application_id']))
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <a href="{{ route('admin.sik.show', $meta['nearest_application_id']) }}" class="btn btn-sm btn-primary">
                                    Quick Action Verifikasi
                                </a>
                                <span class="small text-muted align-self-center">{{ $meta['nearest_title'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle datatable datatable-SikVerifier">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ormawa</th>
                        <th>Proker</th>
                        <th>Status</th>
                        <th>Timeline Final</th>
                        <th>No SIK e-office</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($applications ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->ormawa->nama ?? '-' }}</td>
                            <td>{{ $item->judul_final_kegiatan }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status_sik === 'issued' ? 'success' : ($item->status_sik === 'need_revision' ? 'warning text-dark' : ($item->status_sik === 'cancelled' ? 'danger' : 'secondary')) }}">
                                    {{ $statusLabels[$item->status_sik] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $item->status_sik)) }}
                                </span>
                            </td>
                            <td>{{ optional($item->timeline_mulai_final)->format('d M Y') }} - {{ optional($item->timeline_selesai_final)->format('d M Y') }}</td>
                            <td>{{ $item->nomor_sik_eoffice ?? '-' }}</td>
                            <td>{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                            <td><a href="{{ route('admin.sik.show', $item->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada pengajuan SIK.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@section('scripts')
@parent
<script>
$(function () {
    const tableSelector = "{{ ($mode ?? 'verifikator') === 'verifikator' ? '.datatable-SikVerifier' : '' }}";
    if (tableSelector && $(tableSelector).length) {
        $(tableSelector).DataTable({
            pageLength: 25,
            order: [[6, 'asc']],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'excel', className: 'btn btn-sm btn-outline-success' },
                { extend: 'csv', className: 'btn btn-sm btn-outline-primary' },
                { extend: 'print', className: 'btn btn-sm btn-outline-dark' }
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                zeroRecords: 'Tidak ada data yang cocok',
            }
        });
    }
});
</script>
@endsection
