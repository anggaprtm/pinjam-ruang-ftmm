@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $kegiatan->nama_kegiatan }}</h2>
                <p class="detail-sub-title mb-0">
                    Detail lengkap untuk kegiatan peminjaman ruangan.
                </p>
            </div>
            
            <div class="d-flex">
                @can('kegiatan_edit')
                    <a href="{{ route('admin.kegiatan.edit', $kegiatan->id) }}" class="btn btn-success me-2">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>
                @endcan

                <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row">
            {{-- Kolom Kiri: Detail Utama --}}
            <div class="col-lg-8">
                <h5 class="mb-3 font-weight-bold">Informasi Peminjaman</h5>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-user-circle"></i></div>
                    <div class="content">
                        <div class="label">Peminjam</div>
                        <div class="value">{{ $kegiatan->user->name ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-door-open"></i></div>
                    <div class="content">
                        <div class="label">Ruangan yang Dipinjam</div>
                        <div class="value">{{ $kegiatan->ruangan->nama ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="content">
                        <div class="label">Waktu Mulai</div>
                        <div class="value">{{ \Carbon\Carbon::parse($kegiatan->waktu_mulai)->translatedFormat('l, d F Y - H:i') }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="content">
                        <div class="label">Waktu Selesai</div>
                        <div class="value">{{ \Carbon\Carbon::parse($kegiatan->waktu_selesai)->translatedFormat('l, d F Y - H:i') }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                    <div class="content">
                        <div class="label">Deskripsi Kegiatan</div>
                        <div class="value">{{ $kegiatan->deskripsi ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-user-tag"></i></div>
                    <div class="content">
                        <div class="label">Nama Penanggung Jawab (PIC)</div>
                        <div class="value">{{ $kegiatan->nama_pic ?? '-' }}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-phone"></i></div>
                    <div class="content">
                        <div class="label">Nomor Telepon Penanggung Jawab</div>
                        <div class="value">
                            @if($kegiatan->nomor_telepon)
                                @php
                                    $phoneRaw = $kegiatan->nomor_telepon;
                                    $digits = preg_replace('/\D/', '', $phoneRaw);
                                    $waNumber = $digits;
                                    if (strpos($digits, '0') === 0) {
                                        $waNumber = '62' . substr($digits, 1);
                                    } elseif (strpos($digits, '8') === 0) {
                                        $waNumber = '62' . $digits;
                                    } else {
                                        // fallback: use digits as-is
                                        $waNumber = $digits;
                                    }
                                @endphp
                                <span class="d-inline-flex align-items-center">
                                    <a href="https://wa.me/{{ $waNumber }}" class="text-decoration-none js-wa-link" data-wa-number="{{ $waNumber }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fab fa-whatsapp me-1 text-success"></i>{{ $kegiatan->nomor_telepon }}
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2 copy-phone-btn" data-copy-value="{{ $kegiatan->nomor_telepon }}" aria-label="Salin nomor">Salin</button>
                                </span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                @if($kegiatan->surat_izin)
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-paperclip"></i></div>
                    <div class="content">
                        <div class="label">Surat Izin</div>
                        <div class="value">
                            <a href="{{ asset('storage/' . $kegiatan->surat_izin) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-download me-1"></i> Lihat/Unduh Surat
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Kolom Kanan: Status & Riwayat --}}
            <div class="col-lg-4">
                <h5 class="mb-3 font-weight-bold">Status & Riwayat</h5>
                <div class="p-3 bg-light rounded">
                    <div class="detail-item">
                        <div class="icon"><i class="fas fa-info-circle"></i></div>
                        <div class="content">
                            <div class="label">Status Saat Ini</div>
                            <div class="value">
                                @php
                                    $statusClass = str_replace('_', '-', $kegiatan->status);
                                    $statusText = '';
                                    switch ($kegiatan->status) {
                                        case 'belum_disetujui': 
                                            $statusText = 'Menunggu Verifikasi Operator'; 
                                            break;
                                        case 'verifikasi_sarpras': 
                                            $statusText = 'Operator Sudah Verifikasi'; 
                                            break;
                                        case 'verifikasi_akademik': 
                                            $statusText = 'Akademik Sudah Verifikasi'; 
                                            break;
                                        case 'disetujui': 
                                            $statusText = 'Kegiatan Disetujui'; 
                                            break;
                                        case 'ditolak': 
                                            $statusText = 'Kegiatan Ditolak'; 
                                            break;
                                        default: 
                                            $statusText = $kegiatan->status; 
                                            break;
                                    }
                                @endphp
                                <span class="badge-status badge-status-{{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="icon"><i class="fas fa-comment-dots"></i></div>
                        <div class="content">
                            <div class="label">Catatan Pemroses</div>
                            <div class="value fst-italic">"{{ $kegiatan->notes ?? 'Tidak ada catatan.' }}"</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="icon"><i class="fas fa-history"></i></div>
                        <div class="content">
                            <div class="label">Riwayat Verifikasi</div>
                            <div class="value small">
                                {{-- PERUBAHAN DIMULAI DI SINI --}}
                                @if ($kegiatan->status == 'disetujui' && !$kegiatan->verifikasi_sarpras_at && !$kegiatan->verifikasi_akademik_at && !$kegiatan->disetujui_at)
                                    <div class="text-muted fst-italic">
                                        <i class="fas fa-user-shield me-1"></i> Kegiatan dibuat oleh Admin
                                    </div>
                                @else
                                    @if($kegiatan->verifikasi_sarpras_at)
                                        <div>Verifikasi Operator: {{ \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i') }}</div>
                                    @endif
                                    @if($kegiatan->verifikasi_akademik_at)
                                        <div>Verifikasi Akademik: {{ \Carbon\Carbon::parse($kegiatan->verifikasi_akademik_at)->format('d/m/y H:i') }}</div>
                                    @endif
                                    @if($kegiatan->disetujui_at)
                                        <div>Disetujui: {{ \Carbon\Carbon::parse($kegiatan->disetujui_at)->format('d/m/y H:i') }}</div>
                                    @endif
                                    @if($kegiatan->ditolak_at)
                                        <div>Ditolak: {{ \Carbon\Carbon::parse($kegiatan->ditolak_at)->format('d/m/y H:i') }}</div>
                                    @endif
                                @endif
                                {{-- AKHIR DARI PERUBAHAN --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Intercept clicks on .js-wa-link to show confirmation before opening WhatsApp
    document.addEventListener('click', function(e) {
        // 1) Handle WhatsApp link clicks first
        const waLinkEl = e.target.closest && e.target.closest('.js-wa-link');
        if (waLinkEl) {
            e.preventDefault();
            const waNumber = waLinkEl.getAttribute('data-wa-number') || '';
            const display = waLinkEl.textContent.trim();
            const pretty = waNumber ? ('+' + waNumber) : display;
            const modalEl = document.getElementById('waConfirmModalShow');
            if (modalEl) {
                const displayEl = modalEl.querySelector('.wa-number-display');
                const confirmBtn = modalEl.querySelector('.wa-confirm-open');
                displayEl.textContent = pretty;
                confirmBtn.setAttribute('data-href', waLinkEl.href);
                if (!modalEl.__bsModal) modalEl.__bsModal = new bootstrap.Modal(modalEl);
                modalEl.__bsModal.show();

                if (!confirmBtn.__handled) {
                    confirmBtn.addEventListener('click', function() {
                        const href = this.getAttribute('data-href');
                        if (href) window.open(href, '_blank', 'noopener');
                        if (modalEl.__bsModal) modalEl.__bsModal.hide();
                    });
                    confirmBtn.__handled = true;
                }
            } else {
                // fallback
                if (confirm(`Buka WhatsApp ke ${pretty}?`)) {
                    window.open(waLinkEl.href, '_blank', 'noopener');
                }
            }
            return;
        }

        // 2) Handle copy button clicks
        const copyBtn = e.target.closest && e.target.closest('.copy-phone-btn');
        if (copyBtn) {
            e.preventDefault();
            const value = copyBtn.getAttribute('data-copy-value') || '';
            const showToast = (message) => {
                const toastEl = document.getElementById('copyToast');
                if (!toastEl) return alert(message);
                const body = toastEl.querySelector('.toast-body');
                body.textContent = message;
                const bsToast = new bootstrap.Toast(toastEl);
                bsToast.show();
            };

            if (navigator.clipboard && value) {
                navigator.clipboard.writeText(value).then(function() {
                    showToast('Nomor telepon disalin ke clipboard');
                }).catch(function() {
                    showToast('Gagal menyalin nomor.');
                });
            } else {
                try {
                    const tmp = document.createElement('textarea');
                    tmp.value = value;
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand('copy');
                    document.body.removeChild(tmp);
                    showToast('Nomor telepon disalin ke clipboard');
                } catch (err) {
                    showToast('Gagal menyalin nomor.');
                }
            }
        }
    });
});
</script>
<!-- WA confirm modal for show page -->
<div class="modal fade" id="waConfirmModalShow" tabindex="-1" aria-labelledby="waConfirmModalShowLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="waConfirmModalShowLabel">Konfirmasi WhatsApp</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">Anda akan membuka WhatsApp ke nomor: <strong class="wa-number-display"></strong></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success wa-confirm-open">Buka WhatsApp</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast for copy feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
  <div id="copyToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Clipboard</strong>
      <small class="text-muted"></small>
      <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">Tersalin</div>
  </div>
</div>
@endsection
