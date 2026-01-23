@extends('layouts.admin')

@section('content')
<div class="content statistics-page">

    @can('home_access')

        {{-- HEADER --}}
        <div class="mb-3">
            <h4 class="fw-bold mb-1">
                <i class="fas fa-chart-bar me-2 text-primary"></i>
                Statistik Peminjaman Ruangan
            </h4>
            <div class="text-muted small">
                Pantau performa pemakaian ruangan berdasarkan rentang waktu.
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="card border-0 shadow-sm stats-filter-card mb-4">
            <div class="card-body p-3">
                <form id="filterForm" method="get" action="{{ route('admin.statistics.index') }}">
                    <input type="hidden" name="preset" id="presetInput" value="">

                    <div class="row g-3 align-items-center">
                        {{-- Preset Buttons --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.statistics.index', ['preset' => 'this_month']) }}"
                                   class="btn btn-outline-info flex-fill {{ request('preset') === 'this_month' ? 'active' : '' }}">
                                    <i class="fas fa-calendar-alt me-1"></i> Bulan Ini
                                </a>
                                <a href="{{ route('admin.statistics.index', ['preset' => 'all_time']) }}"
                                   class="btn btn-outline-info flex-fill {{ request('preset') === 'all_time' ? 'active' : '' }}">
                                    <i class="fas fa-infinity me-1"></i> Semua
                                </a>
                            </div>
                        </div>

                        {{-- Date Inputs --}}
                        <div class="col-lg-3 col-md-6">
                            <input type="date" id="startInput" name="start"
                                   class="form-control"
                                   placeholder="Tanggal Mulai"
                                   value="{{ optional($start)->format('Y-m-d') }}">
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <input type="date" id="endInput" name="end"
                                   class="form-control"
                                   placeholder="Tanggal Akhir"
                                   value="{{ optional($end)->format('Y-m-d') }}">
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-filter me-1"></i> Terapkan
                                </button>
                                <a class="btn btn-success" 
                                   href="{{ route('admin.statistics.exportExcel', request()->query()) }}"
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Export Excel">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>



        {{-- SUMMARY CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-summary-card">
                    <div class="icon text-primary">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="content">
                        <div class="label">Total Ruangan</div>
                        <div class="value">{{ $totalRooms ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-summary-card">
                    <div class="icon text-success">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="content">
                        <div class="label">Total Peminjaman</div>
                        <div class="value">{{ $totalBookings ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-summary-card">
                    <div class="icon text-warning">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="content">
                        <div class="label">Top Ruangan</div>
                        <div class="value">
                            {{ $topRooms[0]->nama ?? '-' }}
                        </div>
                        <div class="sub small text-muted">
                            Ruangan paling sering dipakai
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-summary-card">
                    <div class="icon text-info">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="content">
                        <div class="label">Top Pengguna</div>
                        <div class="value">
                            {{ $topUsers[0]->name ?? '-' }}
                        </div>
                        <div class="sub small text-muted">
                            Pengguna paling aktif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TREND CHART --}}
        <div class="card border-0 shadow-sm stats-card mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div class="fw-bold">
                    <i class="fas fa-chart-line me-2 text-warning"></i>
                    Trend Peminjaman
                </div>
                <span class="badge rounded-pill bg-warning bg-opacity-10 text-white">
                    Line Chart
                </span>
            </div>

            <div class="card-body">
                <div class="chart-container trend-chart">
                    <canvas id="trendDailyChart"></canvas>
                </div>
            </div>
        </div>


        {{-- CHARTS --}}
        <div class="row g-3 mb-4">
            {{-- Top Rooms --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm stats-card h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                        <div class="fw-bold">
                            <i class="fas fa-building me-2 text-primary"></i>
                            Top Ruangan
                        </div>
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-white">
                            Grafik & Tabel
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container mb-3">
                            <canvas id="topRoomsChart"></canvas>
                        </div>

                        <div class="table-responsive">
                            <table class="modern-table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:80px;"><i class="fas fa-hashtag me-1"></i>No</th>
                                        <th><i class="fas fa-door-open me-1"></i>Ruangan</th>
                                        <th class="text-end"><i class="fas fa-chart-bar me-1"></i>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topRooms as $r)
                                        <tr>
                                            <td class="text-center">
                                                <span class="bg-opacity-10 fw-bold text-primary">{{ $loop->iteration }}</span>
                                            </td>
                                            <td class="fw-semibold">{{ $r->nama }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-success px-3 py-2">{{ $r->total }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada data
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Top Users --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm stats-card h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                        <div class="fw-bold">
                            <i class="fas fa-users me-2 text-success"></i>
                            Top Pengguna
                        </div>
                        <span class="badge rounded-pill bg-success bg-opacity-10 text-white">
                            Grafik & Tabel
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="chart-container mb-3">
                            <canvas id="topUsersChart"></canvas>
                        </div>

                        <div class="table-responsive">
                            <table class="modern-table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:80px;"><i class="fas fa-hashtag me-1"></i>No</th>
                                        <th><i class="fas fa-user me-1"></i>Pengguna</th>
                                        <th class="text-end"><i class="fas fa-chart-bar me-1"></i>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topUsers as $u)
                                        <tr>
                                            <td class="text-center">
                                                <span class="bg-opacity-10 fw-bold text-primary">{{ $loop->iteration }}</span>
                                            </td>
                                            <td class="fw-semibold">{{ $u->name }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-info px-3 py-2">{{ $u->total }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada data
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                Anda tidak memiliki akses ke halaman statistik.
            </div>
        </div>
    @endcan

</div>
@endsection

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
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
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
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Preset UX
    const presetInput = document.getElementById('presetInput');
    const startInput = document.getElementById('startInput');
    const endInput = document.getElementById('endInput');

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('preset') === 'all_time') {
        if (startInput) startInput.value = '';
        if (endInput) endInput.value = '';
    }

    [startInput, endInput].forEach(el => el && el.addEventListener('change', () => {
        if (presetInput) presetInput.value = '';
    }));
});
</script>
<script>
    // TREND HARIAN
const trendLabels = {!! json_encode($trendLabels ?? []) !!};
const trendData = {!! json_encode($trendData ?? []) !!};

const ctxT = document.getElementById('trendDailyChart');
if (ctxT) {
    new Chart(ctxT, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: trendData,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
}
</script>
@endsection