@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.systemCalendar') }}
    </div>

    <div class="card-body">
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
        <form>
            <div class="row align-items-end">
                <!-- Filter Ruangan -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="ruangan_id">Ruangan</label> 
                        <select class="form-control select2" name="ruangan_id" id="ruangan_id">
                            @foreach($ruangan as $id => $ruangan)
                                <option value="{{ $id }}" {{ request()->input('ruangan_id') == $id ? 'selected' : '' }}>{{ $ruangan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter Peminjam -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="user_id">Peminjam</label>
                        <select class="form-control select2" name="user_id" id="user_id">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ request()->input('user_id') == $id ? 'selected' : '' }}>{{ $user }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tombol Filter -->
                <div class="col-md-3">
                    <div class="form-group d-flex align-items-center">
                        <button class="btn btn-filter btn-primary">
                            <i class="fas fa-fw fa-filter"></i>&nbsp Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <div id='calendar'></div>
    </div>
</div>

<div class="calendar-legend" style="margin-top: 20px;">
    <h5>Pengguna</h5>
    <ul style="list-style: none; padding: 0; display: flex; gap: 15px;">
        @foreach($userColors as $userName => $color)
            <li style="display: flex; align-items: center;">
                <span style="display: inline-block; width: 10px; height: 10px; background-color: {{ $color }}; margin-right: 5px;"></span>
                {{ $userName }}
            </li>
        @endforeach
    </ul>
</div>

<!-- Modal Preview Agenda -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="preview-title" class="modal-title">Detail Agenda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Waktu:</strong> <span id="preview-tanggal"></span></p>
        <p><strong>Ruangan:</strong> <span id="preview-ruangan"></span></p>
        <p><strong>Pengguna:</strong> <span id="preview-pic"></span></p>
        <p><strong>Keterangan:</strong> <span id="preview-keterangan"></span></p>
      </div>
      <div class="modal-footer">
        <a href="#" id="edit-link" class="btn btn-primary">Edit</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>




@endsection

@section('scripts')
@parent
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales-all.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'id',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            views: {
                timeGridWeek: { buttonText: 'Minggu' },
                timeGridDay: { buttonText: 'Hari' },
                listMonth: { buttonText: 'Daftar' }
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari',
                list: 'Daftar'
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();

                const event = info.event;
                const props = event.extendedProps;

                // Set isi preview modal
                document.getElementById('preview-title').textContent = event.title;
                document.getElementById('preview-tanggal').textContent = new Date(event.start).toLocaleString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                }) + ' | ' + event.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' - ' +
                event.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('preview-pic').textContent = props.user_name ?? '-';
                document.getElementById('preview-ruangan').textContent = props.ruangan_nama ?? '-';
                document.getElementById('preview-keterangan').textContent = props.keterangan ?? '-';
                // Edit button link
                document.getElementById('edit-link').href = `/admin/kegiatan/${event.id}/edit`;

                // Tampilkan modal
                new bootstrap.Modal(document.getElementById('previewModal')).show();
            },
            events: {!! json_encode($events) !!}
        });
        calendar.render();
    });
</script>

@stop