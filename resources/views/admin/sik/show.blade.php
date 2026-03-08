@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Detail SIK #{{ $sikApplication->id }}</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"><strong>Ormawa:</strong> {{ $sikApplication->ormawa->nama ?? '-' }}</div>
            <div class="col-md-6"><strong>Status:</strong> {{ strtoupper($sikApplication->status_sik) }}</div>
            <div class="col-md-12 mt-2"><strong>Judul:</strong> {{ $sikApplication->judul_final_kegiatan }}</div>
            <div class="col-md-6 mt-2"><strong>Timeline:</strong> {{ optional($sikApplication->timeline_mulai_final)->format('d M Y') }} - {{ optional($sikApplication->timeline_selesai_final)->format('d M Y') }}</div>
            <div class="col-md-6 mt-2"><strong>No SIK e-office:</strong> {{ $sikApplication->nomor_sik_eoffice ?? '-' }}</div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Tracker Verifikasi</strong></div>
    <div class="card-body table-responsive">
        @php
            $currentPending = $sikApplication->steps->where('status_step', 'pending')->sortBy('step_order')->first();
        @endphp
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Role Target</th>
                    <th>Status</th>
                    <th>Due</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sikApplication->steps as $step)
                    <tr>
                        <td>#{{ $step->step_order }}</td>
                        <td>{{ $step->role_target }}</td>
                        <td>{{ strtoupper($step->status_step) }}</td>
                        <td>{{ optional($step->due_at)->format('d M Y H:i') ?? '-' }}</td>
                        <td>
                            @if($step->status_step === 'pending')
                                @if($currentPending && (int)$currentPending->step_order === (int)$step->step_order)
                                <form method="POST" action="{{ route('admin.sik.processStep', $sikApplication->id) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="step_order" value="{{ $step->step_order }}">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="notes" value="{{ old('notes') }}">
                                    <button class="btn btn-xs btn-success">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.sik.processStep', $sikApplication->id) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="step_order" value="{{ $step->step_order }}">
                                    <input type="hidden" name="action" value="revise">
                                    <input type="text" name="notes" class="form-control form-control-sm d-inline-block" style="width:180px" placeholder="Catatan revisi" required>
                                    <button class="btn btn-xs btn-warning">Kirim Revisi</button>
                                </form>
                                <form method="POST" action="{{ route('admin.sik.processStep', $sikApplication->id) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="step_order" value="{{ $step->step_order }}">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="text" name="notes" class="form-control form-control-sm d-inline-block" style="width:180px" placeholder="Alasan penolakan" required>
                                    <button class="btn btn-xs btn-danger">Tolak</button>
                                </form>
                                @else
                                    <span class="badge bg-light text-dark">Menunggu step sebelumnya</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($sikApplication->status_sik === 'approved_final')
<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Terbitkan SIK</strong></div>
    <div class="card-body">
        <form action="{{ route('admin.sik.issue', $sikApplication->id) }}" method="POST">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Nomor SIK dari e-office</label>
                    <input type="text" name="nomor_sik_eoffice" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" type="submit">Terbitkan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header"><strong>Riwayat</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Event</th>
                    <th>Aktor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sikApplication->histories as $history)
                    <tr>
                        <td>{{ optional($history->created_at)->format('d M Y H:i') }}</td>
                        <td>{{ $history->event }}</td>
                        <td>{{ $history->actor->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
