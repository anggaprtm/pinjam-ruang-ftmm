@extends('layouts.admin')
@section('content')

{{-- Tambahan CSS khusus halaman ini (Sama seperti Aset Fakultas) --}}
<style>
    .pagination-wrapper p.small.text-muted { display: none !important; }
    .sortable-header { color: #333; text-decoration: none; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s; }
    .sortable-header:hover { color: #0d6efd; text-decoration: none; }
    .sortable-icon { font-size: 0.8rem; opacity: 0.4; }
    .sortable-header.active .sortable-icon { opacity: 1; color: #0d6efd; }
    .table-modern th { background-color: #f8f9fa; font-weight: 600; padding: 12px 15px; border-bottom: 2px solid #dee2e6; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;}
    .table-modern td { padding: 12px 15px; vertical-align: middle; }
    
    /* Styling khusus Avatar Pelapor */
    .avatar-circle { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 0.9rem; }
</style>

{{-- Helper Fungsi Sort --}}
@php
    function sortUrl($field) {
        $order = (request('sort') === $field && request('order') === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $field, 'order' => $order]);
    }
    function sortClass($field) {
        return request('sort') === $field ? 'active' : '';
    }
    function sortIcon($field) {
        if (request('sort') === $field) {
            return request('order') === 'desc' ? 'fa-sort-down' : 'fa-sort-up';
        }
        return 'fa-sort';
    }
@endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-nowrap mb-0"><i class="fas fa-headset me-2 text-primary"></i>Daftar Tiket Helpdesk</h3>
        <p class="text-muted small mt-1 mb-0">Kelola semua laporan masalah yang masuk dari TickTrace.</p>
    </div>
</div>

{{-- Stat Cards (Mengadaptasi UI TickTrace) --}}
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white rounded shadow-sm border-start border-4 border-primary">
            <div class="icon-container bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                <i class="fas fa-ticket-alt fa-fw fa-lg"></i>
            </div>
            <div class="info">
                <div class="stat-label text-muted small fw-bold text-uppercase">Total Tiket</div>
                <div class="stat-number fs-4 fw-bold">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white rounded shadow-sm border-start border-4 border-warning">
            <div class="icon-container bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                <i class="fas fa-clock fa-fw fa-lg"></i>
            </div>
            <div class="info">
                <div class="stat-label text-muted small fw-bold text-uppercase">Tiket Aktif</div>
                <div class="stat-number fs-4 fw-bold">{{ number_format($stats['aktif'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white rounded shadow-sm border-start border-4 border-success">
            <div class="icon-container bg-success bg-opacity-10 text-success rounded p-3 me-3">
                <i class="fas fa-check-circle fa-fw fa-lg"></i>
            </div>
            <div class="info">
                <div class="stat-label text-muted small fw-bold text-uppercase">Selesai</div>
                <div class="stat-number fs-4 fw-bold">{{ number_format($stats['selesai'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white rounded shadow-sm border-start border-4 border-danger">
            <div class="icon-container bg-danger bg-opacity-10 text-danger rounded p-3 me-3">
                <i class="fas fa-times-circle fa-fw fa-lg"></i>
            </div>
            <div class="info">
                <div class="stat-label text-muted small fw-bold text-uppercase">Ditolak</div>
                <div class="stat-number fs-4 fw-bold">{{ number_format($stats['ditolak'] ?? 0) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.central-tickets.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1 fw-semibold">Cari ID, Judul, Pelapor...</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ketik..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small mb-1 fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm my-select2" data-placeholder="-- Semua Status --">
                    <option value=""></option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="onprogress" {{ request('status') == 'onprogress' ? 'selected' : '' }}>On Progress</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small mb-1 fw-semibold">Prioritas</label>
                <select name="priority" class="form-select form-select-sm my-select2" data-placeholder="-- Semua Prioritas --">
                    <option value=""></option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>
            
            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-50 shadow-sm">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.central-tickets.index') }}" class="btn btn-sm btn-outline-secondary w-50 shadow-sm">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Data --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-modern mb-0">
                <thead>
                    <tr>
                        <th width="20%">
                            <a href="{{ sortUrl('title') }}" class="sortable-header {{ sortClass('title') }}">
                                <span><i class="fas fa-file-alt me-1 text-muted"></i> TIKET INFO</span>
                                <i class="fas {{ sortIcon('title') }} sortable-icon"></i>
                            </a>
                        </th>
                        <th width="20%"><i class="fas fa-user me-1 text-muted"></i> PELAPOR</th>
                        <th width="15%" class="text-center">
                            <a href="{{ sortUrl('status') }}" class="sortable-header justify-content-center {{ sortClass('status') }}">
                                <span><i class="fas fa-tasks me-1 text-muted"></i> STATUS</span>
                                <i class="fas {{ sortIcon('status') }} sortable-icon ms-2"></i>
                            </a>
                        </th>
                        <th width="15%" class="text-center"><i class="fas fa-exclamation-triangle me-1 text-muted"></i> PRIORITAS</th>
                        <th width="15%">
                            <a href="{{ sortUrl('created_at') }}" class="sortable-header {{ sortClass('created_at') }}">
                                <span><i class="fas fa-calendar-alt me-1 text-muted"></i> TANGGAL</span>
                                <i class="fas {{ sortIcon('created_at') }} sortable-icon"></i>
                            </a>
                        </th>
                        <th width="10%" class="text-center"><i class="fas fa-ellipsis-v me-1 text-muted"></i> AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <span class="badge bg-light text-secondary border mb-1 px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                                    {{ strtoupper(str_replace('_', ' ', $ticket->category)) }}
                                </span>
                                <div class="fw-bold text-dark">{{ $ticket->title }}</div>
                                <div class="text-muted small">{{ $ticket->code }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    {{-- Mengambil inisial 2 huruf depan --}}
                                    @php
                                        $initials = strtoupper(substr($ticket->reporter_name, 0, 2));
                                        $avatarColor = $ticket->is_guest ? '#ff9800' : '#2196f3';
                                    @endphp
                                    <div class="avatar-circle me-2 shadow-sm" style="background-color: {{ $avatarColor }}">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                            {{ $ticket->reporter_name }}
                                            @if($ticket->is_guest)
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 0.65rem;">GUEST</span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $ticket->reporter_email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusBadge = match(strtolower($ticket->status)) {
                                        'open' => 'primary',
                                        'onprogress' => 'warning',
                                        'resolved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusBadge }} bg-opacity-10 text-{{ $statusBadge }} border border-{{ $statusBadge }} px-3 py-1 rounded-pill">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $priorityBadge = match(strtolower($ticket->priority)) {
                                        'low' => 'info',
                                        'medium' => 'warning text-dark',
                                        'high' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $priorityBadge }} px-2 py-1 shadow-sm rounded-pill">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($ticket->created_at)->translatedFormat('d M Y') }}</div>
                                <div class="text-muted small"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($ticket->created_at)->format('H:i') }}</div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.central-tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                                <h5>Belum ada tiket masuk</h5>
                                <p class="small mb-0">Tiket dari TickTrace akan otomatis muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($tickets) && $tickets->hasPages())
            <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-bottom">
                <div class="text-muted small fw-semibold">
                    <i class="fas fa-list-ol me-1"></i> Menampilkan <span class="text-dark">{{ $tickets->firstItem() }}</span> – <span class="text-dark">{{ $tickets->lastItem() }}</span> dari total <span class="text-dark">{{ number_format($tickets->total()) }}</span> tiket
                </div>
                <div class="pagination-wrapper m-0">
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