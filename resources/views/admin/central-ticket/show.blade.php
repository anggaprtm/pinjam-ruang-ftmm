@extends('layouts.admin')

@section('styles')
<style>
    .detail-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        margin-bottom: 5px;
    }
    .detail-value {
        font-size: 0.92rem;
        color: #1a1d23;
        font-weight: 500;
    }

    .chat-bubble {
        max-width: 78%;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.9rem;
        line-height: 1.55;
    }
    .chat-bubble.staff {
        background: #3b82f6;
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .chat-bubble.reporter {
        background: #fff;
        border: 1px solid #e5e7eb;
        color: #1a1d23;
        border-bottom-left-radius: 4px;
    }
    .chat-meta-staff { color: rgba(255,255,255,0.65); font-size: 0.72rem; }
    .chat-meta-reporter { color: #9ca3af; font-size: 0.72rem; }

    .info-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }
    .info-card-header {
        padding: 12px 18px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-card-header i { color: #3b82f6; font-size: 0.85rem; }
    .info-card-body { padding: 18px; }

    .star-icon { font-size: 1.1rem; }

    .section-title {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #374151;
    }
</style>
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0" style="color:#1a1d23;">
                <i class="fas fa-ticket-alt me-2 text-primary"></i>Detail Tiket
            </h4>
            <span class="badge bg-light border text-secondary fw-semibold shadow-sm" style="font-size:0.78rem; border-radius:8px;">
                {{ $ticket->code }}
            </span>
            
            {{-- BADGE KATEGORI --}}
            @php
                $catLabel = match($ticket->category) {
                    'usi'      => 'Unit Sistem Informasi',
                    'sarpras'  => 'Sarana Prasarana',
                    'akademik' => 'Akademik',
                    default    => 'Umum',
                };
                $catColor = match($ticket->category) {
                    'usi'      => 'bg-primary text-white',
                    'sarpras'  => 'bg-warning text-dark',
                    'akademik' => 'bg-info text-dark',
                    default    => 'bg-secondary text-white',
                };
            @endphp
            <span class="badge {{ $catColor }} fw-semibold shadow-sm" style="font-size:0.75rem; border-radius:8px;">
                <i class="fas fa-tags me-1"></i> {{ $catLabel }}
            </span>
        </div>
        <p class="text-muted small mb-0">Informasi lengkap mengenai aduan yang masuk dari sistem TickTrace.</p>
    </div>
    <a href="{{ route('admin.central-tickets.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-4">

    {{-- ═══ KOLOM KIRI ═══ --}}
    <div class="col-lg-8">

        {{-- Isi Laporan --}}
        <div class="info-card mb-4">
            <div class="info-card-header">
                <i class="fas fa-align-left"></i> Isi Laporan
            </div>
            <div class="info-card-body">
                <h5 class="fw-bold mb-3" style="color:#1a1d23;">{{ $ticket->title }}</h5>
                <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #e9ecef; min-height:140px; white-space:pre-wrap; font-size:0.92rem; color:#374151; line-height:1.65;">{{ $ticket->description }}</div>

                @if($ticket->attachment_url)
                <div class="mt-3 p-3 border rounded d-flex align-items-center justify-content-between"
                     style="background:#f8fbff; border-color:#bfdbfe !important; border-radius:10px !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:38px;height:38px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-paperclip" style="color:#3b82f6;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:0.88rem;">Lampiran Pendukung</div>
                            <div class="text-muted" style="font-size:0.78rem;">File tersedia di server TickTrace</div>
                        </div>
                    </div>
                    <a href="{{ $ticket->attachment_url }}" target="_blank"
                       class="btn btn-sm btn-primary" style="border-radius:8px; font-size:0.82rem;">
                        <i class="fas fa-external-link-alt me-1"></i> Lihat
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Riwayat Percakapan --}}
        <div class="info-card mb-4">
            <div class="info-card-header">
                <i class="fas fa-comments"></i> Riwayat Percakapan
                <span class="ms-auto badge bg-primary bg-opacity-10 text-primary" style="font-size:0.7rem; border-radius:6px; font-weight:600;">
                    {{ $ticket->replies->count() }} Pesan
                </span>
            </div>
            <div class="info-card-body" style="max-height:420px; overflow-y:auto; background:#f8f9fa; padding: 20px;">
                @forelse($ticket->replies as $reply)
                    @php $isStaff = in_array($reply->replier_role, ['admin_nexus', 'admin']); @endphp
                    <div class="d-flex mb-3 {{ $isStaff ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="chat-bubble {{ $isStaff ? 'staff' : 'reporter' }}">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                <span class="fw-semibold" style="font-size:0.83rem;">
                                    {{ $reply->replier_name }}
                                    @if($isStaff)
                                        <i class="fas fa-check-circle ms-1"
                                           style="font-size:0.75rem; color:{{ $reply->replier_role == 'admin_nexus' ? 'rgba(255,255,255,0.7)' : '#fbbf24' }};"
                                           title="{{ $reply->replier_role == 'admin_nexus' ? 'Admin FTMM-Nexus' : 'Admin TickTrace' }}"></i>
                                    @endif
                                    @if($reply->replier_role != 'admin_nexus')
                                        <em class="{{ $isStaff ? 'chat-meta-staff' : 'chat-meta-reporter' }}" style="font-size:0.72rem; font-weight:400;">
                                            via TickTrace
                                        </em>
                                    @endif
                                </span>
                                <small class="{{ $isStaff ? 'chat-meta-staff' : 'chat-meta-reporter' }}">
                                    {{ \Carbon\Carbon::parse($reply->created_at)->format('d M, H:i') }}
                                </small>
                            </div>
                            <div style="white-space:pre-wrap;">{{ $reply->content }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="far fa-comment-dots fa-2x mb-2 d-block" style="opacity:0.25;"></i>
                        <p class="small mb-0">Belum ada percakapan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Form Balas --}}
        <div class="info-card mb-4">
            <div class="info-card-header">
                <i class="fas fa-reply"></i> Kirim Balasan
            </div>
            <div class="info-card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('admin.central-tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            Isi Tanggapan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="content" rows="4" required
                                  placeholder="Ketik balasan untuk pelapor..."
                                  style="font-size:0.9rem; resize:vertical;"></textarea>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Update Status <span class="text-muted fw-normal">(opsional)</span></label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="open"        {{ $ticket->status == 'open'        ? 'selected' : '' }}>Open</option>
                                <option value="onprogress"  {{ $ticket->status == 'onprogress'  ? 'selected' : '' }}>On Progress</option>
                                <option value="resolved"    {{ $ticket->status == 'resolved'    ? 'selected' : '' }}>Resolved — Selesai</option>
                                <option value="rejected"    {{ $ticket->status == 'rejected'    ? 'selected' : '' }}>Rejected — Tolak</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button type="submit" class="btn btn-primary" style="border-radius:8px; font-size:0.88rem;">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Sekarang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- /col kiri --}}

    {{-- ═══ KOLOM KANAN ═══ --}}
    <div class="col-lg-4">

        {{-- Status & Prioritas --}}
        <div class="info-card mb-4">
            <div class="info-card-header">
                <i class="fas fa-info-circle"></i> Status & Prioritas
            </div>
            <div class="info-card-body">
                {{-- Status --}}
                @php
                    $sColor = match($ticket->status) {
                        'open'       => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                        'onprogress' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
                        'resolved'   => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
                        'rejected'   => ['bg' => '#fff1f2', 'text' => '#9f1239', 'border' => '#fecdd3'],
                        default      => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#d1d5db'],
                    };
                    $pColor = match($ticket->priority) {
                        'low'    => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                        'medium' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
                        'high'   => ['bg' => '#fff1f2', 'text' => '#9f1239', 'border' => '#fecdd3'],
                        default  => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#d1d5db'],
                    };
                @endphp
                <div class="mb-4">
                    <div class="detail-label mb-2">Status Saat Ini</div>
                    <div style="background:{{ $sColor['bg'] }}; border:1px solid {{ $sColor['border'] }}; border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:8px;">
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $sColor['text'] }}; display:inline-block; flex-shrink:0;"></span>
                        <span style="font-size:0.88rem; font-weight:700; color:{{ $sColor['text'] }}; text-transform:uppercase; letter-spacing:0.5px;">
                            {{ $ticket->status == 'onprogress' ? 'On Progress' : ucfirst($ticket->status) }}
                        </span>
                    </div>
                </div>
                <div>
                    <div class="detail-label mb-2">Tingkat Prioritas</div>
                    <div style="background:{{ $pColor['bg'] }}; border:1px solid {{ $pColor['border'] }}; border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-flag" style="color:{{ $pColor['text'] }}; font-size:0.8rem;"></i>
                        <span style="font-size:0.88rem; font-weight:700; color:{{ $pColor['text'] }}; text-transform:uppercase; letter-spacing:0.5px;">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Penilaian (jika ada) --}}
        @if($ticket->rating)
        <div class="info-card mb-4">
            <div class="info-card-header">
                <i class="fas fa-star" style="color:#f59e0b !important;"></i>
                <span>Penilaian Pelapor</span>
            </div>
            <div class="info-card-body text-center">
                <div class="mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star star-icon {{ $i <= $ticket->rating ? '' : 'text-light' }}"
                           style="{{ $i <= $ticket->rating ? 'color:#f59e0b;' : '' }}"></i>
                    @endfor
                </div>
                <div class="fw-bold mb-3" style="font-size:1.4rem; color:#1a1d23;">
                    {{ $ticket->rating }}<span class="text-muted fw-normal" style="font-size:0.9rem;"> / 5</span>
                </div>
                @if($ticket->feedback)
                <div class="p-3 rounded text-muted fst-italic small" style="background:#f8f9fa; border:1px solid #e9ecef; text-align:left; line-height:1.6;">
                    "{{ $ticket->feedback }}"
                </div>
                @else
                <span class="text-muted small">Tidak ada ulasan tertulis.</span>
                @endif
            </div>
        </div>
        @endif

        {{-- Informasi Pelapor --}}
        <div class="info-card mb-4">
            <div class="info-card-header">
                <i class="fas fa-user-circle"></i> Informasi Pelapor
            </div>
            <div class="info-card-body">
                @php
                    $initials  = strtoupper(substr($ticket->reporter_name, 0, 2));
                    $avatarBg  = $ticket->is_guest ? '#ff9800' : '#3b82f6';
                @endphp
                <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom:1px solid #f1f3f5;">
                    <div style="width:44px;height:44px;border-radius:50%;background:{{ $avatarBg }};display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:0.95rem;flex-shrink:0;">
                        {{ $initials }}
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:0.95rem; color:#1a1d23;">{{ $ticket->reporter_name }}</div>
                        <span class="badge {{ $ticket->is_guest ? 'bg-secondary' : 'bg-primary' }} bg-opacity-10 {{ $ticket->is_guest ? 'text-secondary' : 'text-primary' }}"
                              style="font-size:0.68rem; font-weight:600; border-radius:6px;">
                            {{ $ticket->is_guest ? 'Guest / Publik' : 'User Terdaftar' }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="detail-label">Email</div>
                    <div class="detail-value" style="word-break:break-all;">{{ $ticket->reporter_email }}</div>
                </div>
                <div>
                    <div class="detail-label">Tipe Akun</div>
                    <div class="detail-value">{{ $ticket->is_guest ? 'Guest / Publik' : 'User Terdaftar' }}</div>
                </div>
            </div>
        </div>

        {{-- Waktu --}}
        <div class="info-card">
            <div class="info-card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-2">
                    <i class="far fa-calendar-plus" style="color:#22c55e; font-size:0.85rem;"></i>
                    <span>Diterima:</span>
                    <span class="fw-semibold text-dark">
                        {{ \Carbon\Carbon::parse($ticket->created_at)->translatedFormat('d F Y, H:i') }}
                    </span>
                </div>
                @if($ticket->updated_at != $ticket->created_at)
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <i class="far fa-edit" style="color:#f59e0b; font-size:0.85rem;"></i>
                    <span>Diperbarui:</span>
                    <span class="fw-semibold text-dark">
                        {{ \Carbon\Carbon::parse($ticket->updated_at)->translatedFormat('d F Y, H:i') }}
                    </span>
                </div>
                @endif
            </div>
        </div>

    </div>{{-- /col kanan --}}
</div>

@endsection

@section('scripts')
@parent
@if(session('success') && $ticket->status === 'resolved')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const frontendUrl = "http://10.16.10.19";
    const waText = `Halo kak {{ $ticket->reporter_name }},\n\nLaporan Anda mengenai *{{ $ticket->title }}* dengan kode *{{ $ticket->code }}* telah selesai kami tangani.\n\nSilakan cek detail penyelesaian dan berikan ulasan Anda melalui link berikut:\n${frontendUrl}/?code={{ $ticket->code }}&email={{ $ticket->reporter_email }}\n\nTerima kasih atas laporan Anda! 🙏\n- Tim IT/Sarpras FTMM`;

    Swal.fire({
        title: 'Tiket Berhasil Diselesaikan!',
        icon: 'success',
        html: `
            <div class="text-start mt-2">
                <label class="form-label small text-muted fw-bold mb-1">Template Pesan WhatsApp:</label>
                <textarea id="waTemplate" class="form-control bg-light" rows="8" readonly style="font-size:0.82rem;">${waText}</textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fab fa-whatsapp me-1"></i> Copy & Buka WA',
        cancelButtonText: 'Tutup Saja',
        confirmButtonColor: '#25D366',
        cancelButtonColor: '#6c757d',
    }).then((result) => {
        if (result.isConfirmed) {
            navigator.clipboard.writeText(waText).then(() => {
                window.open(`https://wa.me/?text=${encodeURIComponent(waText)}`, '_blank');
            });
        }
    });
});
</script>
@endif
@endsection