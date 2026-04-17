@extends('layouts.admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 text-gray-800"><i class="fas fa-ticket-alt me-2 text-primary"></i>Detail Tiket #{{ $ticket->code }}</h4>
        <p class="text-muted small mb-0">Informasi lengkap mengenai aduan yang masuk dari sistem TickTrack.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.central-tickets.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    {{-- Sisi Kiri: Isi Aduan --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-align-left me-2"></i>Isi Laporan</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-3">{{ $ticket->title }}</h5>
                <div class="p-3 bg-light rounded border mb-4" style="min-height: 150px; white-space: pre-wrap;">
                    {{ $ticket->description }}
                </div>

                @if($ticket->attachment_url)
                <div class="attachment-section p-3 border rounded d-flex align-items-center justify-content-between bg-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-download fa-2x text-primary me-3"></i>
                        <div>
                            <div class="fw-bold">Lampiran Pendukung</div>
                            <div class="text-muted small">File tersedia di server TickTrack</div>
                        </div>
                    </div>
                    <a href="{{ $ticket->attachment_url }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-external-link-alt me-1"></i> Lihat File
                    </a>
                </div>
                @endif
            </div>
        </div>
        {{-- Riwayat Percakapan (Ditambahkan di sini) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-comments me-2"></i>Riwayat Percakapan</h6>
            </div>
            <div class="card-body p-4" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                @forelse($ticket->replies as $reply)
                    @php
                        $isStaff = in_array($reply->replier_role, ['admin_nexus', 'admin']);
                    @endphp
                    
                    <div class="d-flex mb-4 {{ $isStaff ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="shadow-sm rounded p-3 {{ $isStaff ? 'bg-primary text-white' : 'bg-white border' }}" style="max-width: 80%;">
                            <div class="d-flex justify-content-between align-items-center mb-2 gap-3">
                                <span class="fw-bold" style="font-size: 0.9rem;">
                                    {{ $reply->replier_name }}
                                    
                                    @if($isStaff)
                                        <i class="fas fa-check-circle ms-1 {{ $reply->replier_role == 'admin_nexus' ? 'text-light' : 'text-warning' }}" 
                                           title="{{ $reply->replier_role == 'admin_nexus' ? 'Admin FTMM-Nexus' : 'Admin TickTrack' }}"></i>
                                    @endif

                                    {{-- SENTUHAN TERAKHIR: Label via TickTrack --}}
                                    @if($reply->replier_role != 'admin_nexus')
                                        <span class="ms-1 fw-normal fst-italic {{ $isStaff ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.75rem;">
                                            (Dibalas melalui TickTrack)
                                        </span>
                                    @endif
                                </span>
                                <small class="{{ $isStaff ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.75rem;">
                                    {{ \Carbon\Carbon::parse($reply->created_at)->format('d M Y, H:i') }}
                                </small>
                            </div>
                            <div style="white-space: pre-wrap; font-size: 0.95rem;">{{ $reply->content }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="far fa-comment-dots fa-3x mb-2 opacity-25"></i>
                        <p class="mb-0 small">Belum ada percakapan. Jadilah yang pertama membalas!</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Form Balas --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-reply me-2"></i>Kirim Balasan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.central-tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    
                    {{-- Flash Message Jika Sukses / Gagal --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show small" role="alert">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Isi Pesan / Tanggapan <span class="text-danger">*</span></label>
                        <textarea class="form-control bg-light" name="content" rows="4" required placeholder="Ketik balasan untuk pelapor..."></textarea>
                    </div>
                    
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold small text-muted">Update Status (Opsional)</label>
                            <select name="status" class="form-select border-primary shadow-sm">
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="onprogress" {{ $ticket->status == 'onprogress' ? 'selected' : '' }}>On Progress</option>
                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                                <option value="rejected" {{ $ticket->status == 'rejected' ? 'selected' : '' }}>Rejected (Tolak)</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button type="submit" class="btn btn-primary shadow-sm px-4">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Sekarang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sisi Kanan: Status & Pelapor --}}
    <div class="col-lg-4">
        {{-- Card Status --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-info-circle me-2"></i>Status & Prioritas</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted d-block small fw-bold text-uppercase mb-2">Status Saat Ini</label>
                    @php
                        $statusColor = match($ticket->status) {
                            'open' => 'primary',
                            'onprogress' => 'warning',
                            'resolved' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2 shadow-sm w-100">
                        {{ strtoupper($ticket->status) }}
                    </span>
                </div>

                <div class="mb-2">
                    <label class="text-muted d-block small fw-bold text-uppercase mb-2">Tingkat Prioritas</label>
                    @php
                        $priorityColor = match($ticket->priority) {
                            'high' => 'danger',
                            'medium' => 'warning text-dark',
                            'low' => 'info',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $priorityColor }} fs-6 px-3 py-2 shadow-sm w-100">
                        {{ strtoupper($ticket->priority) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card Penilaian (Hanya Muncul Jika Sudah Dinilai) --}}
        @if($ticket->rating)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-warning"><i class="fas fa-star me-2"></i>Penilaian Pelapor</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-2">
                    {{-- Looping Bintang --}}
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star fs-4 {{ $i <= $ticket->rating ? 'text-warning' : 'text-light' }}"></i>
                    @endfor
                </div>
                <h5 class="fw-bold mb-3 text-dark">{{ $ticket->rating }} / 5</h5>
                
                @if($ticket->feedback)
                    <div class="p-3 bg-light rounded text-muted fst-italic small">
                        "{{ $ticket->feedback }}"
                    </div>
                @else
                    <span class="text-muted small">Tidak ada ulasan tertulis.</span>
                @endif
            </div>
        </div>
        @endif

        {{-- Card Pelapor --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-user-circle me-2"></i>Informasi Pelapor</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted d-block small fw-bold mb-1">Nama Lengkap</label>
                    <div class="fw-bold">{{ $ticket->reporter_name }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted d-block small fw-bold mb-1">Email</label>
                    <div>{{ $ticket->reporter_email }}</div>
                </div>
                <div class="mb-0">
                    <label class="text-muted d-block small fw-bold mb-1">Tipe Akun</label>
                    <span class="badge {{ $ticket->is_guest ? 'bg-secondary' : 'bg-primary' }}">
                        {{ $ticket->is_guest ? 'Guest / Publik' : 'User Terdaftar' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card Waktu --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center text-muted small">
                    <i class="far fa-calendar-alt me-2"></i>
                    Diterima pada: {{ \Carbon\Carbon::parse($ticket->created_at)->translatedFormat('d F Y, H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
@parent
    {{-- Trigger Auto-Copy WA Jika Status Tiket Diubah Jadi Resolved --}}
    @if(session('success') && $ticket->status === 'resolved')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Asumsi domain frontend TickTrack mu berjalan di port 8001 / IP tertentu
            // Sesuaikan domain ini dengan domain production TickTrack-mu
            const frontendUrl = "http://10.16.10.19"; 
            const waText = `Halo kak {{ $ticket->reporter_name }},\n\nLaporan Anda mengenai *{{ $ticket->title }}* dengan kode *{{ $ticket->code }}* telah selesai kami tangani.\n\nSilakan cek detail penyelesaian dan berikan ulasan Anda melalui link otomatis berikut:\n${frontendUrl}/?code={{ $ticket->code }}&email={{ $ticket->reporter_email }}\n\nTerima kasih atas laporan Anda! 🙏\n- Tim IT/Sarpras FTMM`;
            
            Swal.fire({
                title: 'Tiket Berhasil Diselesaikan!',
                text: 'Kirim notifikasi ke pelapor via WhatsApp?',
                icon: 'success',
                html: `
                    <div class="text-start mt-3">
                        <label class="form-label small text-muted fw-bold">Template Pesan WhatsApp:</label>
                        <textarea id="waTemplate" class="form-control bg-light" rows="8" readonly style="font-size: 0.85rem;">${waText}</textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fab fa-whatsapp me-1"></i> Copy & Buka WA',
                cancelButtonText: 'Tutup Saja',
                confirmButtonColor: '#25D366',
                cancelButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Copy teks ke clipboard
                    navigator.clipboard.writeText(waText).then(() => {
                        // Buka WA Web otomatis
                        window.open(`https://wa.me/?text=${encodeURIComponent(waText)}`, '_blank');
                    });
                }
            });
        });
    </script>
    @endif
@endsection
@endsection