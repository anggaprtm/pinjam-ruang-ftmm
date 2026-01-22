@extends('layouts.admin')
@section('content')
<div class="content">
    {{-- Salam Pembuka & Waktu --}}
    <div class="welcome-banner">
        <h4>Selamat Datang, {{ Auth::user()->name }} !</h4>
    @can('home_access')
        <p class="mb-0">Berikut adalah ringkasan aktivitas peminjaman ruangan hari ini.</p>
    @endcan
        <div id="current-time" class="fs-5 mt-2"></div>
    </div>

    {{-- TAMPILAN UNTUK ADMIN (DASHBOARD STATISTIK) --}}
    @can('home_access')
        {{-- Kartu Statistik --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-ruangan"><i class="fas fa-door-open"></i></div>
                    <div class="info"><div class="stat-number">{{ $ruanganCount ?? 0 }}</div><div class="stat-label">Total Ruangan</div></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-menunggu"><i class="fas fa-clock"></i></div>
                    <div class="info"><div class="stat-number">{{ $kegiatanMenungguCount ?? 0 }}</div><div class="stat-label">Kegiatan Menunggu</div></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-disetujui"><i class="fas fa-check-circle"></i></div>
                    <div class="info"><div class="stat-number">{{ $kegiatanDisetujuiCount ?? 0 }}</div><div class="stat-label">Kegiatan Disetujui</div></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="icon-container icon-total"><i class="fas fa-list-alt"></i></div>
                    <div class="info"><div class="stat-number">{{ $kegiatanTotalCount ?? 0 }}</div><div class="stat-label">Total Kegiatan</div></div>
                </div>
            </div>
        </div>

        {{-- Daftar Kegiatan --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-weight-bold text-primary">Daftar Kegiatan</h5>
                {{-- === PERUBAHAN: Grup Tombol Salin === --}}
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-success" id="copyHariIniBtn" @if($kegiatanHariIni->isEmpty()) disabled @endif>
                        <i class="fas fa-copy me-2"></i> Salin Jadwal Hari Ini
                    </button>
                    <button class="btn btn-sm btn-outline-info" id="copyBesokBtn" @if($kegiatanBesok->isEmpty()) disabled @endif>
                        <i class="fas fa-file-alt me-2"></i> Salin Jadwal Besok
                    </button>
                    <a href="{{ route('admin.statistics.index') }}"
                    class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-chart-bar me-2"></i> Lihat Statistik Lengkap
                    </a>

                </div>
            </div>
            <div class="card-body">
                {{-- Dua textarea tersembunyi --}}
                <textarea id="jadwalHariIniClipboard" class="d-none">{{ trim($jadwalHariIniText) }}</textarea>
                <textarea id="jadwalBesokClipboard" class="d-none">{{ trim($jadwalBesokText) }}</textarea>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    {{-- (Nav tabs tidak berubah) --}}
                    <li class="nav-item" role="presentation"><button class="nav-link active font-weight-bold" id="hari-ini-tab" data-bs-toggle="tab" data-bs-target="#hari-ini" type="button" role="tab" aria-controls="hari-ini" aria-selected="true">Hari Ini</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link font-weight-bold" id="besok-tab" data-bs-toggle="tab" data-bs-target="#besok" type="button" role="tab" aria-controls="besok" aria-selected="false">Besok</button></li>
                </ul>
                <div class="tab-content pt-3" id="myTabContent">
                    {{-- (Tab content tidak berubah) --}}
                    <div class="tab-pane fade show active" id="hari-ini" role="tabpanel" aria-labelledby="hari-ini-tab">@include('partials.kegiatanTable', ['kegiatans' => $kegiatanHariIni])</div>
                    <div class="tab-pane fade" id="besok" role="tabpanel" aria-labelledby="besok-tab">@include('partials.kegiatanTable', ['kegiatans' => $kegiatanBesok])</div>
                </div>
            </div>
        </div>
    @endcan

    {{-- TAMPILAN UNTUK PENGGUNA BIASA --}}
    @cannot('home_access')
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Informasi Peminjaman Ruang</h5></div>
            <div class="card-body">
                <h2>Cara Melakukan Peminjaman Ruang</h2>
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

<script>
    // === PERBAIKAN FINAL UNTUK FUNGSI COPY ===
    document.addEventListener('DOMContentLoaded', function() {

        // Fungsi fallback yang lebih tangguh
        function fallbackCopyTextToClipboard(text, button) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            
            // Hindari scrolling ke bawah halaman
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            let successful = false;
            try {
                successful = document.execCommand('copy');
            } catch (err) {
                console.error('Fallback: Gagal menyalin', err);
            }

            document.body.removeChild(textArea);

            // Beri feedback visual pada tombol
            if (successful) {
                const originalText = button.innerHTML;
                const originalClass = button.className;
                button.innerHTML = '<i class="fas fa-check me-2"></i> Disalin!';
                button.className = 'btn btn-sm btn-success';
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.className = originalClass;
                }, 2000);
            } else {
                alert('Gagal menyalin. Silakan coba salin secara manual.');
            }
        }

        // Fungsi utama yang akan dipanggil saat tombol diklik
        function copyHandler(buttonId, textareaId) {
            const button = document.getElementById(buttonId);
            if (!button) return;

            button.addEventListener('click', function() {
                const textToCopy = document.getElementById(textareaId).value;

                // Coba API modern terlebih dahulu (untuk HTTPS)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        // Feedback jika berhasil
                        const originalText = button.innerHTML;
                        const originalClass = button.className;
                        button.innerHTML = '<i class="fas fa-check me-2"></i> Disalin!';
                        button.className = 'btn btn-sm btn-success';
                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.className = originalClass;
                        }, 2000);
                    }).catch(err => {
                        console.error('Gagal menyalin dengan API modern:', err);
                        fallbackCopyTextToClipboard(textToCopy, button); // Coba fallback jika error
                    });
                } else {
                    // Gunakan fallback untuk HTTP atau browser lama
                    fallbackCopyTextToClipboard(textToCopy, button);
                }
            });
        }

        // Terapkan fungsi ke kedua tombol
        copyHandler('copyHariIniBtn', 'jadwalHariIniClipboard');
        copyHandler('copyBesokBtn', 'jadwalBesokClipboard');
    });
</script>
@endsection