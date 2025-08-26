@extends('layouts.admin')
@section('content')

<h3 class="font-weight-bold mb-4">{{ trans('global.systemCalendar') }}</h3>

<div class="calendar-container">
    {{-- Kolom Utama untuk Kalender --}}
    <div class="calendar-main">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                {{-- Form Filter dari kode lama --}}
                <form method="GET" action="{{ route('admin.systemCalendar') }}">
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label for="ruangan_id" class="form-label fw-bold">Filter Ruangan:</label>
                            <select class="form-control select2" name="ruangan_id" id="ruangan_id" onchange="this.form.submit()">
                                @foreach($ruangan as $id => $ruangan_nama)
                                    <option value="{{ $id }}" {{ request()->input('ruangan_id') == $id ? 'selected' : '' }}>{{ $ruangan_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="user_id" class="form-label fw-bold">Filter Peminjam:</label>
                            <select class="form-control select2" name="user_id" id="user_id" onchange="this.form.submit()">
                                @foreach($users as $id => $user)
                                    <option value="{{ $id }}" {{ request()->input('user_id') == $id ? 'selected' : '' }}>{{ $user }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_kuliah" class="form-label fw-bold">Tipe Kegiatan:</label>
                            <select class="form-control select2" name="filter_kuliah" id="filter_kuliah" onchange="this.form.submit()">
                                <option value="semua" {{ request('filter_kuliah', 'semua') == 'semua' ? 'selected' : '' }}>Semua Kegiatan</option>
                                <option value="non-kuliah" {{ request('filter_kuliah', 'non-kuliah') == 'non-kuliah' ? 'selected' : '' }}>Non-Perkuliahan</option>
                                <option value="kuliah" {{ request('filter_kuliah') == 'kuliah' ? 'selected' : '' }}>Perkuliahan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success w-100" type="submit">Terapkan Filter</button>
                        </div>
                    </div>
                </form>
                
                <div id="calendar"></div>

                {{-- Legenda Warna Dinamis yang Dirapikan --}}
                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-3">Legenda Warna Peminjam:</h6>
                    <div class="calendar-legend flex-wrap">
                        @foreach($userColors as $userName => $color)
                            <div class="legend-item">
                                <span class="legend-color-box" style="background-color: {{ $color }};"></span> {{ $userName }}
                            </div>
                        @endforeach
                        <div class="legend-item"><span class="legend-color-box" style="background-color: #17a2b8;"></span> Perkuliahan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar untuk Detail Event --}}
    <div class="calendar-sidebar">
        <div class="card event-details-card" id="event-details">
            <div class="card-header"><h5 class="mb-0">Detail Acara</h5></div>
            <div class="card-body">
                <div class="event-details-placeholder"><i class="fas fa-mouse-pointer"></i><p>Pilih acara untuk melihat detail.</p></div>
                <div id="event-details-content" class="d-none"></div>
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
    const eventDetailsContent = document.getElementById('event-details-content');
    const eventDetailsPlaceholder = document.querySelector('.event-details-placeholder');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        initialView: 'dayGridMonth',
        locale: 'id',
        // PERUBAHAN 1: Menerjemahkan tombol
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
            list: 'Daftar'
        },
        views: {
            timeGridWeek: { buttonText: 'Minggu' },
            timeGridDay: { buttonText: 'Hari' },
            listMonth: { buttonText: 'Daftar' }
        },
        events: {!! json_encode($events) !!},
        editable: false,
        
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const props = info.event.extendedProps;
            const start = info.event.start;
            const end = info.event.end;

            const timeFormat = { hour: '2-digit', minute: '2-digit', hour12: false };
            const dateFormat = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

            let timeString = `${start.toLocaleTimeString([], timeFormat)} - ${end ? end.toLocaleTimeString([], timeFormat) : ''}`;

            let contentHtml = `<h5 class="detail-title">${info.event.title}</h5><hr>`;
            contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-calendar-alt"></i></div><div class="content"><div class="label">Tanggal</div><div class="value">${start.toLocaleDateString('id-ID', dateFormat)}</div></div></div>`;
            contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-clock"></i></div><div class="content"><div class="label">Waktu</div><div class="value">${timeString}</div></div></div>`;

            if (props.ruangan_nama) {
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-door-open"></i></div><div class="content"><div class="label">Ruangan</div><div class="value">${props.ruangan_nama}</div></div></div>`;
            }
            if (props.user_name) {
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-user-circle"></i></div><div class="content"><div class="label">${props.type === 'perkuliahan' ? 'Dosen/Prodi' : 'Peminjam'}</div><div class="value">${props.user_name}</div></div></div>`;
            }
            if (props.deskripsi) {
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-info-circle"></i></div><div class="content"><div class="label">Deskripsi</div><div class="value">${props.deskripsi}</div></div></div>`;
            }

            eventDetailsContent.innerHTML = contentHtml;
            eventDetailsContent.classList.remove('d-none');
            eventDetailsPlaceholder.classList.add('d-none');
        }
    });

    calendar.render();
});
</script>
@endsection
