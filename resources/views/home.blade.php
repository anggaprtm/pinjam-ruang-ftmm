@extends('layouts.admin')

@section('content')
<div class="content dashboard-home">

    {{-- WELCOME BANNER --}}
    <div class="welcome-banner mb-4">
        <h4 class="fw-bold text-white mb-1">
            Selamat Datang, {{ Auth::user()->name }} !
        </h4>

        @can('home_access')
            <p class="mb-0 text-white-50">
                Dashboard monitoring aktivitas Sarpras & Akademik.
            </p>
        @endcan

        <div id="current-time" class="mt-2 text-white fw-semibold"></div>
    </div>


    @can('home_access')

        {{-- STATISTIK --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="icon-container icon-ruangan">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="info">
                        <div class="stat-number">{{ $ruanganCount ?? 0 }}</div>
                        <div class="stat-label">Total Ruangan</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="icon-container icon-menunggu">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info">
                        <div class="stat-number">{{ $kegiatanMenungguCount ?? 0 }}</div>
                        <div class="stat-label">Menunggu Verifikasi</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="icon-container icon-disetujui">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="info">
                        <div class="stat-number">{{ $kegiatanDisetujuiCount ?? 0 }}</div>
                        <div class="stat-label">Disetujui</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="icon-container icon-total">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="info">
                        <div class="stat-number">{{ $kegiatanTotalCount ?? 0 }}</div>
                        <div class="stat-label">Total Kegiatan</div>
                    </div>
                </div>
            </div>
        </div>


        {{-- PANEL INFORMASI --}}
        <div class="row mb-4">

            {{-- STATUS DRIVER --}}
            <div class="col-lg-4 mb-3">
                <div class="card h-100 border-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="fas fa-car-side text-primary me-2"></i>
                            Status Driver
                        </h6>

                        <div class="ms-auto d-flex align-items-center gap-2 flex-shrink-0">

                            {{-- Badge Status Mobil --}}
                            @if(($isMobilOnDuty ?? false) === true)
                                <span class="badge rounded-pill bg-danger px-3 py-2">
                                    ON DUTY
                                </span>
                            @else
                                <span class="badge rounded-pill bg-success px-3 py-2">
                                    Standby
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- kalau standby tapi ada jadwal terdekat --}}
                    @if(!($isMobilOnDuty ?? false) && !empty($nextTrip))
                        <div class="px-3 pt-3">
                            <div class="alert alert-warning py-2 mb-0 small d-flex align-items-start gap-2">
                                <i class="fas fa-clock mt-1"></i>
                                <div>
                                    <div class="fw-bold">Ada jadwal terdekat</div>
                                    <div class="text-muted">
                                        {{ \Carbon\Carbon::parse($nextTrip->getRawOriginal('waktu_mulai'))->format('d M Y, H:i') }}
                                        - {{ \Carbon\Carbon::parse($nextTrip->getRawOriginal('waktu_selesai'))->format('H:i') }}
                                    </div>
                                    <div class="fw-semibold text-dark">
                                        {{ $nextTrip->tujuan ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="list-group list-group-flush flex-grow-1">
                        {{-- jika sedang ON DUTY tampilkan ongoing trip --}}
                        @if(($isMobilOnDuty ?? false) && !empty($ongoingTrip))
                            <div class="list-group-item border-0 border-top py-3">
                                {{-- Nama mobil + plat --}}
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="fw-bold text-primary" style="font-size: 1.05rem;">
                                        {{ $ongoingTrip->mobil->nama_mobil ?? '-' }}
                                    </div>

                                    @if(!empty($ongoingTrip->mobil->plat_nomor))
                                        <span class="plate-badge">
                                            {{ $ongoingTrip->mobil->plat_nomor }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Detail --}}
                                <div class="text-muted" style="font-size: .92rem;">
                                    <div class="mb-1">
                                        <i class="fas fa-user me-2"></i>
                                        <span class="fw-semibold text-dark">Driver:</span>
                                        {{ $ongoingTrip->driver->name ?? '-' }}
                                    </div>

                                    <div class="mb-1">
                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                        <span class="fw-semibold text-dark">Tujuan:</span>
                                        {{ $ongoingTrip->tujuan ?? '-' }}
                                    </div>

                                    <div>
                                        <i class="fas fa-clipboard-list me-2"></i>
                                        <span class="fw-semibold text-dark">Keperluan:</span>
                                        {{ $ongoingTrip->keperluan ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Standby view (mobil tidak dipakai) --}}
                            <div class="list-group-item border-0 border-top py-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="fw-bold text-dark" style="font-size: 1.05rem;">
                                        {{ $mobilFakultas->nama_mobil ?? 'Mobil Fakultas' }}
                                    </div>

                                    @if(!empty($mobilFakultas->plat_nomor))
                                        <span class="plate-badge">
                                            {{ $mobilFakultas->plat_nomor }}
                                        </span>
                                    @endif
                                </div>

                                <div class="text-muted" style="font-size: .92rem;">
                                    <div class="mb-1">
                                        <i class="fas fa-user me-2"></i>
                                        <span class="fw-semibold text-dark">Driver:</span>
                                        -
                                    </div>

                                    <div class="mb-1">
                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                        <span class="fw-semibold text-dark">Tujuan:</span>
                                        -
                                    </div>

                                    <div>
                                        <i class="fas fa-clipboard-list me-2"></i>
                                        <span class="fw-semibold text-dark">Keperluan:</span>
                                        -
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-0 text-center py-3 mt-auto">
                        <a href="{{ route('admin.riwayat-perjalanan.index') }}"
                        class="btn btn-sm btn-outline-secondary w-100 fw-semibold">
                            Lihat Logbook
                        </a>
                    </div>
                </div>
            </div>



            {{-- PERMINTAAN LAYANAN --}}
            <div class="col-lg-4 mb-3">
                <div class="card h-100 border-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="fas fa-concierge-bell text-primary me-2"></i>
                            Permintaan Layanan
                        </h6>

                        <div class="ms-auto flex-shrink-0">
                            @if(($pendingPermintaan->count() ?? 0) > 0)
                                <span class="badge rounded-pill bg-danger">
                                    {{ $pendingPermintaan->count() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="list-group list-group-flush flex-grow-1 dashboard-scroll">
                        @forelse($pendingPermintaan as $req)
                            <a href="{{ route('admin.permintaan-kegiatan.show', $req->id) }}"
                               class="list-group-item list-group-item-action border-light border-bottom py-3">

                                <div class="d-flex align-items-start gap-3">
                                    {{-- KIRI --}}
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-primary mb-1 text-truncate">
                                            {{ $req->nama_kegiatan ?? '-' }}
                                        </div>

                                        <div class="d-flex flex-wrap gap-2">
                                            @if($req->request_ruang)
                                                <span class="badge bg-info">
                                                    Ruang
                                                </span>
                                            @endif

                                            @if($req->request_konsumsi)
                                                <span class="badge bg-warning">
                                                    Konsumsi
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- KANAN --}}
                                    <div class="text-end flex-shrink-0">
                                        <div class="small text-muted mb-1">
                                            {{ optional($req->created_at)->diffForHumans() }}
                                        </div>

                                        <div class="small text-muted">
                                            <i class="fas fa-user me-1"></i>{{ $req->user->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>

                            </a>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                <p class="small mb-0">Tidak ada permintaan baru.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="card-footer bg-white border-0 text-center py-3">
                        <a href="{{ route('admin.permintaan-kegiatan.index') }}"
                           class="btn btn-sm btn-outline-secondary w-100 fw-semibold">
                            Kelola Permintaan
                        </a>
                    </div>
                </div>
            </div>


            {{-- BUTUH PERSETUJUAN --}}
            <div class="col-lg-4 mb-3">
                <div class="card h-100 border-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="fas fa-file-signature text-primary me-2"></i>
                            Butuh Persetujuan
                        </h6>

                        <div class="ms-auto flex-shrink-0">
                            @if(($pendingApproval->count() ?? 0) > 0)
                                <span class="badge rounded-pill bg-danger">
                                    {{ $pendingApproval->count() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="list-group list-group-flush flex-grow-1 dashboard-scroll">
                        @forelse($pendingApproval as $keg)
                            <a href="{{ route('admin.kegiatan.index') }}"
                               class="list-group-item list-group-item-action border-0 border-bottom py-3">

                                <div class="fw-bold text-dark mb-1 text-truncate">
                                    {{ $keg->nama_kegiatan ?? '-' }}
                                </div>

                                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                    <div class="small text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($keg->waktu_mulai)->format('d M, H:i') }}
                                    </div>

                                    <span class="badge bg-info text-dark">
                                        {{ $keg->ruangan->nama ?? 'TBA' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="d-flex flex-column justify-content-center align-items-center text-center text-muted flex-grow-1 py-4" style="min-height: 180px;">
                                <i class="fas fa-thumbs-up fa-2x mb-2 text-primary"></i>
                                <p class="small mb-0">Semua pengajuan telah diproses.</p>
                            </div>
                        @endforelse

                    </div>

                    <div class="card-footer bg-white border-0 text-center py-3">
                        <a href="{{ route('admin.kegiatan.index') }}"
                           class="btn btn-sm btn-outline-secondary w-100 fw-semibold">
                            Verifikasi Kegiatan
                        </a>
                    </div>
                </div>
            </div>

        </div>


        {{-- JADWAL --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-calendar-alt me-2"></i> Jadwal Pemakaian Ruang
                </h5>

                <div class="d-flex flex-wrap gap-2" role="group">
                    <button class="btn btn-sm btn-outline-success" id="copyHariIniBtn" @if($kegiatanHariIni->isEmpty()) disabled @endif>
                        <i class="fas fa-copy me-2"></i> Salin Hari Ini
                    </button>

                    <button class="btn btn-sm btn-outline-info" id="copyBesokBtn" @if($kegiatanBesok->isEmpty()) disabled @endif>
                        <i class="fas fa-file-alt me-2"></i> Salin Besok
                    </button>

                    <a href="{{ route('admin.statistics.index') }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-chart-bar me-2"></i> Lihat Statistik Lengkap
                    </a>
                </div>
            </div>

            <div class="card-body">
                <textarea id="jadwalHariIniClipboard" class="d-none">{{ trim($jadwalHariIniText ?? '') }}</textarea>
                <textarea id="jadwalBesokClipboard" class="d-none">{{ trim($jadwalBesokText ?? '') }}</textarea>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="hari-ini-tab" data-bs-toggle="tab" data-bs-target="#hari-ini" type="button" role="tab">
                            Hari Ini
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="besok-tab" data-bs-toggle="tab" data-bs-target="#besok" type="button" role="tab">
                            Besok
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-3" id="myTabContent">
                    <div class="tab-pane fade show active" id="hari-ini" role="tabpanel">
                        @include('partials.kegiatanTable', ['kegiatans' => $kegiatanHariIni])
                    </div>

                    <div class="tab-pane fade" id="besok" role="tabpanel">
                        @include('partials.kegiatanTable', ['kegiatans' => $kegiatanBesok])
                    </div>
                </div>
            </div>
        </div>

    @endcan



    {{-- USER BIASA --}}
    @cannot('home_access')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Informasi Peminjaman Ruang</h5>
            </div>
            <div class="card-body">
                <h2 class="h4">Cara Melakukan Peminjaman Ruang</h2>
                <ol class="list-group list-group-numbered">
                    <li class="list-group-item">Klik "Cari Ruang" atau icon 🔍 pada menu sidebar.</li>
                    <li class="list-group-item">Masukkan waktu mulai dan waktu selesai kegiatan.</li>
                    <li class="list-group-item">Ketikkan kapasitas ruang yang dibutuhkan.</li>
                    <li class="list-group-item">Kemudian klik "Cari". Ruang yang tersedia akan muncul di bawahnya.</li>
                    <li class="list-group-item">Pilih ruang yang tersedia, kemudian klik "Pinjam Ruang".</li>
                    <li class="list-group-item">Isi semua data yang diperlukan pada form peminjaman.</li>
                    <li class="list-group-item">Upload berkas Surat Peminjaman Ruang jika diperlukan.</li>
                    <li class="list-group-item">Klik tombol "Save" atau "Simpan".</li>
                    <li class="list-group-item">Kegiatan berhasil dibuat dan akan muncul di menu "Kegiatan" untuk diproses.</li>
                    <li class="list-group-item">Harap tunggu proses verifikasi dari pihak terkait.</li>
                    <li class="list-group-item">Ketika kegiatan telah disetujui, Anda dapat menggunakan ruangan sesuai jadwal.</li>
                </ol>
            </div>
        </div>
    @endcannot

</div>


{{-- SCRIPT: COPY CLIPBOARD --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    function buttonFeedback(button) {
        const originalText = button.innerHTML;
        const originalClass = button.className;

        button.innerHTML = '<i class="fas fa-check me-2"></i> Disalin!';
        button.className = 'btn btn-sm btn-success';

        setTimeout(() => {
            button.innerHTML = originalText;
            button.className = originalClass;
        }, 2000);
    }

    function fallbackCopyTextToClipboard(text, button) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = 0;
        textArea.style.left = 0;
        textArea.style.width = "2em";
        textArea.style.height = "2em";
        textArea.style.padding = 0;
        textArea.style.border = "none";
        textArea.style.outline = "none";
        textArea.style.boxShadow = "none";
        textArea.style.background = "transparent";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            buttonFeedback(button);
        } catch (err) {
            alert('Gagal menyalin.');
        }

        document.body.removeChild(textArea);
    }

    function copyHandler(buttonId, textareaId) {
        const button = document.getElementById(buttonId);
        const textarea = document.getElementById(textareaId);

        if (!button || !textarea) return;

        button.addEventListener('click', function() {
            const text = textarea.value || '';
            if (!text.trim()) return;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(() => buttonFeedback(button))
                    .catch(() => fallbackCopyTextToClipboard(text, button));
            } else {
                fallbackCopyTextToClipboard(text, button);
            }
        });
    }

    copyHandler('copyHariIniBtn', 'jadwalHariIniClipboard');
    copyHandler('copyBesokBtn', 'jadwalBesokClipboard');
});
</script>

@endsection
