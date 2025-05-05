@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.systemCalendar') }}
    </div>

    <div class="card-body">
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.1.0/fullcalendar.min.css' />
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

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filter_kuliah">Jenis Kegiatan</label>
                        <select class="form-control select2" name="filter_kuliah" id="filter_kuliah">
                            <option value="non-kuliah" {{ request('filter_kuliah') == 'non-kuliah' ? 'selected' : '' }}>Selain Perkuliahan</option>
                            <option value="kuliah" {{ request('filter_kuliah') == 'kuliah' ? 'selected' : '' }}>Hanya Perkuliahan</option>
                            <option value="">Semua</option>
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
    <h5>Peminjam</h5>
    <ul style="list-style: none; padding: 0; display: flex; gap: 15px;">
        @foreach($userColors as $userName => $color)
            <li style="display: flex; align-items: center;">
                <span style="display: inline-block; width: 10px; height: 10px; background-color: {{ $color }}; margin-right: 5px;"></span>
                {{ $userName }}
            </li>
        @endforeach
    </ul>
</div>



@endsection

@section('scripts')
@parent
<script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.1.0/fullcalendar.min.js'></script>
<script>
    $(document).ready(function () {
            events={!! json_encode($events) !!};
            $('#calendar').fullCalendar({
                locale: 'id',
                timeFormat: 'H:mm',
                events: events,


            })
        });
</script>
@stop