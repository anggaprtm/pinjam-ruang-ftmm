@extends('layouts.landing')

@section('content')

{{-- 1. HERO SECTION & SHORTCUTS --}}
<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content container">
        
        {{-- JUDUL UTAMA --}}
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Layanan Sarpras FTMM</h1>
            <p class="lead mb-0" style="opacity: 0.9;">Portal Peminjaman Ruangan, Barang, & Logistik Terpadu</p>
        </div>

        {{-- SHORTCUT MENU (PINDAH KESINI) --}}
        <div class="row g-3 justify-content-center hero-shortcuts px-2">
            
            {{-- 1. Pinjam Ruang --}}
            <div class="col-6 col-md-4">
                <a href="#" onclick="document.querySelector('.search-container').scrollIntoView({behavior: 'smooth'}); return false;" class="service-card">
                    <div class="service-icon"><i class="fas fa-building"></i></div>
                    <h5 class="service-title">Pinjam Ruang</h5>
                    <p class="service-desc d-none d-md-block">Booking ruang rapat & kelas.</p>
                </a>
            </div>

            {{-- 2. Pinjam Barang --}}
            <div class="col-6 col-md-4">
                <a href="{{ route('login') }}" class="service-card">
                    <div class="service-icon"><i class="fas fa-box-open"></i></div>
                    <h5 class="service-title">Pinjam Barang</h5>
                    <p class="service-desc d-none d-md-block">Kabel, Proyektor, & Alat.</p>
                </a>
            </div>

            {{-- 3. Layanan Konsumsi --}}
            <div class="col-6 col-md-4">
                <a href="{{ route('login') }}" class="service-card">
                    <div class="service-icon"><i class="fas fa-utensils"></i></div>
                    <h5 class="service-title">Konsumsi</h5>
                    <p class="service-desc d-none d-md-block">Layanan pengadaan konsumsi.</p>
                </a>
            </div>

            {{-- 4. Inventarisasi Aset --}}
            <div class="col-6 col-md-4">
                <a href="http://inventaris.ftmm/" target="_blank" class="service-card">
                    <div class="service-icon"><i class="fas fa-box"></i></div>
                    <h5 class="service-title">Inventarisasi Aset</h5>
                    <p class="service-desc d-none d-md-block">Pengelolaan aset dan inventaris.</p>
                </a>
            </div>

            {{-- 5. SiMon --}}
            <div class="col-6 col-md-4">
                <a href="http://simon.ftmm/login" target="_blank" class="service-card">
                    <div class="service-icon"><i class="fas fa-boxes"></i></div>
                    <h5 class="service-title">SiMon FTMM</h5>
                    <p class="service-desc d-none d-md-block">Sistem informasi monitoring stock opname.</p>
                </a>
            </div>

            {{-- 6. Lost & Found --}}
            <div class="col-6 col-md-4">
                <a href="#" class="service-card">
                    <div class="service-icon"><i class="fas fa-search"></i></div>
                    <h5 class="service-title">Lost & Found <i style="font-size: 0.8rem;">(Coming Soon)</i></h5>
                    <p class="service-desc d-none d-md-block">Laporan barang hilang. <i style="font-size: 0.8rem;">(Coming Soon)</i></p>
                </a>
            </div>

        </div>

    </div>
</div>

{{-- 2. SEARCH FORM (FORM PENCARIAN) --}}
{{-- Note: margin-top dibuat overlap (-60px) biar nyambung sama hero --}}
<div class="container search-container" style="margin-top: -60px;">
    <div class="glass-card bg-white">
        {{-- Alert Error --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('landing') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-dark">Waktu Mulai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                        <input class="form-control border-start-0 ps-0" type="text" name="waktu_mulai" id="waktu_mulai"
                               value="{{ request('waktu_mulai') }}" placeholder="Waktu Mulai" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-dark">Waktu Selesai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt  text-primary"></i></span>
                        <input class="form-control border-start-0 ps-0" type="text" name="waktu_selesai" id="waktu_selesai"
                               value="{{ request('waktu_selesai') }}" placeholder="Waktu Selesai" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Kapasitas</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-users text-primary"></i></span>
                        <input class="form-control border-start-0 ps-0" type="number" name="kapasitas" 
                               value="{{ request('kapasitas') }}" min="1" placeholder="Orang" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-custom w-100 py-2" type="submit">
                        <i class="fas fa-search me-1"></i> Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 3. DAFTAR HASIL PENCARIAN --}}
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark border-start border-4 border-secondary ps-3">
            @if(request()->filled('waktu_mulai'))
                Tersedia: {{ $ruangan->count() }} Ruangan
            @else
                Daftar Ruangan
            @endif
        </h3>
        
        @if(request()->filled('waktu_mulai'))
            <a href="{{ route('landing') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fas fa-sync-alt me-1"></i> Reset Filter
            </a>
        @endif
    </div>

    <div class="row">
        @forelse ($ruangan as $item)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card room-card h-100">
                    <img src="{{ $item->foto ? asset('storage/' . $item->foto) : asset('assets/img/unsplash/ruangan_default.jpg') }}" 
                         class="card-img-top" alt="{{ $item->nama }}">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="fw-bold text-dark">{{ $item->nama }}</h5>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-map-marker-alt me-1 text-danger"></i> Lantai {{ $item->lantai }}
                        </p>
                        
                        <div class="mb-4">
                            <span class="badge bg-light text-dark border me-1">
                                <i class="fas fa-user-friends me-1"></i> Kapasitas {{ $item->kapasitas }}
                            </span>
                            @if(request()->filled('waktu_mulai'))
                                <span class="badge bg-success text-white">
                                    <i class="fas fa-check me-1"></i> Available
                                </span>
                            @endif
                        </div>

                        <div class="mt-auto">
                            @auth
                                <button type="button" 
                                        class="btn btn-outline-primary w-100 fw-bold rounded-pill"
                                        data-bs-toggle="modal"
                                        data-bs-target="#bookRuang"
                                        data-ruangan-id="{{ $item->id }}"
                                        data-ruangan-nama="{{ $item->nama }}"
                                        {{ !request()->filled('waktu_mulai') ? 'disabled' : '' }}>
                                    <i class="fas fa-calendar-plus me-1"></i> 
                                    {{ !request()->filled('waktu_mulai') ? 'Cek Ketersediaan Dulu' : 'Pinjam Sekarang' }}
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 fw-bold rounded-pill">
                                    <i class="fas fa-lock me-1"></i> Login untuk Meminjam
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="alert alert-warning">
                    <i class="fas fa-search me-2"></i>
                    Tidak ada ruangan ditemukan. Coba ganti jam atau kurangi kapasitas.
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- 4. SOP & DOWNLOAD AREA --}}
<div class="sop-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-5 mb-4 mb-md-0">
                <h3 class="fw-bold text-dark mb-3">Panduan & Prosedur</h3>
                <p class="text-muted">
                    Unduh dokumen Standar Operasional Prosedur (SOP) untuk memastikan kelancaran kegiatan Anda di lingkungan Fakultas Teknologi Maju dan Multidisiplin.
                </p>
            </div>
            <div class="col-md-7">
                <div class="row">
                    <div class="col-12">
                        <a href="#" class="sop-card">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">SOP Peminjaman Ruangan & Fasilitas</h6>
                                    <small class="text-muted">PDF Document</small>
                                </div>
                            </div>
                            <i class="fas fa-download text-primary"></i>
                        </a>
                    </div>
                    <div class="col-12">
                        <a href="#" class="sop-card">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Alur Permintaan Konsumsi & Logistik</h6>
                                    <small class="text-muted">PDF Document</small>
                                </div>
                            </div>
                            <i class="fas fa-download text-primary"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 5. MODAL BOOKING (AUTH ONLY) --}}
@auth
<div class="modal fade" tabindex="-1" role="dialog" id="bookRuang">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Pinjam: <span id="modal-ruangan-nama"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.bookRuang') }}" method="POST" id="bookingForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ruangan_id" id="modal_ruangan_id">
                    <input type="hidden" name="waktu_mulai" value="{{ request('waktu_mulai') }}">
                    <input type="hidden" name="waktu_selesai" value="{{ request('waktu_selesai') }}">

                    <div class="mb-3">
                        <label class="form-label fw-bold required">Nama Kegiatan</label>
                        <input class="form-control" type="text" name="nama_kegiatan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold required">Nama PIC</label>
                            <input type="text" name="nama_pic" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold required">No. HP PIC</label>
                            <input type="text" name="nomor_telepon" class="form-control" required>
                        </div>
                    </div>

                    @if(!auth()->user()->isAdmin())
                    <div class="mb-3">
                        <label class="form-label fw-bold required">Upload Surat Izin (PDF)</label>
                        <input type="file" name="surat_izin" class="form-control" accept=".pdf" required>
                        <small class="text-muted">Maks. 5MB</small>
                    </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="submitBooking">
                    <i class="fas fa-paper-plane me-1"></i> Ajukan
                </button>
            </div>
        </div>
    </div>
</div>
@endauth

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // --- CONFIG DATEPICKER (TANPA DETIK) ---
        const pickerConfig = {
            format: 'YYYY-MM-DD HH:mm', // Penting: Match format Controller
            locale: 'en',
            sideBySide: true,
            ignoreReadonly: true,
            icons: {
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                time: 'fas fa-clock',
                date: 'fas fa-calendar'
            }
        };

        // 1. Init Waktu Mulai
        $('#waktu_mulai').datetimepicker(pickerConfig);

        // 2. Init Waktu Selesai (useCurrent false agar tidak auto-fill)
        $('#waktu_selesai').datetimepicker($.extend({}, pickerConfig, {
            useCurrent: false 
        }));

        // --- LOGIC SALING KUNCI (START & END DATE) ---

        // Saat Waktu Mulai Berubah
        $("#waktu_mulai").on("dp.change", function (e) {
            // Set batas minimal Waktu Selesai
            $('#waktu_selesai').data("DateTimePicker").minDate(e.date);
            
            // Auto-Clear jika Waktu Selesai jadi invalid (lebih lampau dari Start)
            var currentEnd = $('#waktu_selesai').data("DateTimePicker").date();
            if (currentEnd && e.date && currentEnd.isBefore(e.date)) {
                 $('#waktu_selesai').data("DateTimePicker").clear();
            }
        });

        // Saat Waktu Selesai Berubah
        $("#waktu_selesai").on("dp.change", function (e) {
            // Set batas maksimal Waktu Mulai
            $('#waktu_mulai').data("DateTimePicker").maxDate(e.date);
        });

        // PENGAMAN SUBMIT: Cek manual sebelum form dikirim
        $('form').on('submit', function(e) {
            // Skip validasi kalau ini form booking modal (bukan search)
            if(this.id === 'bookingForm') return;

            var start = $('#waktu_mulai').data("DateTimePicker").date();
            var end = $('#waktu_selesai').data("DateTimePicker").date();

            if (start && end && end.isBefore(start)) {
                e.preventDefault(); 
                alert('Waktu Selesai tidak boleh mundur dari Waktu Mulai!');
                return false;
            }
        });

        // --- LOGIC MODAL BOOKING ---
        const bookRuangModal = document.getElementById('bookRuang');
        if (bookRuangModal) {
            bookRuangModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const ruanganId = button.getAttribute('data-ruangan-id');
                const ruanganNama = button.getAttribute('data-ruangan-nama');
                
                // Isi Nama Ruangan di Judul Modal
                document.getElementById('modal-ruangan-nama').textContent = ruanganNama;
                // Isi ID Ruangan di Input Hidden
                document.getElementById('modal_ruangan_id').value = ruanganId;
            });

            // Submit form via tombol "Ajukan"
            document.getElementById('submitBooking').addEventListener('click', function () {
                document.getElementById('bookingForm').submit();
            });
        }
    });
</script>
@endsection