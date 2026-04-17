@extends('layouts.admin')

@section('styles')
<style>
    .sortable-header { color: inherit; text-decoration: none; display: flex; align-items: center; justify-content: space-between; transition: color 0.2s; }
    .sortable-header:hover { color: #0d6efd; }
    .sortable-icon { font-size: 0.75rem; opacity: 0.35; margin-left: 6px; }
    .sortable-header.active .sortable-icon { opacity: 1; color: #0d6efd; }

    .table-tickets th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: #6c757d;
        padding: 11px 16px;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
    }
    .table-tickets td {
        padding: 13px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
    }
    .table-tickets tbody tr:hover { background-color: #f8fbff; }
    .table-tickets tbody tr:last-child td { border-bottom: none; }

    .avatar-circle {
        width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; color: white; font-size: 0.8rem;
        flex-shrink: 0;
    }

    .stat-card {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        padding: 16px 20px;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow 0.2s;
    }
    .stat-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,0.07); }
    .stat-icon {
        width: 46px; height: 46px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .stat-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 3px; }
    .stat-number { font-size: 1.5rem; font-weight: 700; color: #1a1d23; line-height: 1; }

    .filter-card { border-radius: 12px; border: 1px solid #e9ecef; background: #fff; }
    .filter-card .card-body { padding: 14px 18px; }

    .pagination-wrapper .pagination { margin-bottom: 0; }
    .pagination-wrapper p.small { display: none !important; }
    .pagination .page-link { border-radius: 8px !important; margin: 0 2px; font-size: 0.85rem; }

    .ticket-code { font-family: monospace; font-size: 0.75rem; color: #6c757d; }
    .ticket-title { font-weight: 600; font-size: 0.9rem; color: #1a1d23; margin-bottom: 2px; }
    .ticket-category {
        display: inline-block; font-size: 0.68rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.4px;
        background: #f1f3f5; color: #6c757d;
        border-radius: 20px; padding: 2px 8px; margin-bottom: 4px;
    }
</style>
@endsection

@section('content')

@php
    function sortUrl($field) {
        $order = (request('sort') === $field && request('order') === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $field, 'order' => $order]);
    }
    function sortClass($field) { return request('sort') === $field ? 'active' : ''; }
    function sortIcon($field) {
        if (request('sort') === $field) {
            return request('order') === 'desc' ? 'fa-sort-down' : 'fa-sort-up';
        }
        return 'fa-sort';
    }
@endphp

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1a1d23;">
            <i class="fas fa-headset me-2"></i>Tiket Helpdesk
        </h4>
        <p class="text-muted small mb-0">Kelola semua laporan masalah yang masuk dari TickTrace.</p>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;">
                <i class="fas fa-ticket-alt" style="color:#3b82f6;"></i>
            </div>
            <div>
                <div class="stat-label">Total Tiket</div>
                <div class="stat-number">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb;">
                <i class="fas fa-clock" style="color:#f59e0b;"></i>
            </div>
            <div>
                <div class="stat-label">Tiket Aktif</div>
                <div class="stat-number">{{ number_format($stats['aktif'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;">
                <i class="fas fa-check-circle" style="color:#22c55e;"></i>
            </div>
            <div>
                <div class="stat-label">Selesai</div>
                <div class="stat-number">{{ number_format($stats['selesai'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff1f2;">
                <i class="fas fa-times-circle" style="color:#ef4444;"></i>
            </div>
            <div>
                <div class="stat-label">Ditolak</div>
                <div class="stat-number">{{ number_format($stats['ditolak'] ?? 0) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.central-tickets.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1 fw-semibold text-muted">Cari Tiket</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted" style="font-size:0.8rem;"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                           placeholder="ID, judul, nama pelapor..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1 fw-semibold text-muted">Kategori</label>
                <select name="category" class="form-select form-select-sm my-select2" data-placeholder="Semua Kategori">
                    <option value=""></option>
                    <option value="usi"      {{ request('category') == 'usi'      ? 'selected' : '' }}>USI (IT)</option>
                    <option value="sarpras"  {{ request('category') == 'sarpras'  ? 'selected' : '' }}>Sarpras</option>
                    <option value="akademik" {{ request('category') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                    <option value="umum"     {{ request('category') == 'umum'     ? 'selected' : '' }}>Umum</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1 fw-semibold text-muted">Status</label>
                <select name="status" class="form-select form-select-sm my-select2" data-placeholder="Semua Status">
                    <option value=""></option>
                    <option value="open"       {{ request('status') == 'open'       ? 'selected' : '' }}>Open</option>
                    <option value="onprogress" {{ request('status') == 'onprogress' ? 'selected' : '' }}>On Progress</option>
                    <option value="resolved"   {{ request('status') == 'resolved'   ? 'selected' : '' }}>Resolved</option>
                    <option value="rejected"   {{ request('status') == 'rejected'   ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1 fw-semibold text-muted">Prioritas</label>
                <select name="priority" class="form-select form-select-sm my-select2" data-placeholder="Semua Prioritas">
                    <option value=""></option>
                    <option value="low"    {{ request('priority') == 'low'    ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high"   {{ request('priority') == 'high'   ? 'selected' : '' }}>High</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.central-tickets.index') }}" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table Card --}}
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-tickets mb-0">
                <thead>
                    <tr>
                        <th style="width:28%;">
                            <a href="{{ sortUrl('title') }}" class="sortable-header {{ sortClass('title') }}">
                                TIKET INFO <i class="fas {{ sortIcon('title') }} sortable-icon"></i>
                            </a>
                        </th>
                        <th style="width:22%;">PELAPOR</th>
                        <th style="width:14%;" class="text-center">
                            <a href="{{ sortUrl('status') }}" class="sortable-header justify-content-center {{ sortClass('status') }}">
                                STATUS <i class="fas {{ sortIcon('status') }} sortable-icon"></i>
                            </a>
                        </th>
                        <th style="width:13%;" class="text-center">PRIORITAS</th>
                        <th style="width:16%;">
                            <a href="{{ sortUrl('created_at') }}" class="sortable-header {{ sortClass('created_at') }}">
                                TANGGAL <i class="fas {{ sortIcon('created_at') }} sortable-icon"></i>
                            </a>
                        </th>
                        <th style="width:7%;" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>
                            <div class="ticket-category">{{ str_replace('_', ' ', $ticket->category) }}</div>
                            <div class="ticket-title">{{ $ticket->title }}</div>
                            <div class="ticket-code">{{ $ticket->code }}</div>
                        </td>
                        <td>
                            @php
                                $initials   = strtoupper(substr($ticket->reporter_name, 0, 2));
                                $avatarBg   = $ticket->is_guest ? '#ff9800' : '#3b82f6';
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle" style="background:{{ $avatarBg }};">{{ $initials }}</div>
                                <div>
                                    <div class="fw-semibold" style="font-size:0.88rem; color:#1a1d23;">
                                        {{ $ticket->reporter_name }}
                                        @if($ticket->is_guest)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:0.62rem; padding:2px 6px;">GUEST</span>
                                        @endif
                                    </div>
                                    <div class="text-muted" style="font-size:0.78rem;">{{ $ticket->reporter_email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $sColor = match(strtolower($ticket->status)) {
                                    'open'       => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                                    'onprogress' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
                                    'resolved'   => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
                                    'rejected'   => ['bg' => '#fff1f2', 'text' => '#9f1239', 'border' => '#fecdd3'],
                                    default      => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#d1d5db'],
                                };
                            @endphp
                            <span style="display:inline-block; background:{{ $sColor['bg'] }}; color:{{ $sColor['text'] }}; border:1px solid {{ $sColor['border'] }}; border-radius:20px; font-size:0.75rem; font-weight:600; padding:3px 10px;">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $pColor = match(strtolower($ticket->priority)) {
                                    'low'    => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
                                    'medium' => ['bg' => '#fffbeb', 'text' => '#92400e'],
                                    'high'   => ['bg' => '#fff1f2', 'text' => '#9f1239'],
                                    default  => ['bg' => '#f3f4f6', 'text' => '#374151'],
                                };
                            @endphp
                            <span style="display:inline-block; background:{{ $pColor['bg'] }}; color:{{ $pColor['text'] }}; border-radius:6px; font-size:0.75rem; font-weight:600; padding:3px 10px;">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:0.87rem; color:#1a1d23;">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-muted" style="font-size:0.78rem;">
                                <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($ticket->created_at)->format('H:i') }}
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.central-tickets.show', $ticket->id) }}"
                               class="btn btn-sm btn-outline-primary"
                               style="border-radius:8px; font-size:0.8rem; padding:4px 12px;">
                                <i class="fas fa-eye me-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div style="opacity:0.4;">
                                <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                            </div>
                            <h6 class="fw-semibold mb-1">Belum ada tiket masuk</h6>
                            <p class="small mb-0">Tiket dari TickTrace akan otomatis muncul di sini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($tickets) && $tickets->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#fafafa;">
            <div class="text-muted small">
                Menampilkan <strong class="text-dark">{{ $tickets->firstItem() }}</strong>–<strong class="text-dark">{{ $tickets->lastItem() }}</strong>
                dari <strong class="text-dark">{{ number_format($tickets->total()) }}</strong> tiket
            </div>
            <div class="pagination-wrapper">
                {{ $tickets->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(document).ready(function() {
    $('.my-select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 10
    });
});
</script>
@endsection