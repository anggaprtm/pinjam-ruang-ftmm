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
            $flowStepsByOrder = optional($sikApplication->flow)->steps ? $sikApplication->flow->steps->keyBy('step_order') : collect();
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
                            @php
                                $flowStep = $flowStepsByOrder->get($step->step_order);
                                $actionType = $flowStep->action_type ?? 'verify';
                            @endphp
                            @if($step->status_step === 'pending')
                                @if($currentPending && (int)$currentPending->step_order === (int)$step->step_order)
                                    @if($actionType === 'issue')
                                        <form method="POST" action="{{ route('admin.sik.processStep', $sikApplication->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="step_order" value="{{ $step->step_order }}">
                                            <input type="hidden" name="action" value="issue">
                                            <input type="text" name="nomor_sik_eoffice" class="form-control form-control-sm d-inline-block" style="width:220px" placeholder="Nomor SIK e-office" required>
                                            <button class="btn btn-xs btn-primary">Issue SIK</button>
                                        </form>
                                    @else
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
                                    @endif
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

@if($sikApplication->status_sik === 'approved_final' && !optional($sikApplication->flow)->steps?->contains('action_type', 'issue'))
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

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Amendment (Perubahan Judul/Timeline)</strong></div>
    <div class="card-body">
        <form action="{{ route('admin.sik.amendments.request', $sikApplication->id) }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Judul Final Baru</label>
                    <input type="text" name="judul_final_kegiatan" class="form-control" value="{{ $sikApplication->judul_final_kegiatan }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Timeline Mulai Baru</label>
                    <input type="date" name="timeline_mulai_final" class="form-control" value="{{ optional($sikApplication->timeline_mulai_final)->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Timeline Selesai Baru</label>
                    <input type="date" name="timeline_selesai_final" class="form-control" value="{{ optional($sikApplication->timeline_selesai_final)->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rencana Tempat Baru</label>
                    <input type="text" name="rencana_tempat" class="form-control" value="{{ $sikApplication->rencana_tempat }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Alasan Perubahan</label>
                    <input type="text" name="alasan_perubahan" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-warning" type="submit">Ajukan Amendment</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pemohon</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sikApplication->amendments as $amendment)
                        <tr>
                            <td>{{ $amendment->id }}</td>
                            <td>{{ $amendment->requester->name ?? '-' }}</td>
                            <td>{{ $amendment->alasan_perubahan }}</td>
                            <td>{{ strtoupper($amendment->status_amendment) }}</td>
                            <td>{{ optional($amendment->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                @if(optional(auth()->user())->isAdmin() && $amendment->status_amendment === 'submitted')
                                    <form method="POST" action="{{ route('admin.sik.amendments.process', [$sikApplication->id, $amendment->id]) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button class="btn btn-xs btn-success">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.sik.amendments.process', [$sikApplication->id, $amendment->id]) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button class="btn btn-xs btn-danger">Reject</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada amendment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

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
