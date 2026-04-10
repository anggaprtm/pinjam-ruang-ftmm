@extends('layouts.admin')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="font-weight-bold text-nowrap"><i class="fas fa-calendar-check me-2"></i> Kalender Kegiatan</h3>
</div>

<div class="calendar-container">
    {{-- Kolom Utama untuk Kalender --}}
    <div class="calendar-main">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                {{-- Form Filter dari kode lama --}}
                <form method="GET" action="{{ route('admin.systemCalendar') }}">
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label for="ruangan_id" class="form-label fw-bold">Filter Ruangan:</label>
                            <select class="form-control select2" name="ruangan_id" id="ruangan_id" onchange="this.form.submit()">
                                @foreach($ruangan as $id => $ruangan_nama)
                                    <option value="{{ $id }}" {{ request()->input('ruangan_id') == $id ? 'selected' : '' }}>{{ $ruangan_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="user_id" class="form-label fw-bold">Filter Peminjam:</label>
                            <select class="form-control select2" name="user_id" id="user_id" onchange="this.form.submit()">
                                @foreach($users as $id => $user)
                                    <option value="{{ $id }}" {{ request()->input('user_id') == $id ? 'selected' : '' }}>{{ $user }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_kuliah" class="form-label fw-bold">Tipe Kegiatan:</label>
                            <select class="form-control select2" name="filter_kuliah" id="filter_kuliah" onchange="this.form.submit()">
                                <option value="semua" {{ request('filter_kuliah', 'semua') == 'semua' ? 'selected' : '' }}>Semua Kegiatan</option>
                                <option value="non-kuliah" {{ request('filter_kuliah', 'non-kuliah') == 'non-kuliah' ? 'selected' : '' }}>Non-Perkuliahan</option>
                                <option value="kuliah" {{ request('filter_kuliah') == 'kuliah' ? 'selected' : '' }}>Perkuliahan</option>
                            </select>
                        </div>
                    </div>
                </form>
                
                <div id="calendar"></div>

                {{-- Legenda Warna Dinamis yang Dirapikan --}}
                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-3">Legenda Warna Peminjam:</h6>
                    <div class="calendar-legend flex-wrap">
                        @foreach($userColors as $userName => $color)
                            <div class="legend-item">
                                <span class="legend-color-box" style="background-color: {{ $color }};"></span> {{ $userName }}
                            </div>
                        @endforeach
                        <div class="legend-item"><span class="legend-color-box" style="background-color: #17a2b8;"></span> Perkuliahan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar untuk Detail Event --}}
    <div class="calendar-sidebar">
        <div class="card event-details-card" id="event-details">
            <div class="card-header"><h5 class="mb-0">Detail Acara</h5></div>
            <div class="card-body">
                <div class="event-details-placeholder"><i class="fas fa-mouse-pointer"></i><p>Pilih acara untuk melihat detail.</p></div>
                <div id="event-details-content" class="d-none"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailModalLabel">Detail Acara</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
            <div id="event-details">
                {{-- Detail akan diisi oleh JavaScript --}}
            </div>
      </div>
      <div class="modal-footer">
        {{-- Tombol akan ditambahkan di sini oleh JavaScript --}}
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
@parent
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventDetailsContent = document.getElementById('event-details-content');
    const eventDetailsPlaceholder = document.querySelector('.event-details-placeholder');
    const modalTitle = document.getElementById('eventDetailModalLabel');
    const modalBody = document.getElementById('modalEventBody');
    const eventModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
    const isMobile = window.innerWidth < 768;
    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isMobile ? '' : 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        initialView: isMobile ? 'listMonth' : 'dayGridMonth',
        
        // --- 1. Ganti height menjadi auto agar gap scrollbar kanan hilang ---
        contentHeight: 'auto', 
        height: 'auto',

        locale: 'id',
        
        // --- 2. Tambahkan ini agar nama hari tampil penuh (Senin, Selasa, dll) ---
        dayHeaderFormat: { weekday: 'long' },

        // Menerjemahkan tombol
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
            list: 'Daftar'
        },
        views: {
            timeGridWeek: { buttonText: 'Minggu' },
            timeGridDay: { buttonText: 'Hari' },
            listMonth: { buttonText: 'Daftar' }
        },
        events: {!! json_encode($events) !!},
        editable: false,
        
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const props = info.event.extendedProps;
            const start = info.event.start;
            const end = info.event.end;
            
            // === TAMBAHAN 1: Ambil ID event ===
            const eventId = info.event.id;

            const timeFormat = { hour: '2-digit', minute: '2-digit', hour12: false };
            const dateFormat = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

            let timeString = `${start.toLocaleTimeString([], timeFormat)} - ${end ? end.toLocaleTimeString([], timeFormat) : ''}`;

            // Konten detail tetap sama
            let contentHtml = ``;
            contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-calendar-alt"></i></div><div class="content"><div class="label">Tanggal</div><div class="value">${start.toLocaleDateString('id-ID', dateFormat)}</div></div></div>`;
            contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-clock"></i></div><div class="content"><div class="label">Waktu</div><div class="value">${timeString}</div></div></div>`;
            if (props.ruangan_nama) {
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-door-open"></i></div><div class="content"><div class="label">Ruangan</div><div class="value">${props.ruangan_nama}</div></div></div>`;
            }
            if (props.user_name) {
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-user-circle"></i></div><div class="content"><div class="label">${props.type === 'perkuliahan' ? 'Dosen/Prodi' : 'Peminjam'}</div><div class="value">${props.user_name}</div></div></div>`;
            }
            if (props.nama_pic) {
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-user-tag"></i></div><div class="content"><div class="label">Nama PIC</div><div class="value">${props.nama_pic}</div></div></div>`;
            }
            if (props.nomor_telepon) {
                // Buat link WhatsApp dari nomor yang diinput pengguna.
                // Langkah: ambil hanya digit, ubah leading 0 -> 62 (kode negara Indonesia),
                // jika user memasukkan angka tanpa 0 (mis. 8123...), juga tambahkan 62.
                const phoneRaw = String(props.nomor_telepon || '');
                const digits = phoneRaw.replace(/\D/g, '');
                let waNumber = digits;
                if (digits.startsWith('0')) {
                    waNumber = '62' + digits.slice(1);
                } else if (digits.startsWith('+')) {
                    waNumber = digits.replace(/^\+/, '');
                } else if (digits.startsWith('8')) {
                    // user mungkin mengetik tanpa leading 0
                    waNumber = '62' + digits;
                }

                const waLink = `https://wa.me/${waNumber}`;
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-phone"></i></div><div class="content"><div class="label">No. Telepon PIC</div><div class="value"><a href="${waLink}" data-wa-number="${waNumber}" class="text-decoration-none js-wa-link"><i class="fab fa-whatsapp me-1 text-success"></i>${phoneRaw}</a></div></div></div>`;
            }
            if (props.deskripsi) {
                contentHtml += `<div class="detail-item"><div class="icon"><i class="fas fa-info-circle"></i></div><div class="content"><div class="label">Deskripsi</div><div class="value">${props.deskripsi}</div></div></div>`;
            }

            // === TAMBAHAN 2: Buat HTML untuk tombol HANYA jika bukan perkuliahan ===
            let buttonsHtml = '';
            if (props.type !== 'perkuliahan' && eventId) {
                // Buat URL dengan mengganti placeholder :id
                const showUrl = '{{ route("admin.kegiatan.show", ":id") }}'.replace(':id', eventId);
                const editUrl = '{{ route("admin.kegiatan.edit", ":id") }}'.replace(':id', eventId);
                
                buttonsHtml = `
                    <div class="mt-3 pt-3 border-top">
                        <a href="${showUrl}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i> Lihat Detail
                        </a>
                        <a href="${editUrl}" class="btn btn-success btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    </div>
                `;
            }
            
            // === TAMBAHAN 3: Tampilkan detail DAN tombol sesuai platform (mobile/desktop) ===
            if (isMobile) {
                modalTitle.textContent = info.event.title;
                // Gabungkan konten detail dengan tombol
                modalBody.innerHTML = contentHtml + buttonsHtml;
                eventModal.show();
            } else {
                let desktopContent = `<h5 class="detail-title">${info.event.title}</h5><hr>` + contentHtml + buttonsHtml;
                eventDetailsContent.innerHTML = desktopContent;
                eventDetailsContent.classList.remove('d-none');
                eventDetailsPlaceholder.classList.add('d-none');
            }
        }
    });

    calendar.render();
    setTimeout(function() {
        calendar.updateSize();
    }, 250);

    // Global handler: open Bootstrap modal to confirm opening WhatsApp links
    document.addEventListener('click', function(e) {
        const el = e.target.closest && e.target.closest('.js-wa-link');
        if (!el) return;
        e.preventDefault();
        const waNumber = el.getAttribute('data-wa-number') || '';
        const display = el.textContent.trim();
        const pretty = waNumber ? ('+' + waNumber) : display;

        // Ensure modal exists
        let modalEl = document.getElementById('waConfirmModal');
        if (!modalEl) {
            // Create modal markup dynamically (fallback)
            modalEl = document.createElement('div');
            modalEl.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Konfirmasi WhatsApp</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">Anda akan membuka WhatsApp ke nomor: <strong class="wa-number-display"></strong></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success wa-confirm-open">Buka WhatsApp</button>
                        </div>
                    </div>
                </div>`;
            modalEl.id = 'waConfirmModal';
            modalEl.className = 'modal fade';
            document.body.appendChild(modalEl);
        }

        const displayEl = modalEl.querySelector('.wa-number-display');
        const confirmBtn = modalEl.querySelector('.wa-confirm-open');
        displayEl.textContent = pretty;
        confirmBtn.setAttribute('data-href', el.href);
        // Store instance so we can hide later
        if (!modalEl.__bsModal) modalEl.__bsModal = new bootstrap.Modal(modalEl);
        modalEl.__bsModal.show();

        // Ensure single handler for confirm button
        if (!confirmBtn.__handled) {
            confirmBtn.addEventListener('click', function() {
                const href = this.getAttribute('data-href');
                if (href) {
                    window.open(href, '_blank', 'noopener');
                }
                if (modalEl.__bsModal) modalEl.__bsModal.hide();
            });
            confirmBtn.__handled = true;
        }
    });
});
</script>

<!-- Modal markup: WA confirmation (for browsers that prefer static markup) -->
<div class="modal fade" id="waConfirmModal" tabindex="-1" aria-labelledby="waConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="waConfirmModalLabel">Konfirmasi WhatsApp</h5>
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

<style>
.modal-footer .dynamic-buttons {
    margin-right: auto;
}
/* ==========================================================================
   LAYOUT UTAMA (KALENDER & SIDEBAR)
   ========================================================================== */
.calendar-container {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}
.calendar-main {
    flex: 1; 
    min-width: 0; /* Mencegah overflow flexbox */
}
.calendar-sidebar {
    width: 350px;
    flex-shrink: 0;
    position: sticky;
    top: 80px; /* Menempel saat di-scroll */
}
@media (max-width: 991.98px) {
    .calendar-container { flex-direction: column; }
    .calendar-sidebar { width: 100%; position: static; }
}

/* ==========================================================================
   FULLCALENDAR V6 CUSTOM THEME (TEMA MODERN FTMM)
   ========================================================================== */
:root {
    --fc-border-color: #eaecf0;
    --fc-today-bg-color: rgba(32, 201, 151, 0.05); /* Highlight hari ini (soft tosca) */
    
    /* Warna Dasar Tombol Standar */
    --fc-button-bg-color: #ffffff;
    --fc-button-border-color: #dee2e6;
    --fc-button-text-color: #495057;
    
    /* Efek Hover Tombol */
    --fc-button-hover-bg-color: #f8f9fa;
    --fc-button-hover-border-color: #dee2e6;
}

/* 1. Area Header (Judul Bulan & Tombol) */
.fc-toolbar-title {
    font-weight: 800 !important;
    font-size: 1.4rem !important;
    color: #2b2b2b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.fc-button {
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    font-weight: 600 !important;
    text-transform: capitalize;
    border-radius: 6px !important;
    padding: 0.4rem 0.9rem !important;
    transition: all 0.2s ease;
}
.fc-button-primary:not(:disabled):active,
.fc-button-primary:not(:disabled).fc-button-active {
    background-color: #741847 !important; /* Warna Maroon/Ungu Aktif */
    color: #fff !important;
    border-color: #741847 !important;
    box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125) !important;
}

/* 2. Area Grid & Nama Hari */
.fc-theme-standard th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
.fc-col-header-cell-cushion {
    color: #495057;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 12px 0 !important;
    text-decoration: none !important;
}
.fc-daygrid-day-number {
    font-weight: 600;
    color: #6c757d;
    padding: 8px !important;
    text-decoration: none !important;
}
.fc-day-today .fc-daygrid-day-number {
    color: #20c997; /* Tanggal hari ini diberi warna tosca/hijau */
    font-weight: 800;
}

/* 3. Area Event (Acara) */
.fc-event {
    border-radius: 4px !important;
    border: none !important;
    padding: 3px 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s;
}
.fc-event:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    filter: brightness(1.1);
    z-index: 5;
}
.fc-h-event .fc-event-title {
    font-weight: 600;
}

/* ==========================================================================
   STYLING LAINNYA (LEGEND & DETAIL ITEM)
   ========================================================================== */
.calendar-legend {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: #495057;
    font-weight: 600;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.legend-color-box {
    width: 14px;
    height: 14px;
    border-radius: 3px;
    display: inline-block;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.detail-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 12px;
}
.detail-item .icon {
    font-size: 1.1rem;
    margin-right: 15px;
    width: 25px;
    text-align: center;
    color: #741847; /* Icon maroon */
    margin-top: 2px;
}
.detail-item .label {
    font-weight: bold;
    color: #6c757d;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.detail-item .value {
    color: #212529;
    font-weight: 500;
}
.detail-title {
    color: #2b2b2b;
    font-weight: 800;
    line-height: 1.4;
}
.event-details-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    height: 200px;
}
.event-details-placeholder i {
    font-size: 3rem;
    margin-bottom: 10px;
    opacity: 0.5;
}
/* ==========================================================================
   PERBAIKAN WARNA TEKS EVENT (MENGHILANGKAN BIRU BOOTSTRAP)
   ========================================================================== */
.fc-event, 
.fc-daygrid-event, 
.fc-timegrid-event,
.fc-list-event-title a {
    color: #2b2b2b !important; /* Warna abu-abu gelap/hitam elegan */
    text-decoration: none !important; /* Hilangkan garis bawah link jika ada */
}

/* Memastikan hover event tetap memiliki warna teks yang benar */
.fc-event:hover, 
.fc-daygrid-event:hover {
    color: #000000 !important;
}

/* Memastikan dot warna event tetap sejajar dan proporsional */
.fc-daygrid-event-dot {
    margin-right: 6px;
    border-width: 4px !important; 
}
</style>
@endsection
