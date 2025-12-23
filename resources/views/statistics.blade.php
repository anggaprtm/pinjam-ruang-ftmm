@extends('layouts.admin')
@section('content')
<div class="content">
@section('styles')
<style>
    .chart-container { height: 320px; min-height: 200px; position: relative; }
    .chart-container canvas { width: 100% !important; height: 100% !important; }
    .stat-card { background:#fff; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,0.04); }
</style>
@endsection
    @can('home_access')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Statistik Peminjaman Ruangan</h5>
            <div>
                <div class="btn-group me-2" role="group" aria-label="preset">
                    <a href="{{ route('admin.statistics.index', ['preset' => 'this_month']) }}" class="btn btn-sm btn-outline-secondary">Bulan Ini</a>
                    <a href="{{ route('admin.statistics.index', ['preset' => 'all_time']) }}" class="btn btn-sm btn-outline-secondary">Sepanjang Waktu</a>
                </div>
                <form id="filterForm" class="d-inline-flex align-items-center" method="get" action="{{ route('admin.statistics.index') }}">
                    <input type="hidden" name="preset" id="presetInput" value="">
                    <input type="date" id="startInput" name="start" class="form-control form-control-sm me-2" value="{{ optional($start)->format('Y-m-d') }}">
                    <input type="date" id="endInput" name="end" class="form-control form-control-sm me-2" value="{{ optional($end)->format('Y-m-d') }}">
                    <button class="btn btn-sm btn-primary">Terapkan</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3"><i class="fas fa-door-open fa-2x text-primary"></i></div>
                            <div>
                                <div class="text-muted">Total Ruangan</div>
                                <div class="h4 mb-0">{{ $totalRooms ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3"><i class="fas fa-list-alt fa-2x text-success"></i></div>
                            <div>
                                <div class="text-muted">Total Peminjaman</div>
                                <div class="h4 mb-0">{{ $totalBookings ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><strong>Top Ruangan</strong></div>
                        <div class="card-body">
                            <div class="chart-container mb-3">
                                <canvas id="topRoomsChart"></canvas>
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Ruangan</th><th class="text-end">Jumlah Peminjaman</th></tr></thead>
                                    <tbody>
                                        @forelse($topRooms as $r)
                                            <tr><td>{{ $r->nama }}</td><td class="text-end">{{ $r->total }}</td></tr>
                                        @empty
                                            <tr><td colspan="2">Tidak ada data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header"><strong>Top Pengguna</strong></div>
                        <div class="card-body">
                            <div class="chart-container mb-3">
                                <canvas id="topUsersChart"></canvas>
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Pengguna</th><th class="text-end">Jumlah Peminjaman</th></tr></thead>
                                    <tbody>
                                        @forelse($topUsers as $u)
                                            <tr><td>{{ $u->name }}</td><td class="text-end">{{ $u->total }}</td></tr>
                                        @empty
                                            <tr><td colspan="2">Tidak ada data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="card"><div class="card-body">Anda tidak memiliki akses ke halaman statistik.</div></div>
    @endcan
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const topRoomsLabels = {!! json_encode($topRoomsLabels ?? []) !!};
        const topRoomsData = {!! json_encode($topRoomsData ?? []) !!};

        const topUsersLabels = {!! json_encode($topUsersLabels ?? []) !!};
        const topUsersData = {!! json_encode($topUsersData ?? []) !!};

        const ctxR = document.getElementById('topRoomsChart');
        if (ctxR) {
            new Chart(ctxR, {
                type: 'bar',
                data: {
                    labels: topRoomsLabels,
                    datasets: [{
                        label: 'Jumlah Peminjaman',
                        data: topRoomsData,
                        backgroundColor: topRoomsLabels.map((_,i) => `rgba(${50 + i*10 % 200}, ${120 + i*8 % 120}, ${200 - i*15 % 120}, 0.8)`)
                    }]
                },
                options: {maintainAspectRatio: false, responsive: true, plugins: {legend: {display: false}}}
            });
        }

        const ctxU = document.getElementById('topUsersChart');
        if (ctxU) {
            new Chart(ctxU, {
                type: 'bar',
                data: {
                    labels: topUsersLabels,
                    datasets: [{
                        label: 'Jumlah Peminjaman',
                        data: topUsersData,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)'
                    }]
                },
                options: {maintainAspectRatio: false, responsive: true, plugins: {legend: {display: false}}}
            });
        }

        // Preset handling: when user clicks preset links, they navigate with ?preset=...; handle custom range UI
        const presetInput = document.getElementById('presetInput');
        const startInput = document.getElementById('startInput');
        const endInput = document.getElementById('endInput');

        // If page loaded with no start/end (all_time), disable inputs
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('preset') === 'all_time') {
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';
        }

        // Simple UX: when user edits dates, clear preset
        [startInput, endInput].forEach(el => el && el.addEventListener('change', () => {
            if (presetInput) presetInput.value = '';
        }));
    });
</script>
@endsection

@endsection
