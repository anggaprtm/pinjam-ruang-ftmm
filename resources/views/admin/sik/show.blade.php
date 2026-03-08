@extends('layouts.admin')
@section('content')
@php
    $viewer = auth()->user();
    $isApplicantMember = $viewer && ! $viewer->isAdmin() && $viewer->ormawas()->where('ormawas.id', $sikApplication->ormawa_id)->exists();
    $canModerateAmendment = $viewer && ($viewer->isAdmin() || $viewer->hasRole('Kemahasiswaan') || $viewer->hasRole('Staf Kemahasiswaan'));
    $canReviseSubmission = $viewer && ($viewer->isAdmin() || $isApplicantMember) && $sikApplication->status_sik === 'need_revision';

    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Diajukan',
        'on_verification' => 'Dalam Verifikasi',
        'need_revision' => 'Memerlukan Revisi',
        'approved_final' => 'Disetujui Final',
        'issued' => 'Diterbitkan',
        'cancelled' => 'Dibatalkan',
    ];

    $stepStatusLabels = [
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'revised' => 'Perlu Revisi',
    ];

    $amendmentStatusLabels = [
        'submitted' => 'Diajukan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    $proposalUrl = $sikApplication->proposal_path ? asset('storage/' . $sikApplication->proposal_path) : null;
    $proposalExt = $sikApplication->proposal_path ? strtolower(pathinfo($sikApplication->proposal_path, PATHINFO_EXTENSION)) : null;
    $proposalPreviewable = in_array($proposalExt, ['pdf'], true);

    $suratUrl = $sikApplication->surat_permohonan_path ? asset('storage/' . $sikApplication->surat_permohonan_path) : null;
    $suratExt = $sikApplication->surat_permohonan_path ? strtolower(pathinfo($sikApplication->surat_permohonan_path, PATHINFO_EXTENSION)) : null;
    $suratPreviewable = in_array($suratExt, ['pdf'], true);

    $issuedSikUrl = $sikApplication->issued_document_path ? asset('storage/' . $sikApplication->issued_document_path) : null;
    $issuedSikExt = $sikApplication->issued_document_path ? strtolower(pathinfo($sikApplication->issued_document_path, PATHINFO_EXTENSION)) : null;
    $issuedSikPreviewable = in_array($issuedSikExt, ['pdf'], true);

    $translateEvent = function ($event) {
        $map = [
            'submitted' => 'Pengajuan dibuat',
            'amendment_submitted' => 'Amendment diajukan',
            'amendment_approve' => 'Amendment disetujui',
            'amendment_approved' => 'Amendment disetujui',
            'amendment_reject' => 'Amendment ditolak',
            'amendment_rejected' => 'Amendment ditolak',
            'issued' => 'SIK diterbitkan',
            'step_issue' => 'Step penerbitan diproses',
            'step_approve' => 'Step disetujui',
            'step_reject' => 'Step ditolak',
            'step_revise' => 'Diminta revisi',
            'revision_resubmitted' => 'Revisi dikirim ulang',
        ];

        if (isset($map[$event])) {
            return $map[$event];
        }

        if (\Illuminate\Support\Str::startsWith($event, 'step_')) {
            return 'Proses step: ' . str_replace('_', ' ', \Illuminate\Support\Str::after($event, 'step_'));
        }

        return \Illuminate\Support\Str::title(str_replace('_', ' ', $event));
    };
@endphp

<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Detail Pengajuan SIK Proker: {{ $sikApplication->programItem->nama_rencana ?? $sikApplication->judul_final_kegiatan }}</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"><strong>Ormawa:</strong> {{ $sikApplication->ormawa->nama ?? '-' }}</div>
            <div class="col-md-6"><strong>Status:</strong> {{ $statusLabels[$sikApplication->status_sik] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $sikApplication->status_sik)) }}</div>
            <div class="col-md-12 mt-2"><strong>Judul:</strong> {{ $sikApplication->judul_final_kegiatan }}</div>
            <div class="col-md-6 mt-2"><strong>Timeline:</strong> {{ optional($sikApplication->timeline_mulai_final)->format('d M Y') }} - {{ optional($sikApplication->timeline_selesai_final)->format('d M Y') }}</div>
            <div class="col-md-6 mt-2"><strong>No SIK e-office:</strong> {{ $sikApplication->nomor_sik_eoffice ?? '-' }}</div>
            @if(!empty($sikApplication->catatan_terakhir))
                <div class="col-md-12 mt-2"><strong>Catatan Terakhir:</strong> {{ $sikApplication->catatan_terakhir }}</div>
            @endif
            @if($canReviseSubmission)
                <div class="col-md-12 mt-3">
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0">
                        <div>
                            <strong>Pengajuan perlu revisi.</strong>
                            Silakan perbarui data/dokumen lalu kirim ulang agar proses verifikasi dapat dilanjutkan.
                        </div>
                        <a href="{{ route('admin.sik.edit', $sikApplication->id) }}" class="btn btn-warning ms-3">
                            Revisi Pengajuan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Dokumen Pengajuan</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Proposal</h6>
                    @if($proposalUrl)
                        <div class="d-flex gap-2 flex-wrap">
                            @if($proposalPreviewable)
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#previewProposalModal">Preview</button>
                            @else
                                <span class="badge bg-light text-dark">Preview tersedia untuk file PDF</span>
                            @endif
                            <a href="{{ $proposalUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">Unduh</a>
                        </div>
                    @else
                        <span class="text-muted">Dokumen proposal belum tersedia.</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Surat Permohonan</h6>
                    @if($suratUrl)
                        <div class="d-flex gap-2 flex-wrap">
                            @if($suratPreviewable)
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#previewSuratModal">Preview</button>
                            @else
                                <span class="badge bg-light text-dark">Preview tersedia untuk file PDF</span>
                            @endif
                            <a href="{{ $suratUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">Unduh</a>
                        </div>
                    @else
                        <span class="text-muted">Dokumen surat permohonan belum tersedia.</span>
                    @endif
                </div>
            </div>
            <div class="col-md-12">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="mb-2">Dokumen SIK Terbit</h6>
                    @if($issuedSikUrl)
                        <div class="d-flex gap-2 flex-wrap">
                            @if($issuedSikPreviewable)
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#previewIssuedSikModal">Preview</button>
                            @else
                                <span class="badge bg-light text-dark">Preview tersedia untuk file PDF</span>
                            @endif
                            <a href="{{ $issuedSikUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">Unduh SIK Terbit</a>
                        </div>
                    @else
                        <span class="text-muted">Dokumen SIK terbit belum diunggah.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($proposalUrl && $proposalPreviewable)
<div class="modal fade" id="previewProposalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Proposal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe src="{{ $proposalUrl }}" title="Preview Proposal" class="w-100 h-100 border-0"></iframe>
            </div>
        </div>
    </div>
</div>
@endif

@if($suratUrl && $suratPreviewable)
<div class="modal fade" id="previewSuratModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Surat Permohonan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe src="{{ $suratUrl }}" title="Preview Surat Permohonan" class="w-100 h-100 border-0"></iframe>
            </div>
        </div>
    </div>
</div>
@endif

@if($issuedSikUrl && $issuedSikPreviewable)
<div class="modal fade" id="previewIssuedSikModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Dokumen SIK Terbit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe src="{{ $issuedSikUrl }}" title="Preview Dokumen SIK Terbit" class="w-100 h-100 border-0"></iframe>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Tracker Verifikasi</strong></div>
    <div class="card-body table-responsive">
        @php
            $currentPending = $sikApplication->steps->where('status_step', 'pending')->sortBy('step_order')->first();
            $flowStepsByOrder = optional($sikApplication->flow)->steps ? $sikApplication->flow->steps->keyBy('step_order') : collect();
            $viewerRoles = $viewer ? $viewer->roles->pluck('title')->map(fn($r) => strtolower(trim($r))) : collect();
        @endphp
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Role Target</th>
                    <th>Status</th>
                    <th>Due</th>
                    <th>Aksi / Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sikApplication->steps as $step)
                    @php
                        $flowStep = $flowStepsByOrder->get($step->step_order);
                        $actionType = $flowStep->action_type ?? 'verify';
                        $canActStep = $viewer && ! $isApplicantMember && ($viewer->isAdmin() || $viewerRoles->contains(strtolower(trim($step->role_target))));
                    @endphp
                    <tr>
                        <td>#{{ $step->step_order }}</td>
                        <td>{{ $step->role_target }}</td>
                        <td>{{ $stepStatusLabels[$step->status_step] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $step->status_step)) }}</td>
                        <td>{{ optional($step->due_at)->format('d M Y H:i') ?? '-' }}</td>
                        <td>
                            @php
                                $hasUnfinishedPreviousStep = $sikApplication->steps
                                    ->where('step_order', '<', $step->step_order)
                                    ->contains(fn($prev) => ! in_array($prev->status_step, ['approved'], true));
                            @endphp
                            @if($step->status_step === 'pending')
                                @if($hasUnfinishedPreviousStep)
                                    <span class="badge bg-light text-dark">Menunggu step sebelumnya</span>
                                @elseif($sikApplication->status_sik === 'need_revision' && $currentPending && (int)$currentPending->step_order === (int)$step->step_order)
                                    <span class="badge bg-warning text-dark">Menunggu revisi Ormawa</span>
                                    @if(!empty($sikApplication->catatan_terakhir))
                                        <div class="text-muted mt-1">{{ $sikApplication->catatan_terakhir }}</div>
                                    @endif
                                @elseif($currentPending && (int)$currentPending->step_order === (int)$step->step_order && $canActStep)
                                    @if($actionType === 'issue')
                                        <form method="POST" action="{{ route('admin.sik.processStep', $sikApplication->id) }}" class="d-inline-flex align-items-center gap-2" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="step_order" value="{{ $step->step_order }}">
                                            <input type="hidden" name="action" value="issue">
                                            <input type="text" name="nomor_sik_eoffice" class="form-control form-control-sm" style="width:220px" placeholder="Nomor SIK e-office" required>
                                            <input type="file" name="issued_document" class="form-control form-control-sm" style="width:230px" accept=".pdf" required>
                                            <button class="btn btn-xs btn-primary">Terbitkan Surat Izin Kegiatan</button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-xs btn-success js-open-step-modal" data-action="approve" data-step-order="{{ $step->step_order }}">Setujui</button>
                                        <button type="button" class="btn btn-xs btn-warning js-open-step-modal" data-action="revise" data-step-order="{{ $step->step_order }}">Minta Revisi</button>
                                        <button type="button" class="btn btn-xs btn-danger js-open-step-modal" data-action="reject" data-step-order="{{ $step->step_order }}">Tolak</button>
                                    @endif
                                @elseif($currentPending && (int)$currentPending->step_order !== (int)$step->step_order)
                                    <span class="badge bg-light text-dark">Menunggu step sebelumnya</span>
                                @else
                                    <span class="text-muted">Menunggu verifikator</span>
                                @endif
                            @else
                                <span class="text-muted">{{ $step->notes ?: '-' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="stepActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.sik.processStep', $sikApplication->id) }}" id="stepActionForm">
            @csrf
            <input type="hidden" name="step_order" id="stepActionOrder">
            <input type="hidden" name="action" id="stepActionType">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stepActionTitle">Konfirmasi Aksi Verifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="stepActionHelp" class="mb-2 text-muted">Tambahkan catatan untuk proses verifikasi ini.</p>
                    <label for="stepActionNotes" class="form-label" id="stepActionLabel">Catatan Verifikasi</label>
                    <textarea name="notes" id="stepActionNotes" rows="4" class="form-control" placeholder="Tulis catatan..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="stepActionSubmit">Kirim</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($sikApplication->status_sik === 'approved_final' && !optional($sikApplication->flow)->steps?->contains('action_type', 'issue') && $canModerateAmendment)
<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Terbitkan Surat Izin Kegiatan</strong></div>
    <div class="card-body">
        <form action="{{ route('admin.sik.issue', $sikApplication->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Nomor SIK dari e-office</label>
                    <input type="text" name="nomor_sik_eoffice" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unggah Dokumen SIK (PDF)</label>
                    <input type="file" name="issued_document" class="form-control" accept=".pdf" required>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary w-100" type="submit">Terbitkan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Perubahan Judul/Timeline (Amendment)</strong>
        @if($canModerateAmendment)
            <form method="POST" action="{{ route('admin.sik.amendments.toggleAccess', $sikApplication->id) }}" class="d-inline">
                @csrf
                <input type="hidden" name="is_open" value="{{ $sikApplication->is_amendment_open ? 0 : 1 }}">
                <button class="btn btn-sm {{ $sikApplication->is_amendment_open ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
                    {{ $sikApplication->is_amendment_open ? 'Tutup Akses Perubahan' : 'Buka Akses Perubahan' }}
                </button>
            </form>
        @endif
    </div>
    <div class="card-body">
        @if($isApplicantMember && $sikApplication->is_amendment_open)
            <button class="btn btn-warning btn-sm mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#amendmentFormCollapse">Ajukan Amendment (Mendesak)</button>
            <div class="collapse" id="amendmentFormCollapse">
                <form action="{{ route('admin.sik.amendments.request', $sikApplication->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Judul Final Baru</label><input type="text" name="judul_final_kegiatan" class="form-control" value="{{ $sikApplication->judul_final_kegiatan }}" required></div>
                        <div class="col-md-3"><label class="form-label">Timeline Mulai Baru</label><input type="date" name="timeline_mulai_final" class="form-control" value="{{ optional($sikApplication->timeline_mulai_final)->format('Y-m-d') }}" required></div>
                        <div class="col-md-3"><label class="form-label">Timeline Selesai Baru</label><input type="date" name="timeline_selesai_final" class="form-control" value="{{ optional($sikApplication->timeline_selesai_final)->format('Y-m-d') }}" required></div>
                        <div class="col-md-6"><label class="form-label">Rencana Tempat Baru</label><input type="text" name="rencana_tempat" class="form-control" value="{{ $sikApplication->rencana_tempat }}"></div>
                        <div class="col-md-6"><label class="form-label">Alasan Perubahan</label><input type="text" name="alasan_perubahan" class="form-control" required></div>
                        <div class="col-md-12"><button class="btn btn-warning" type="submit">Kirim Perubahan</button></div>
                    </div>
                </form>
            </div>
        @elseif($isApplicantMember)
            <div class="alert alert-info">Akses amendment belum dibuka. Silakan konfirmasi ke Kemahasiswaan jika ada kebutuhan urgent.</div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead><tr><th>#</th><th>Pemohon</th><th>Alasan</th><th>Status</th><th>Diajukan</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($sikApplication->amendments as $amendment)
                        <tr>
                            <td>{{ $amendment->id }}</td>
                            <td>{{ $amendment->requester->name ?? '-' }}</td>
                            <td>{{ $amendment->alasan_perubahan }}</td>
                            <td>{{ $amendmentStatusLabels[$amendment->status_amendment] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $amendment->status_amendment)) }}</td>
                            <td>{{ optional($amendment->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                @if($canModerateAmendment && $amendment->status_amendment === 'submitted')
                                    <form method="POST" action="{{ route('admin.sik.amendments.process', [$sikApplication->id, $amendment->id]) }}" class="d-inline">@csrf<input type="hidden" name="action" value="approve"><button class="btn btn-xs btn-success">Setujui</button></form>
                                    <form method="POST" action="{{ route('admin.sik.amendments.process', [$sikApplication->id, $amendment->id]) }}" class="d-inline">@csrf<input type="hidden" name="action" value="reject"><button class="btn btn-xs btn-danger">Tolak</button></form>
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
            <thead><tr><th>Waktu</th><th>Event</th><th>Aktor</th></tr></thead>
            <tbody>
                @forelse($sikApplication->histories as $history)
                    <tr><td>{{ optional($history->created_at)->format('d M Y H:i') }}</td><td>{{ $translateEvent($history->event) }}</td><td>{{ $history->actor->name ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('stepActionModal');
    if (!modalEl) return;

    const stepModal = new bootstrap.Modal(modalEl);
    const actionInput = document.getElementById('stepActionType');
    const orderInput = document.getElementById('stepActionOrder');
    const title = document.getElementById('stepActionTitle');
    const label = document.getElementById('stepActionLabel');
    const notes = document.getElementById('stepActionNotes');
    const submit = document.getElementById('stepActionSubmit');

    const config = {
        approve: { title: 'Setujui Verifikasi', label: 'Catatan Persetujuan', submit: 'Setujui', required: false, btnClass: 'btn-success' },
        revise: { title: 'Minta Revisi', label: 'Catatan Revisi', submit: 'Kirim Permintaan Revisi', required: true, btnClass: 'btn-warning' },
        reject: { title: 'Tolak Pengajuan', label: 'Catatan Penolakan', submit: 'Tolak Pengajuan', required: true, btnClass: 'btn-danger' },
    };

    document.querySelectorAll('.js-open-step-modal').forEach((btn) => {
        btn.addEventListener('click', function () {
            const action = this.dataset.action;
            const stepOrder = this.dataset.stepOrder;
            const conf = config[action] || config.approve;

            actionInput.value = action;
            orderInput.value = stepOrder;
            title.textContent = conf.title;
            label.textContent = conf.label;
            submit.textContent = conf.submit;
            notes.required = conf.required;
            notes.value = '';
            submit.className = 'btn ' + conf.btnClass;

            stepModal.show();
        });
    });
});
</script>
@endsection
