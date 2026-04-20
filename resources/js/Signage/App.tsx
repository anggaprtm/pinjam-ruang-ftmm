import React, { useEffect, useState, useRef } from 'react';
import Header from './components/Header';
import LecturesPanel from './components/LecturesPanel';
import EventsPanel from './components/EventsPanel';
import MeetingsPanel from './components/MeetingsPanel';
import CarStatusWidget from './components/CarStatusWidget';
import PendingRequestsWidget from './components/PendingRequestsWidget';
import AgendaFakultasPanel from './components/AgendaFakultasPanel';
import RoomAvailabilityPanel from './components/RoomAvailabilityPanel';
import { AgendaItem, ApiResponse, Meeting } from './types';

const getSignageApiKey = () =>
  document.querySelector('meta[name="signage-api-key"]')?.getAttribute('content') || '';

type CenterPanel = 'events' | 'agenda' | 'rooms';

// ── Ticker CSS ─────────────────────────────────────────────────
const TICKER_STYLE = `
@keyframes ticker-scroll {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.ticker-track {
  display: inline-block;
  white-space: nowrap;
  animation: ticker-scroll 30s linear infinite;
  will-change: transform;
}
.ticker-track:hover { animation-play-state: paused; }

@keyframes flash-update {
  0%, 100% { opacity: 0; }
  20%, 80%  { opacity: 1; }
}
.flash-update {
  animation: flash-update 1.5s ease forwards;
}
`;

const App: React.FC = () => {
  const [lectures, setLectures]         = useState<AgendaItem[]>([]);
  const [events, setEvents]             = useState<AgendaItem[]>([]);
  const [jadwalUjian, setJadwalUjian]   = useState<AgendaItem[]>([]);
  const [meetingsData, setMeetingsData] = useState<Meeting[]>([]);
  const [rooms, setRooms]               = useState<any[]>([]);
  const [isOnline, setIsOnline]         = useState<boolean>(navigator.onLine);
  const [lastUpdate, setLastUpdate]     = useState<string>('');
  const [signageMode, setSignageMode]   = useState<'dashboard' | 'announcement'>('dashboard');
  const [config, setConfig]             = useState<any>(null);
  const [fade, setFade]                 = useState(true);
  const [progress, setProgress]         = useState(0);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [panelFade, setPanelFade]       = useState(true);
  const [centerPanel, setCenterPanel]   = useState<CenterPanel>('events');
  const [dataVisible, setDataVisible]   = useState(true);
  const [showUpdated, setShowUpdated]   = useState(false);
  const updateTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  // Inject ticker CSS
  useEffect(() => {
    if (document.getElementById('signage-style')) return;
    const el = document.createElement('style');
    el.id = 'signage-style';
    el.textContent = TICKER_STYLE;
    document.head.appendChild(el);
  }, []);

  const urlParams       = new URLSearchParams(window.location.search);
  const lantai          = urlParams.get('lantai');
  const gedung          = urlParams.get('gedung');
  const isMainDashboard = !lantai && !gedung;
  const locationTitle   = isMainDashboard
    ? 'Gedung Nano • Fakultas Teknologi Maju dan Multidisiplin'
    : `Gedung ${gedung ?? '-'} • Lantai ${lantai ?? '-'}`;

  const isPanelVisible = (panelName: string) => {
    if (!config || !config.panel_visibility) return true;
    const value = config.panel_visibility[panelName];
    if (value === undefined) return true;
    return value !== false && value !== '0' && value !== 0;
  };

  const showLectures  = isPanelVisible('lectures');
  const showPending   = isMainDashboard && isPanelVisible('pending_requests');
  const showLeftCol   = showLectures || showPending;
  const showEvents    = isPanelVisible('events');
  const showAgenda    = isPanelVisible('agenda');
  const showRooms     = isPanelVisible('rooms');
  const showCenterCol = showEvents || showAgenda || showRooms;
  const showMeetings  = isPanelVisible('meetings');
  const showCars      = isMainDashboard && isPanelVisible('cars');
  const showRightCol  = showMeetings || showCars;

  const centerColClass = (!showLeftCol && !showRightCol)
    ? 'col-span-12'
    : (!showLeftCol || !showRightCol)
      ? 'col-span-9'
      : 'col-span-6';

  const availableCenterPanels: CenterPanel[] = [];
  if (showEvents) availableCenterPanels.push('events');
  if (showAgenda) availableCenterPanels.push('agenda');
  if (showRooms)  availableCenterPanels.push('rooms');

  useEffect(() => {
    if (!availableCenterPanels.includes(centerPanel) && availableCenterPanels.length > 0) {
      setCenterPanel(availableCenterPanels[0]);
    }
  }, [JSON.stringify(availableCenterPanels)]);

  // Auto-rotate center panel
  useEffect(() => {
    if (signageMode !== 'dashboard' || availableCenterPanels.length <= 1) return;
    const iv = setInterval(() => {
      setPanelFade(false);
      setTimeout(() => {
        setCenterPanel(prev => {
          const idx = availableCenterPanels.indexOf(prev);
          return availableCenterPanels[(idx + 1) % availableCenterPanels.length] ?? availableCenterPanels[0];
        });
        setPanelFade(true);
      }, 400);
    }, 30000);
    return () => clearInterval(iv);
  }, [signageMode, JSON.stringify(availableCenterPanels)]);

  // Keyboard shortcuts
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (signageMode !== 'dashboard') return;
      const map: Record<string, CenterPanel | undefined> = {
        r: showRooms  ? 'rooms'  : undefined,
        e: showEvents ? 'events' : undefined,
        a: showAgenda ? 'agenda' : undefined,
      };
      const target = map[e.key.toLowerCase()];
      if (target && target !== centerPanel) {
        setPanelFade(false);
        setTimeout(() => { setCenterPanel(target); setPanelFade(true); }, 400);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [centerPanel, signageMode, showRooms, showEvents, showAgenda]);

  const triggerDataUpdate = (updater: () => void) => {
    setDataVisible(false);
    setTimeout(() => {
      updater();
      setDataVisible(true);
      setShowUpdated(true);
      if (updateTimerRef.current) clearTimeout(updateTimerRef.current);
      updateTimerRef.current = setTimeout(() => setShowUpdated(false), 2500);
    }, 300);
  };

  const fetchData = async () => {
    if (signageMode !== 'dashboard') return;
    try {
      const apiUrl = new URL('/api/v1/signage', window.location.origin);
      if (lantai) apiUrl.searchParams.append('lantai', lantai);
      if (gedung)  apiUrl.searchParams.append('gedung', gedung);
      const apiKey = getSignageApiKey();
      if (apiKey) apiUrl.searchParams.set('signage_key', apiKey);

      const res  = await fetch(apiUrl.toString(), {
        headers: {
          'Accept': 'application/json',
          ...(apiKey ? { 'X-SIGNAGE-KEY': apiKey } : {}),
        },
      });
      if (!res.ok) throw new Error(`API ${res.status}`);
      const data: ApiResponse = await res.json();

      triggerDataUpdate(() => {
        setLectures(data.lectures   ?? []);
        setEvents(data.events       ?? []);
        setJadwalUjian(data.jadwal_ujian ?? []);
        setMeetingsData(data.meetings ?? []);
        setRooms(data.rooms         ?? []);
        setConfig(data.config       ?? null);
        if (data.config?.mode === 'announcement') {
          setSignageMode('announcement');
        } else {
          setSignageMode('dashboard');
        }
        setLastUpdate(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
      });
    } catch (err) {
      console.error('Fetch error:', err);
    }
  };

  useEffect(() => {
    fetchData();
    const iv = setInterval(fetchData, 60000);
    window.addEventListener('online',  () => setIsOnline(true));
    window.addEventListener('offline', () => setIsOnline(false));
    return () => {
      clearInterval(iv);
      window.removeEventListener('online',  () => setIsOnline(true));
      window.removeEventListener('offline', () => setIsOnline(false));
    };
  }, []);

  // ── ANNOUNCEMENT MODE ──────────────────────────────────────────
  if (signageMode === 'announcement' && config) {
    const contents = config.contents;
    const content  = contents?.length
      ? contents[currentIndex]
      : { type: config.content_type, value: config.content_value };
    return (
      <div className={`fixed inset-0 z-[9999] bg-black flex items-center justify-center transition-opacity duration-500 ${fade ? 'opacity-100' : 'opacity-0'}`}>
        {content?.type === 'image' && <img src={content.image_path ? `/storage/${content.image_path}` : content.value} className="w-full h-full object-contain" />}
        {content?.type === 'video' && (
          <video key={currentIndex} src={`/storage/${content.image_path}`} autoPlay muted playsInline className="w-full h-full object-contain"
            onTimeUpdate={e => { const v = e.currentTarget; if (v.duration) setProgress((v.currentTime / v.duration) * 100); }}
            onEnded={() => { setFade(false); setTimeout(() => { setCurrentIndex(p => (p + 1) % config.contents.length); setFade(true); }, 300); }}
          />
        )}
        {content?.type === 'text' && <div className="text-white text-6xl text-center px-20">{content.value}</div>}
        <div className="absolute bottom-0 left-0 w-full h-2 bg-white/20">
          <div className="h-full bg-white transition-all duration-100" style={{ width: `${progress}%` }} />
        </div>
      </div>
    );
  }

  // ── DASHBOARD MODE ─────────────────────────────────────────────
  return (
    // Background: permukaan terang khas FTMM light theme
    <div className="relative h-screen w-full bg-surface-1 text-ink-primary overflow-hidden">

      {/* Dekorasi radial maroon sangat halus */}
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(116,24,71,0.04)_0%,_transparent_55%)] pointer-events-none" />
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_rgba(116,24,71,0.03)_0%,_transparent_55%)] pointer-events-none" />

      <div className="relative z-10 flex flex-col h-full px-5 pt-4 gap-3 max-w-[3200px] mx-auto">

        {/* Header */}
        <div className="shrink-0">
          <Header customTitle={locationTitle} />
        </div>

        {/* Panel grid */}
        <div
          className="grid grid-cols-12 gap-4 flex-1 min-h-0 transition-opacity duration-300"
          style={{ opacity: dataVisible ? 1 : 0 }}
        >
          {/* ── KOLOM KIRI ── */}
          {showLeftCol && (
            <div className="col-span-3 flex flex-col gap-3 min-h-0">
              {showLectures && <div className="flex-1 min-h-0"><LecturesPanel data={lectures} ujianData={jadwalUjian} /></div>}
              {showPending  && (
                <div className={showLectures ? 'shrink-0' : 'flex-1 min-h-0 [&>div]:h-full'}>
                  <PendingRequestsWidget />
                </div>
              )}
            </div>
          )}

          {/* ── KOLOM TENGAH ── */}
          {showCenterCol && (
            <div className={`${centerColClass} min-h-0 flex flex-col transition-all duration-500`}>

              {/* Tabs */}
              <div className="shrink-0 flex items-center gap-2 mb-2">
                {showEvents && (
                  <button
                    onClick={() => { setPanelFade(false); setTimeout(() => { setCenterPanel('events'); setPanelFade(true); }, 400); }}
                    className={`text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full transition-all ${
                      centerPanel === 'events'
                        ? 'bg-maroon-600 text-white shadow-sm-brand'
                        : 'text-ink-secondary hover:text-ink-primary bg-surface-2 border border-surface-border'
                    }`}
                  >
                    Agenda Kegiatan
                  </button>
                )}
                {showAgenda && (
                  <button
                    onClick={() => { setPanelFade(false); setTimeout(() => { setCenterPanel('agenda'); setPanelFade(true); }, 400); }}
                    className={`text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full transition-all ${
                      centerPanel === 'agenda'
                        ? 'bg-maroon-600 text-white shadow-sm-brand'
                        : 'text-ink-secondary hover:text-ink-primary bg-surface-2 border border-surface-border'
                    }`}
                  >
                    Agenda Fakultas
                  </button>
                )}
                {showRooms && (
                  <button
                    onClick={() => { setPanelFade(false); setTimeout(() => { setCenterPanel('rooms'); setPanelFade(true); }, 400); }}
                    className={`text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full transition-all ${
                      centerPanel === 'rooms'
                        ? 'bg-success-600 text-white'
                        : 'text-ink-secondary hover:text-ink-primary bg-surface-2 border border-surface-border'
                    }`}
                  >
                    Ketersediaan Ruang
                  </button>
                )}
                {availableCenterPanels.length > 1 && (
                  <div className="ml-auto flex items-center gap-1.5 text-[10px] text-ink-muted font-mono">
                    <span className="w-1.5 h-1.5 rounded-full bg-maroon-300 animate-pulse" />
                    auto
                  </div>
                )}
              </div>

              <div className={`flex-1 min-h-0 transition-opacity duration-400 ${panelFade ? 'opacity-100' : 'opacity-0'}`}>
                {centerPanel === 'events' && showEvents  ? <EventsPanel data={events} />
                : centerPanel === 'agenda' && showAgenda ? <AgendaFakultasPanel />
                : centerPanel === 'rooms'  && showRooms  ? <RoomAvailabilityPanel data={rooms} />
                : <div className="h-full flex items-center justify-center text-ink-muted text-sm font-mono tracking-widest uppercase">Panel Dinonaktifkan</div>
                }
              </div>
            </div>
          )}

          {/* ── KOLOM KANAN ── */}
          {showRightCol && (
            <div className="col-span-3 flex flex-col gap-3 min-h-0">
              {showMeetings && <div className="flex-1 min-h-0"><MeetingsPanel data={meetingsData} /></div>}
              {showCars && (
                <div className={showMeetings ? 'shrink-0' : 'flex-1 min-h-0 [&>div]:h-full'}>
                  <CarStatusWidget />
                </div>
              )}
            </div>
          )}
        </div>

        {/* ── RUNNING TEXT TICKER ── */}
        {config?.running_text && (
          <div className="shrink-0 flex items-center overflow-hidden h-9 rounded-xl shadow-sm-brand mb-1"
               style={{ background: 'linear-gradient(135deg, #741847 0%, #9c2456 100%)' }}>

            {/* Badge INFO */}
            <div className="relative shrink-0 bg-maroon-800 text-white text-[11px] font-extrabold px-5 h-full flex items-center uppercase tracking-widest z-10">
              INFO
              {/* Segitiga aksen */}
              <div className="absolute top-0 -right-2.5 w-0 h-0
                border-t-[18px] border-t-transparent
                border-b-[18px] border-b-transparent
                border-l-[10px] border-l-maroon-800" />
            </div>

            {/* Track teks berjalan */}
            <div className="flex-1 overflow-hidden relative h-full flex items-center pl-6">
              <span
                className="ticker-track text-[13px] font-semibold tracking-wide text-white/90"
                style={{ animationDuration: `${Math.max(15, config.running_text.length * 0.35)}s` }}
              >
                {config.running_text}&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;{config.running_text}
              </span>
            </div>
          </div>
        )}

        {/* ── FOOTER ── */}
        <div className="shrink-0 h-7 flex items-center justify-between px-3 text-[11px] border-t border-surface-border">
          <span className={`flex items-center gap-1.5 font-mono font-bold tracking-widest ${isOnline ? 'text-success-600' : 'text-danger-600'}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${isOnline ? 'bg-success-400 animate-pulse' : 'bg-danger-600'}`} />
            {isOnline ? 'ONLINE' : 'OFFLINE'}
          </span>

          <div className="flex items-center gap-3">
            {showUpdated && (
              <span className="flash-update text-maroon-600 font-mono font-bold text-[10px] flex items-center gap-1">
                <span className="w-1.5 h-1.5 rounded-full bg-maroon-400 inline-block" />
                Data diperbarui
              </span>
            )}
            <span className="text-ink-muted font-mono">
              {lastUpdate ? `Last update: ${lastUpdate}` : ''}
            </span>
          </div>
        </div>

      </div>
    </div>
  );
};

export default App;