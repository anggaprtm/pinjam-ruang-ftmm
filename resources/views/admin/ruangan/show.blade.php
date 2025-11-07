@extends('layouts.admin')
@section('content')

<div class="card detail-view-card">
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="detail-title">{{ $ruangan->nama }}</h2>
                <p class="detail-sub-title mb-0">Detail dan Jadwal Penggunaan Ruangan</p>
            </div>
            <a href="{{ route('admin.ruangan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row">
            {{-- Kolom Kiri: Detail Info Ruangan --}}
            <div class="col-lg-5">
                <h5 class="mb-3 font-weight-bold">Informasi Ruangan</h5>
                {{-- === TAMBAHAN: Menampilkan Foto Ruangan === --}}
                @if($ruangan->foto) 
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $ruangan->foto) }}" alt="{{ $ruangan->nama }}" class="img-fluid rounded shadow-sm">
                    </div>
                @else
                    <div class="mb-3">
                        <img src="{{ asset('assets/img/unsplash/ruangan_default.jpg') }}" alt="Default Image" class="img-fluid rounded shadow-sm">
                    </div>
                @endif
                {{-- === AKHIR TAMBAHAN === --}}
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.ruangan.fields.kapasitas') }}</div>
                        <div class="value">{{ $ruangan->kapasitas }} Orang</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-info-circle"></i></div>
                    <div class="content">
                        <div class="label">Status</div>
                        <div class="value">
                            @if($ruangan->is_active)
                                <span class="badge-status badge-status-aktif">Aktif</span>
                            @else
                                <span class="badge-status badge-status-tidak-aktif">Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                    <div class="content">
                        <div class="label">{{ trans('cruds.ruangan.fields.deskripsi') }}</div>
                        <div class="value">{{ $ruangan->deskripsi ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Kalender Mini --}}
            <div class="col-lg-7">
                <h5 class="mb-3 font-weight-bold">Jadwal Ruangan</h5>
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: daftar kegiatan per tanggal -->
<div class="modal fade" id="dayEventsModal" tabindex="-1" aria-labelledby="dayEventsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dayEventsModalLabel">Daftar Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul id="dayEventsList" class="list-unstyled mb-0"></ul>
            </div>
            <div class="modal-footer">
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
    // Ambil events yang dikirim dari server
    const serverEvents = {!! json_encode($events) !!} || [];

    // Kita akan membuat event latar (background) untuk menandai hari-hari ketika ruangan terpakai.
    // FullCalendar mendukung event dengan property "display: 'background'" atau "rendering: 'background'" tergantung versi.
    // Karena kita menggunakan FullCalendar v6 (index.global), gunakan 'display: "background"'.

    // Fungsi bantu: mengembalikan array tanggal (YYYY-MM-DD) antara dua tanggal inklusif
    function datesBetween(start, end) {
        const dates = [];
        const cur = new Date(start);
        const last = new Date(end);
        // Normalisasi waktu ke tengah malam UTC local to avoid timezone shift issues
        cur.setHours(0,0,0,0);
        last.setHours(0,0,0,0);
        while (cur <= last) {
            dates.push(cur.toISOString().slice(0,10));
            cur.setDate(cur.getDate() + 1);
        }
        return dates;
    }

    // Untuk setiap event server, kita buat event latar (background) per tanggal yang dipakai,
    // dan gunakan warna yang sama namun lebih transparan agar judul event tetap terbaca.
    const backgroundEvents = [];

    // Membantu konversi hex color (#rrggbb) ke rgba dengan alpha
    function hexToRgba(hex, alpha) {
        if (!hex) return `rgba(0,0,0,${alpha})`;
        // dukung format #rrggbb atau #rgb
        const h = hex.replace('#', '');
        let r, g, b;
        if (h.length === 3) {
            r = parseInt(h[0] + h[0], 16);
            g = parseInt(h[1] + h[1], 16);
            b = parseInt(h[2] + h[2], 16);
        } else if (h.length === 6) {
            r = parseInt(h.substring(0,2), 16);
            g = parseInt(h.substring(2,4), 16);
            b = parseInt(h.substring(4,6), 16);
        } else {
            return `rgba(0,0,0,${alpha})`;
        }
        return `rgba(${r},${g},${b},${alpha})`;
    }

    // Mendukung hex atau rgb/rgba CSS string, mengembalikan warna dengan alpha
    function cssColorWithAlpha(color, alpha) {
        if (!color) return `rgba(0,0,0,${alpha})`;
        color = color.trim();
        // jika sudah rgba, replace alpha
        if (color.startsWith('rgba')) {
            return color.replace(/rgba\(([^)]+)\)/, function(_, inner) {
                const parts = inner.split(',').map(p => p.trim());
                parts[3] = alpha.toString();
                return 'rgba(' + parts.join(',') + ')';
            });
        }
        // jika rgb(...) -> ubah ke rgba
        if (color.startsWith('rgb(')) {
            return color.replace('rgb(', 'rgba(').replace(')', ', ' + alpha + ')');
        }
        // jika hex -> gunakan hexToRgba
        if (color.startsWith('#')) {
            return hexToRgba(color, alpha);
        }
        // fallback: return rgba(0,0,0,alpha)
        return `rgba(0,0,0,${alpha})`;
    }

    serverEvents.forEach(ev => {
        // noop here — we will rely on FullCalendar's parsed Event objects after render
    });

    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        initialView: 'dayGridMonth',
        locale: 'id',
        buttonText: {
            month: 'Bulan',
            week: 'Minggu',
        },
    events: serverEvents,
        editable: false,
        // Membuat event tidak bisa diklik karena ini hanya untuk display
        eventClick: function(info) {
            info.jsEvent.preventDefault(); 
        },
        // Tampilkan maksimal 4 baris event di month view, sisanya jadi link '+n lainnya'
        dayMaxEventRows: 4,
    });

    calendar.render();

    // remove debug exposure

    // Setelah calendar dirender, ambil EventApi yang sudah ter-parse (menghindari masalah timezone/parsing)
    // dan beri anotasi pada sel hari di month view: shading per event (menggunakan warna event) dan
    // sisipkan daftar judul kegiatan jika ada lebih dari satu pada hari tersebut.
    function annotateDayCells() {
        // clear previous injected summaries/lists to avoid duplicates
        document.querySelectorAll('.fc-day-summary').forEach(n => n.remove());
        document.querySelectorAll('.fc-day-event-list').forEach(n => n.remove());

        const events = calendar.getEvents();
        const map = {}; // tanggal 'YYYY-MM-DD' => array of {title, color}

        events.forEach(e => {
            if (!e.start) return;
            // gunakan end-1ms untuk meng-handle exclusive end
            const startDt = new Date(e.start);
            const endDt = e.end ? new Date(e.end.getTime() - 1) : new Date(e.start);

            // Buat tanggal lokal (midnight) untuk start dan end
            const localStart = new Date(startDt.getFullYear(), startDt.getMonth(), startDt.getDate());
            const localEnd = new Date(endDt.getFullYear(), endDt.getMonth(), endDt.getDate());

            for (let d = new Date(localStart); d <= localEnd; d.setDate(d.getDate() + 1)) {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                const key = `${y}-${m}-${day}`;
                if (!map[key]) map[key] = [];
                // normalize title: ignore null / 'null' strings
                let title = e.title;
                if (title === null || title === undefined) title = null;
                if (typeof title === 'string' && title.trim().toLowerCase() === 'null') title = null;
                map[key].push({ title: title, color: e.backgroundColor || e.color || '#17a2b8' });
            }
        });

        // Untuk setiap tanggal yang terpakai, cari elemen day cell dan modifikasi
        Object.keys(map).forEach(date => {
            const dayEl = document.querySelector(`.fc-daygrid-day[data-date="${date}"]`);
            if (!dayEl) return; // mis: diluar view

            // Tambahkan shading latar dengan warna campuran (jika banyak acara, pakai warna pertama)
            const firstColor = map[date][0].color;
            const shade = cssColorWithAlpha(firstColor, 0.15);
            // beri style latar pada .fc-daygrid-day-frame agar tidak memengaruhi header
            const frame = dayEl.querySelector('.fc-daygrid-day-frame');
            if (frame) {
                frame.style.backgroundColor = shade;
                frame.style.borderRadius = '6px';
            }

            // Jika FullCalendar sudah merender event elements di hari ini, jangan inject daftar teks lagi
            const existingEvents = dayEl.querySelectorAll('.fc-daygrid-event, .fc-event');
                if (existingEvents && existingEvents.length > 0) {
                    // FullCalendar sudah merender event blocks; kita tidak inject overlay.
                    // We'll let FullCalendar show up to 4 events and provide its built-in '+n more' link.
                } else {
                // Sisipkan daftar nama kegiatan (jika belum ada)
                let list = dayEl.querySelector('.fc-day-event-list');
                if (!list) {
                    list = document.createElement('ul');
                    list.className = 'fc-day-event-list';
                    list.style.listStyle = 'none';
                    list.style.padding = '0 6px 6px 6px';
                    list.style.margin = '0';
                    list.style.fontSize = '0.75rem';
                    list.style.lineHeight = '1.1';
                    // append ke frame supaya layout konsisten
                    if (frame) frame.appendChild(list);
                    else dayEl.appendChild(list);
                } else {
                    list.innerHTML = '';
                }

                // tambahkan semua judul (abaikan yang null/empty)
                map[date].forEach(item => {
                    if (!item.title) return;
                    const li = document.createElement('li');
                    li.textContent = item.title;
                    li.style.whiteSpace = 'nowrap';
                    li.style.overflow = 'hidden';
                    li.style.textOverflow = 'ellipsis';
                    li.style.marginBottom = '2px';
                    li.title = item.title;
                    list.appendChild(li);
                });
                // jika lebih dari 2, tambahkan link more
                const titles = map[date].map(i => i.title).filter(t => !!t);
                if (titles.length > 2) {
                    const moreLi = document.createElement('li');
                    const moreLink = document.createElement('a');
                    moreLink.href = '#';
                    moreLink.textContent = `Lihat ${titles.length} kegiatan`;
                    moreLink.addEventListener('click', function(ev) {
                        ev.preventDefault();
                        openDayModal(date, titles);
                    });
                    moreLi.appendChild(moreLink);
                    list.appendChild(moreLi);
                }
            }
        });

        // store map globally so more-link handlers can access titles
        window.__fc_dayMap = map;

        // attach click handlers to FullCalendar's built-in more links to open modal instead of native popover
        document.querySelectorAll('.fc-daygrid-more-link').forEach(link => {
            // remove previous handler if any by cloning
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            newLink.addEventListener('click', function(ev) {
                ev.preventDefault();
                const dayEl = newLink.closest('.fc-daygrid-day');
                if (!dayEl) return;
                const date = dayEl.getAttribute('data-date');
                const titles = (window.__fc_dayMap && window.__fc_dayMap[date]) ? window.__fc_dayMap[date].map(i => i.title).filter(t=>!!t) : [];
                if (titles.length > 0) openDayModal(date, titles);
            });
        });

    }

    // Membuka modal dan mengisi daftar kegiatan
    function openDayModal(date, titles) {
        const modalLabel = document.getElementById('dayEventsModalLabel');
        modalLabel.textContent = 'Kegiatan pada ' + date;
        const list = document.getElementById('dayEventsList');
        list.innerHTML = '';
        titles.forEach(t => {
            const li = document.createElement('li');
            li.textContent = t;
            li.style.padding = '6px 0';
            list.appendChild(li);
        });

        // show bootstrap modal (Bootstrap 5) if available, otherwise fallback to alert
        const modalEl = document.getElementById('dayEventsModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } else {
            alert('Kegiatan pada ' + date + '\n\n' + titles.join('\n'));
        }
    }
    // Jalankan anotasi setelah render pertama, dan juga setiap kali view berubah (mis. navigasi bulan)
    // run annotate slightly after render so DOM event elements exist
    setTimeout(annotateDayCells, 50);
    calendar.on('datesSet', function() {
        // hapus anotasi lama
        document.querySelectorAll('.fc-day-event-list').forEach(n => n.remove());
        document.querySelectorAll('.fc-daygrid-day-frame').forEach(f => { f.style.backgroundColor = ''; });
        setTimeout(annotateDayCells, 50);
    });
});
</script>
@endsection
