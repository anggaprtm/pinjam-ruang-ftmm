@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $ruangan->nama }}</h2>
                <p class="detail-sub-title mb-0">Detail dan Jadwal Penggunaan Ruangan</p>
            </div>
            <a href="{{ route('admin.ruangan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row">
            {{-- Kolom Kiri: Detail Info Ruangan --}}
            <div class="col-lg-5">
                <h5 class="mb-3 font-weight-bold">Informasi Ruangan</h5>
                {{-- === TAMBAHAN: Menampilkan Foto Ruangan === --}}
                @if($ruangan->foto) 
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $ruangan->foto) }}" alt="{{ $ruangan->nama }}" class="img-fluid rounded shadow-sm">
                    </div>
                @else
                    <div class="mb-3">
                        <img src="{{ asset('assets/img/unsplash/ruangan_default.jpg') }}" alt="Default Image" class="img-fluid rounded shadow-sm">
                    </div>
                @endif
                {{-- === AKHIR TAMBAHAN === --}}
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.ruangan.fields.kapasitas') }}</div>
                        <div class="value">{{ $ruangan->kapasitas }} Orang</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-info-circle"></i></div>
                    <div class="content">
                        <div class="label">Status</div>
                        <div class="value">
                            @if($ruangan->is_active)
                                <span class="badge-status badge-status-aktif">Aktif</span>
                            @else
                                <span class="badge-status badge-status-tidak-aktif">Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.ruangan.fields.deskripsi') }}</div>
                        <div class="value">{{ $ruangan->deskripsi ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Kalender Mini --}}
            <div class="col-lg-7">
                <h5 class="mb-3 font-weight-bold">Jadwal Ruangan</h5>
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        initialView: 'dayGridMonth',
        locale: 'id',
        buttonText: {
            month: 'Bulan',
            week: 'Minggu',
        },
        events: {!! json_encode($events) !!},
        editable: false,
        // Membuat event tidak bisa diklik karena ini hanya untuk display
        eventClick: function(info) {
            info.jsEvent.preventDefault(); 
        }
    });
    calendar.render();
});
</script>
@endsection
