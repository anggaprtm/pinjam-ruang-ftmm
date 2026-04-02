@extends('layouts.admin')

@section('content')
<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Remote Control Mini PC</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">

        @php
            $locations = ['lantai6', 'lantai7', 'sarpras', 'dekanat'];
        @endphp

        @foreach($locations as $loc)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <h5 class="fw-bold text-uppercase">{{ $loc }}</h5>

                        <div class="d-flex gap-2 mt-3 flex-wrap">

                            {{-- RELOAD --}}
                            <form method="POST" action="{{ route('admin.device-command.store') }}">
                                @csrf
                                <input type="hidden" name="location" value="{{ $loc }}">
                                <input type="hidden" name="command" value="reload">
                                <button class="btn btn-warning">
                                    🔄 Reload
                                </button>
                            </form>

                            {{-- RESTART --}}
                            <form method="POST" action="{{ route('admin.device-command.store') }}">
                                @csrf
                                <input type="hidden" name="location" value="{{ $loc }}">
                                <input type="hidden" name="command" value="restart">
                                <button class="btn btn-primary">
                                    ♻️ Restart App
                                </button>
                            </form>

                            {{-- SHUTDOWN --}}
                            <form method="POST" action="{{ route('admin.device-command.store') }}">
                                @csrf
                                <input type="hidden" name="location" value="{{ $loc }}">
                                <input type="hidden" name="command" value="shutdown">
                                <button class="btn btn-danger">
                                    ⛔ Shutdown
                                </button>
                            </form>

                        </div>

                    </div>
                </div>
            </div>
        @endforeach

    </div>

</div>
@endsection