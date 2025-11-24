@extends('layouts.admin')

@section('styles')
@parent
<style>
  html,body,#kiosk-root { height: 100%; }
  /* Bright card-based theme similar to home.blade */
  .kiosk-fullscreen { background: #f8fafc; color: #0f172a; height: 100vh; overflow: hidden; }
  .kiosk-header { display:flex; align-items:center; justify-content:space-between; padding:28px; }
  .kiosk-clock { font-size:72px; font-weight:800; letter-spacing:1px; color:#0b5ed7 }
  .kiosk-sub { font-size:22px; color:#6b7280; }
  .kiosk-body { display:flex; height: calc(100vh - 200px); }
  .kiosk-left { width: 42%; padding-left:15px; padding-top:1px; padding-right:15px; overflow:auto }
  .kiosk-right { flex:1; padding-top:1px; padding-left:5px; padding-right:15px; overflow:hidden }
  .card-kiosk { background: #fff; padding:20px; border-radius:12px; box-shadow: 0 8px 30px rgba(15,23,42,0.06); }
  .kiosk-event { padding:16px 18px; margin-bottom:12px; background: #f1f5f9; border-radius:10px }
  .kiosk-event .title { font-size:20px; font-weight:800; color:#0f172a }
  .kiosk-event .meta { color:#6b7280; font-size:15px }
  .kiosk-controls { position:fixed; right:40px; top:20px; z-index:2000; display:flex; gap:8px }
  .kiosk-qr-exit { background: #fff; color:#0f172a; border-radius:8px; padding:10px 14px; border:1px solid rgba(15,23,42,0.06); font-weight:600 }
  .kiosk-ctrl-icon { background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(6px);  border-radius:8px; width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; font-size:18px; border:1px solid rgba(15,23,42,0.06); }
  .kiosk-ctrl-icon.active { background:#7a1f55; color:#fff; border-color: rgba(15,23,42,0.12); }
  .kiosk-ctrl-icon.active i { color: #fff; }
  .time-banner { padding:15px 18px; background: linear-gradient(90deg,#8b2d67,#7a1f55); color:#fff; margin-bottom:8px; }
  .kiosk-list { height: calc(100% - 40px); overflow:auto }
  .fc { height:100% }

    /* Kalender mengambil sisa ruang dalam card (penting) */
   #kioskCalendar {
    flex: 1;          /* mengambil sisa ruang */
    min-height: 280px;/* batas minimal agar tidak terlalu kecil */
    border-radius: 6px;
    overflow: auto;   /* scroll internal jika event banyak */
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.04);
    }
  /* Large screen tuning for 2K/4K */
  @media (min-width: 1920px) {
    .kiosk-clock { font-size:96px; }
    .kiosk-sub { font-size:26px; }
    .kiosk-event .title { font-size:24px; }
    .kiosk-event .meta { font-size:18px; }
  }
  /* responsive tweaks */
  @media (max-width: 768px) {
    .kiosk-body { flex-direction: column; }
    .kiosk-left { width: 100%; border-right: none; }
    .kiosk-right { width: 100%; }
  }
</style>
@endsection

@section('content')
<div id="kiosk-root" class="kiosk-fullscreen">
  {{-- Welcome banner (use same structure as home.blade) --}}
  <div class="time-banner text-center">
    <div id="current-time" class="fs-2 fw-bold mt-1"></div>
  </div>

  <div class="container-fluid px-3 mt-2">
    <div class="row">
      <div class="col-lg-3 col-md-6 mb-4 pt-2">
        <div class="stat-card card-kiosk">
          <div class="icon-container icon-ruangan"><i class="fas fa-door-open"></i></div>
          <div class="info"><div class="stat-number">{{ $ruanganCount ?? 0 }}</div><div class="stat-label">Total Ruangan</div></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 pt-2">
        <div class="stat-card card-kiosk">
          <div class="icon-container icon-menunggu"><i class="fas fa-clock"></i></div>
          <div class="info"><div class="stat-number">{{ $kegiatanMenungguCount ?? 0 }}</div><div class="stat-label">Kegiatan Menunggu</div></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 pt-2">
        <div class="stat-card card-kiosk">
          <div class="icon-container icon-disetujui"><i class="fas fa-check-circle"></i></div>
          <div class="info"><div class="stat-number">{{ $kegiatanDisetujuiCount ?? 0 }}</div><div class="stat-label">Kegiatan Disetujui</div></div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 pt-2">
        <div class="stat-card card-kiosk">
          <div class="icon-container icon-total"><i class="fas fa-list-alt"></i></div>
          <div class="info"><div class="stat-number">{{ $kegiatanTotalCount ?? 0 }}</div><div class="stat-label">Total Kegiatan</div></div>
        </div>
      </div>
    </div>

    
  </div>

  <div class="kiosk-body">
    <div class="kiosk-left">
      <div class="card-kiosk">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="mb-0" style="font-weight:600;">Daftar Kegiatan</h4>
          <button id="perkuliahanToggle" class="kiosk-ctrl-icon" aria-pressed="false" title="Perkuliahan"><i class="fas fa-university"></i></button>
        </div>
        <div id="kioskEventList" class="kiosk-list">
          @forelse($todayEvents as $ev)
            @php $type = isset($ev['type']) ? strtolower(trim($ev['type'])) : '' ; @endphp
            <div class="kiosk-event type-{{ $type }}" data-type="{{ $type }}" data-start="{{ $ev['start'] }}" data-end="{{ $ev['end'] }}">
              <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <div style="flex:1">
                  <div class="title" style="font-size:18px; color:#7a1f55;">{{ $ev['title'] }}</div>
                  <div class="meta" style="margin-top:6px;">
                    <span class="me-3"><i class="fas fa-door-open me-1"></i> {{ $ev['ruangan'] ?? '' }}</span>
                    <span><i class="fas fa-user me-1"></i> {{ $ev['peminjam'] ?? ($ev['user_name'] ?? '-') }}</span>
                  </div>
                </div>
                <div style="text-align:right; min-width:120px">
                  <div style="font-size:18px; font-weight:700;">{{ \Carbon\Carbon::parse($ev['start'])->format('H:i') }}{{ $ev['end'] ? ' — ' . \Carbon\Carbon::parse($ev['end'])->format('H:i') : '' }}</div>
                </div>
              </div>
            </div>
          @empty
            <div class="text-muted">Tidak ada kegiatan untuk hari ini.</div>
          @endforelse
        </div>
      </div>
    </div>
    <div class="kiosk-right">
      <div class="card-kiosk" style="height:100%;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <h5 class="mb-0" id="calendarTitle">&nbsp;</h5>
          </div>
          <div class="d-flex align-items-center gap-2">
            <div class="btn-group" role="group" aria-label="Calendar view" id="calendarViewBtns">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-view="dayGridMonth">Bulanan</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-view="timeGridWeek">Mingguan</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-view="listWeek">Daftar</button>
            </div>
            <button id="kioskHelpBtn" class="kiosk-ctrl-icon" title="Panduan Keyboard"><i class="fas fa-question"></i></button>
          </div>
        </div>
        <div id="kioskCalendar" style="height:calc(100% - 36px); border-radius:6px; overflow:hidden"></div>
        <div id="calendarLegend" class="mt-3 p-3 bg-light rounded">
          <h6 class="mb-2">Legenda:</h6>
          <div id="calendarLegendItems" class="d-flex flex-wrap gap-2"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="kiosk-controls">
    <button id="kioskFullscreenBtn" class="kiosk-ctrl-icon" title="Fullscreen"><i class="fas fa-expand"></i></button>
    <button id="kioskQrBtn" class="kiosk-ctrl-icon" title="Tampilkan QR Exit"><i class="fas fa-qrcode"></i></button>
    <button id="exitKioskBtn" class="kiosk-ctrl-icon" title="Keluar (esc)"><i class="fas fa-sign-out-alt"></i></button>
  </div>
  <div id="kioskHelpOverlay">
    <div class="content">
      <h4>Keyboard shortcuts</h4>
      <ul>
        <li><strong>Arrow Left / Right</strong> — Navigasi tanggal</li>
        <li><strong>B</strong> — Bulanan</li>
        <li><strong>W</strong> — Mingguan</li>
        <li><strong>L</strong> — Daftar (list)</li>
        <li><strong>T</strong> — Hari ini (today)</li>
        <li><strong>?</strong> atau <strong>F1</strong> — Tampilkan/ Sembunyikan panduan ini</li>
      </ul>
      <div class="text-end"><button id="closeHelpBtn" class="btn btn-sm btn-secondary">Tutup</button></div>
    </div>
  </div>
</div>

<style>
  .kiosk-fullscreen { background:#f6f7fb; min-height:100vh; }
  .stat-card { padding:12px; display:flex; align-items:center; gap:12px; }
  .stat-number { font-size:22px; font-weight:700; }
  .stat-label { font-size:12px; color:#6b7280; }
  .kiosk-list { max-height:70vh; overflow:auto; }
  .kiosk-event { padding:12px 14px; border-radius:8px; background:linear-gradient(180deg, rgba(255,255,255,0.96), rgba(250,245,255,0.96)); margin-bottom:10px; }
  .kiosk-event .title { font-weight:600; color:#7a1f55; }
  .kiosk-event .meta { font-size:14px; color:#6b7280; }
  .kiosk-event .meta i { color:#9ca3af; margin-right:6px; }
  .kiosk-event .time { font-size:18px; font-weight:700; color:#0f172a; }
  .welcome-banner { padding:18px; border-radius:8px; background: linear-gradient(90deg,#8b2d67,#7a1f55); color:#fff; margin-bottom:8px; }
  #calendarViewBtns .btn.active { background:#7a1f55; color:#fff; border-color: rgba(15,23,42,0.12); }
  .icon-container { width:48px; height:48px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:#eef2ff; color:#111827; }
  .icon-ruangan { background: #e6f4ff; color:#0ea5e9; }
  .icon-menunggu { background:#fff7ed; color:#f59e0b; }
  .icon-disetujui { background:#ecfdf5; color:#10b981; }
  .icon-total { background:#faf5ff; color:#8b5cf6; }
  /* help overlay */
  #kioskHelpOverlay { position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:4000; }
  #kioskHelpOverlay .content { background: rgba(255,255,255,0.98); padding:24px; border-radius:12px; max-width:720px; box-shadow:0 12px 40px rgba(2,6,23,0.4); }
  #kioskHelpOverlay h4 { margin-top:0; }
</style>

<script>
  // Perkuliahan filter
  document.addEventListener('DOMContentLoaded', function(){
    const perBtn = document.getElementById('perkuliahanToggle');
    if (!perBtn) return;
    // helper: show perkuliahan only (true) or kegiatan only (false)
    function applyPerkuliahanFilter(showPerkuliahan) {
      const items = document.querySelectorAll('#kioskEventList .kiosk-event');
      let visibleCount = 0;
      items.forEach(function(item){
        const dtype = (item.getAttribute('data-type') || '').toString().toLowerCase().trim();
        const isPer = (dtype === 'perkuliahan' || item.classList.contains('type-perkuliahan'));
        if (showPerkuliahan) {
          if (isPer) { item.style.display = ''; visibleCount++; }
          else item.style.display = 'none';
        } else {
          if (!isPer) { item.style.display = ''; visibleCount++; }
          else item.style.display = 'none';
        }
      });

      // placeholder handling
      const list = document.getElementById('kioskEventList');
      if (!list) return;
      let ph = document.getElementById('kioskFilterPlaceholder');
      if (visibleCount === 0) {
        if (!ph) {
          ph = document.createElement('div');
          ph.id = 'kioskFilterPlaceholder';
          ph.className = 'text-muted';
          ph.style.padding = '18px';
          ph.textContent = showPerkuliahan ? 'Tidak ada jadwal perkuliahan hari ini.' : 'Tidak ada kegiatan non-perkuliahan untuk hari ini.';
          list.appendChild(ph);
        } else {
          ph.textContent = showPerkuliahan ? 'Tidak ada jadwal perkuliahan hari ini.' : 'Tidak ada kegiatan non-perkuliahan untuk hari ini.';
        }
      } else {
        if (ph) ph.remove();
      }
    }

    // initialize default: show only kegiatan (non-perkuliahan)
    perBtn.setAttribute('aria-pressed', 'false');
    perBtn.classList.remove('active');
    perBtn.title = 'Tampilkan Perkuliahan';
    applyPerkuliahanFilter(false);

    perBtn.addEventListener('click', function(){
      const active = perBtn.getAttribute('aria-pressed') === 'true';
      const willShowPer = !active;
      perBtn.setAttribute('aria-pressed', willShowPer ? 'true' : 'false');
      perBtn.classList.toggle('active', willShowPer);
      perBtn.title = willShowPer ? 'Perkuliahan: Aktif' : 'Tampilkan Perkuliahan';
      applyPerkuliahanFilter(willShowPer);
    });
  });
</script>
@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<!-- QRCode library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(() => {
  const apiUrl = '{{ route('admin.api.kiosk.events') }}';
  const listEl = document.getElementById('kioskEventList');
  const calEl = document.getElementById('kioskCalendar');

  // greeting is handled in the top script via date/time; no duplicate clock here

  // Fetch events (approved only) and render list + calendar
  let fc;
  async function fetchEvents() {
    try {
      const res = await fetch(apiUrl, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Network');
      const json = await res.json();
    // console.log('kiosk fetch result', json);
  const events = (json.data || []).map(e => ({
      id: String(e.id),
      title: e.title,
      start: e.start,
      end: e.end,
      color: e.color || (e.extendedProps && e.extendedProps.color) || undefined,
      extendedProps: e.extendedProps || {},
  }));
      if (fc) {
        // replace event sources cleanly
        try { fc.getEventSources().forEach(src => src.remove()); } catch (err) {}
        if (events.length) fc.addEventSource(events);
        fc.refetchEvents();
        // update legend from freshly fetched events
        try { updateLegendFromEvents(events); } catch (e) { console.error(e); }
      }
    } catch (err) {
      console.error('Kiosk fetch error', err);
    }
  }
  // renderList removed: left column uses server-rendered $todayEvents for accuracy

  // FullCalendar init
  document.addEventListener('DOMContentLoaded', function() {
    // helper to compute and set calendar title from current view
    function updateCalendarTitle() {
      try {
        if (!fc) return;
        const viewType = fc.view.type;
        // use center date of the current view to determine the month displayed
        const center = fc.getDate();
        const start = center; // center is a Date object
        const end = center; // used only for fallback
        let title = '';
        if (viewType === 'dayGridMonth') {
          title = start.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        } else if (viewType === 'timeGridWeek' || viewType === 'listWeek') {
          const s = fc.view.activeStart ? new Date(fc.view.activeStart) : new Date(center);
          const e = fc.view.activeEnd ? new Date(fc.view.activeEnd - 1) : new Date(center);
          const sStr = s.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
          const eStr = e.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
          title = `${sStr} — ${eStr}`;
        } else {
          title = start.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        }
        const titleEl = document.getElementById('calendarTitle');
        if (titleEl) titleEl.textContent = title;
      } catch (e) { console.error('updateCalendarTitle', e); }
    }

    // helper to build legend under calendar from events array
    function updateLegendFromEvents(events) {
      try {
        const container = document.getElementById('calendarLegendItems');
        if (!container) return;
        container.innerHTML = '';
        const seen = new Map();
        (events || []).forEach(ev => {
          const props = ev.extendedProps || {};
          const label = props.user_name || props.peminjam || (props.type === 'perkuliahan' ? 'Perkuliahan' : (props.nama_pic || ev.title || 'Lainnya'));
          const color = String(ev.color || props.color || (props.type === 'perkuliahan' ? '#17a2b8' : '#6c757d'));
          if (seen.has(label)) return;
          seen.set(label, color);
        });
  // no default Perkuliahan entry for kiosk mode (show only kegiatan/peminjam)
        // render
        for (const [label, color] of seen.entries()) {
          const item = document.createElement('div');
          item.className = 'legend-item d-flex align-items-center me-3 mb-2';
          item.innerHTML = `<span class="legend-color-box me-2" style="width:18px;height:18px;display:inline-block;border-radius:4px;background:${color};border:1px solid rgba(0,0,0,0.06)"></span><span class="legend-label">${label}</span>`;
          container.appendChild(item);
        }
      } catch (e) { console.error('updateLegendFromEvents', e); }
    }

    fc = new FullCalendar.Calendar(calEl, {
      initialView: 'dayGridMonth',
      height: '100%',
      headerToolbar: false,
      events: [],
      dayMaxEventRows: true,
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
      displayEventTime: true,
      locale: 'id',
      datesSet: function(info) {
        // called on initial render and when view changes
        updateCalendarTitle();
      }
    });
    fc.render();
    // ensure title is set immediately (some environments don't fire datesSet on render)
    setTimeout(updateCalendarTitle, 30);
    fetchEvents();
    setInterval(fetchEvents, 30000); // refresh events every 30s

    // help overlay handlers
    const helpOverlay = document.getElementById('kioskHelpOverlay');
    const helpBtn = document.getElementById('kioskHelpBtn');
    const closeHelpBtn = document.getElementById('closeHelpBtn');
    function toggleHelp() { if (!helpOverlay) return; helpOverlay.style.display = helpOverlay.style.display === 'flex' ? 'none' : 'flex'; }
    if (helpBtn) helpBtn.addEventListener('click', toggleHelp);
    if (closeHelpBtn) closeHelpBtn.addEventListener('click', toggleHelp);
    document.addEventListener('keydown', function(e){ if (e.key === '?' || e.key === 'F1') { e.preventDefault(); toggleHelp(); } });

    // Calendar view buttons wiring
    const viewBtns = document.querySelectorAll('#calendarViewBtns [data-view]');
    function setActiveViewButton(view) {
      viewBtns.forEach(b => b.classList.toggle('active', b.getAttribute('data-view') === view));
    }
    viewBtns.forEach(btn => {
      btn.addEventListener('click', function(){
        const v = btn.getAttribute('data-view');
        try { fc.changeView(v); setActiveViewButton(v); setCalendarHeight(); } catch(e) { console.error(e); }
      });
    });
    // default active
    setActiveViewButton('dayGridMonth');

    // calculate calendar height so it doesn't get cut off
    function setCalendarHeight() {
      try {
        if (!calEl) return;
        const card = calEl.closest('.card-kiosk');
        const header = card ? card.querySelector('.d-flex.justify-content-between, .d-flex.align-items-center') : null;
        const headerHeight = header ? header.offsetHeight : 0;
        // subtract some padding/margins
        const padding = 28; // safe padding
        const h = (card ? card.clientHeight : calEl.clientHeight) - headerHeight - padding;
        if (h > 200) {
          fc.setOption('height', h);
          if (typeof fc.updateSize === 'function') fc.updateSize();
        } else {
          fc.setOption('height', 'auto');
          if (typeof fc.updateSize === 'function') fc.updateSize();
        }
      } catch (e) { console.error('setCalendarHeight', e); }
    }

    // call once and on resize
    setCalendarHeight();
    window.addEventListener('resize', setCalendarHeight);

    // Keyboard shortcuts: Left/Right navigate dates; b=Bulanan, w=Mingguan, l=List, t=Today
    document.addEventListener('keydown', function(e){
      const tag = document.activeElement && document.activeElement.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA') return;
      if (!fc) return;
      const k = e.key;
      if (k === 'ArrowLeft') { fc.prev(); }
      else if (k === 'ArrowRight') { fc.next(); }
      else if (k.toLowerCase() === 'b') { fc.changeView('dayGridMonth'); setActiveViewButton('dayGridMonth'); setCalendarHeight(); }
      else if (k.toLowerCase() === 'w') { fc.changeView('timeGridWeek'); setActiveViewButton('timeGridWeek'); setCalendarHeight(); }
      else if (k.toLowerCase() === 'l') { fc.changeView('listWeek'); setActiveViewButton('listWeek'); setCalendarHeight(); }
      else if (k.toLowerCase() === 't') { fc.today(); }
    });
  });

  // Rotate between list and calendar
  function showList() {
    viewState = 'list';
    const left = document.querySelector('.kiosk-left');
    const right = document.querySelector('.kiosk-right');
    if (left) left.style.display = 'block';
    if (right) right.style.display = 'none';
  }
  function showCalendar() {
    viewState = 'calendar';
    const left = document.querySelector('.kiosk-left');
    const right = document.querySelector('.kiosk-right');
    if (left) left.style.display = 'none';
    if (right) right.style.display = 'block';
  }
  // Keyboard exit (Esc)
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      window.location.href = '{{ route('admin.home') }}';
    }
  });
  // Ensure both panels visible by default
  const left = document.querySelector('.kiosk-left');
  const right = document.querySelector('.kiosk-right');
  if (left) left.style.display = 'block';
  if (right) right.style.display = 'block';
  // Fullscreen button
  const fsBtn = document.getElementById('kioskFullscreenBtn');
  if (fsBtn) {
    fsBtn.addEventListener('click', function() {
      const el = document.getElementById('kiosk-root');
      if (!document.fullscreenElement) {
        if (el.requestFullscreen) el.requestFullscreen();
        else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
        // swap icon to compress
        const ic = fsBtn.querySelector('i');
        if (ic) { ic.classList.remove('fa-expand'); ic.classList.add('fa-compress'); }
        fsBtn.setAttribute('aria-pressed', 'true');
      } else {
        if (document.exitFullscreen) document.exitFullscreen();
        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
        const ic = fsBtn.querySelector('i');
        if (ic) { ic.classList.remove('fa-compress'); ic.classList.add('fa-expand'); }
        fsBtn.setAttribute('aria-pressed', 'false');
      }
    });
  }

  // QR modal (simple): show modal with URL to exit kiosk or admin.home
  const qrBtn = document.getElementById('kioskQrBtn');
  if (qrBtn) {
    qrBtn.addEventListener('click', function() {
      let modal = document.getElementById('kioskQrModal');
      const kioskRoot = document.getElementById('kiosk-root') || document.body;
      const exitUrl = '{{ route('admin.home') }}';
      if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'kioskQrModal';
        modal.innerHTML = `
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
              <div class="modal-header">
                <h5 class="modal-title">QR Exit Kiosk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body text-center">
                <p>Scan QR untuk membuka halaman admin.</p>
                <div id="kioskQrImg" style="margin:0 auto; width:200px; height:200px;
                  display:flex; align-items:center; justify-content:center;"></div>
                <p class="mt-2"><small id="kioskQrUrl">${exitUrl}</small></p>
                <button id="copyKioskUrl" class="btn btn-sm btn-primary">Copy URL</button>
              </div>
            </div>
          </div>
        `;
        // append to kiosk root so modal shows when kiosk-root is fullscreen
        kioskRoot.appendChild(modal);
      }

      // set URL text
      const urlEl = modal.querySelector('#kioskQrUrl');
      urlEl.textContent = exitUrl;

      // generate QR code (clear previous if any)
      const qrContainer = modal.querySelector('#kioskQrImg');
      if (qrContainer) {
        qrContainer.innerHTML = '';
        try {
          // QRCode from qrcodejs expects element and options
          new QRCode(qrContainer, { text: exitUrl, width: 200, height: 200 });
        } catch (e) {
          // fallback to showing URL text
          qrContainer.textContent = exitUrl;
        }
      }

      // Copy handler
      const copyBtn = modal.querySelector('#copyKioskUrl');
      if (copyBtn) {
        copyBtn.addEventListener('click', function() {
          navigator.clipboard && navigator.clipboard.writeText(urlEl.textContent)
            .then(() => alert('URL disalin'))
            .catch(() => alert('Gagal menyalin'));
        });
      }

      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
    });
  }

  // Exit Kiosk button
  const exitBtn = document.getElementById('exitKioskBtn');
  if (exitBtn) {
    exitBtn.addEventListener('click', function() {
      window.location.href = '{{ route('admin.home') }}';
    });
  }

})();
</script>

@endsection
