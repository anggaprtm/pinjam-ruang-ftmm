import React, { useEffect, useState } from 'react';
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

// Panel tengah yang tersedia
type CenterPanel = 'events' | 'agenda' | 'rooms';

const App: React.FC = () => {
  const [lectures, setLectures]         = useState<AgendaItem[]>([]);
  const [events, setEvents]             = useState<AgendaItem[]>([]);
  const [meetingsData, setMeetingsData] = useState<Meeting[]>([]);
  const [isOnline, setIsOnline]         = useState<boolean>(navigator.onLine);
  const [lastUpdate, setLastUpdate]     = useState<string>(new Date().toLocaleTimeString());
  const [signageMode, setSignageMode]   = useState<'dashboard' | 'announcement'>('dashboard');
  const [config, setConfig]             = useState<any>(null);
  const [fade, setFade]                 = useState(true);
  const [progress, setProgress]         = useState(0);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [rooms, setRooms] = useState<any[]>([]);
  const [panelFade, setPanelFade]       = useState(true);
  const [centerPanel, setCenterPanel]   = useState<CenterPanel>('events');

  // 1. HELPER: CEK VISIBILITAS PANEL (Harus di atas sebelum dipanggil)
  const isPanelVisible = (panelName: string) => {
    if (!config || !config.panel_visibility) return true;
    const value = config.panel_visibility[panelName];
    if (value === undefined) return true;
    return value !== false && value !== "0" && value !== 0;
  };

  // 2. URL PARAMS
  const urlParams       = new URLSearchParams(window.location.search);
  const lantai          = urlParams.get('lantai');
  const gedung          = urlParams.get('gedung');
  const isMainDashboard = !lantai && !gedung;
  const locationTitle   = isMainDashboard
    ? 'Gedung Nano • FTMM'
    : `Gedung ${gedung ?? '-'} • Lantai ${lantai ?? '-'}`;
  const location = `lantai${lantai ?? '0'}`;

  // 3. KALKULASI GRID (Digabung di sini semua, jangan ada yang dobel)
  const showLectures = isPanelVisible('lectures');
  const showPending  = isMainDashboard && isPanelVisible('pending_requests');
  const showLeftCol  = showLectures || showPending;

  const showEvents   = isPanelVisible('events');
  const showAgenda   = isPanelVisible('agenda');
  const showRooms    = isPanelVisible('rooms'); // <--- Tambahkan di sini
  const showCenterCol = showEvents || showAgenda || showRooms; // <--- Update di sini

  const showMeetings = isPanelVisible('meetings');
  const showCars     = isMainDashboard && isPanelVisible('cars');
  const showRightCol = showMeetings || showCars;

  // Tentukan lebar kolom tengah secara otomatis agar responsif menutupi layar
  const centerColClass = (!showLeftCol && !showRightCol) 
      ? 'col-span-12' 
      : (!showLeftCol || !showRightCol) 
          ? 'col-span-9' 
          : 'col-span-6';

  // ─── LOGIC PANEL TENGAH (ROTASI DINAMIS) ──────────────────────
  const availableCenterPanels: CenterPanel[] = [];
  if (showEvents) availableCenterPanels.push('events');
  if (showAgenda) availableCenterPanels.push('agenda');
  if (showRooms)  availableCenterPanels.push('rooms');

  useEffect(() => {
    // Force switch ke panel yang tersedia jika panel yang aktif saat ini dimatikan dari admin
    if (!availableCenterPanels.includes(centerPanel) && availableCenterPanels.length > 0) {
      setCenterPanel(availableCenterPanels[0]);
    }
  }, [availableCenterPanels, centerPanel]);

  useEffect(() => {
    if (signageMode !== 'dashboard' || availableCenterPanels.length <= 1) return;

    const iv = setInterval(() => {
      setPanelFade(false);
      setTimeout(() => {
        setCenterPanel(prev => {
          const currentIndex = availableCenterPanels.indexOf(prev);
          const nextIndex = (currentIndex + 1) % availableCenterPanels.length;
          return availableCenterPanels[nextIndex] || availableCenterPanels[0];
        });
        setPanelFade(true);
      }, 400);
    }, 30000); // 30 Detik
    
    return () => clearInterval(iv);
  }, [signageMode, availableCenterPanels]);

  // ─── KEYBOARD SHORTCUTS ───────────────────────────────────────
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (signageMode !== 'dashboard') return;
      const key = e.key.toLowerCase();
      
      let targetPanel: CenterPanel | null = null;
      if (key === 'r' && showRooms)  targetPanel = 'rooms';
      if (key === 'e' && showEvents) targetPanel = 'events';
      if (key === 'a' && showAgenda) targetPanel = 'agenda';

      if (targetPanel && targetPanel !== centerPanel) {
        setPanelFade(false);
        setTimeout(() => {
          setCenterPanel(targetPanel as CenterPanel);
          setPanelFade(true);
        }, 400);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [centerPanel, signageMode, showRooms, showEvents, showAgenda]);

  // ─── FETCH DATA ───────────────────────────────────────────────
  const fetchData = async () => {
    if (signageMode !== 'dashboard') return;
    try {
      const apiUrl = new URL('/api/v1/signage', window.location.origin);
      if (lantai) apiUrl.searchParams.append('lantai', lantai);
      if (gedung)  apiUrl.searchParams.append('gedung', gedung);
      const apiKey = getSignageApiKey();
      if (apiKey) apiUrl.searchParams.set('signage_key', apiKey);

      const res  = await fetch(apiUrl.toString());
      const data: ApiResponse = await res.json();
      setLectures(data.jadwal_kuliah_hari_ini);
      setEvents(data.kegiatan_mendatang);
      setRooms(data.room_availability);
      setMeetingsData(data.sidang_rapat);
      setIsOnline(true);
      setLastUpdate(new Date().toLocaleTimeString('id-ID') + ' WIB');
    } catch {
      setIsOnline(false);
    }
  };

  useEffect(() => {
    fetchData();
    const iv = setInterval(fetchData, 60000);
    return () => clearInterval(iv);
  }, [signageMode]);

  // ─── FETCH CONFIG ─────────────────────────────────────────────
  useEffect(() => {
    const fetchConfig = async () => {
      try {
        const res  = await fetch(`/api/v1/display-config/${lantai ?? 'default'}`);
        const data = await res.json();
        setSignageMode(data.mode || 'dashboard');
        setConfig((prev: any) => {
          if (JSON.stringify(prev) === JSON.stringify(data)) return prev;
          setCurrentIndex(0);
          return data;
        });
      } catch {
        setSignageMode('dashboard');
      }
    };
    fetchConfig();
    const iv = setInterval(fetchConfig, 30000);
    return () => clearInterval(iv);
  }, []);

  // ─── CURSOR ───────────────────────────────────────────────────
  useEffect(() => {
    document.body.style.cursor = signageMode === 'announcement' ? 'none' : 'default';
  }, [signageMode]);

  // ─── DEVICE COMMAND ───────────────────────────────────────────
  useEffect(() => {
    const iv = setInterval(async () => {
      try {
        const res  = await fetch(`/api/device-command/${location}`);
        const data = await res.json();
        if (data.command === 'reload' || data.command === 'restart') window.location.reload();
      } catch { /* silent */ }
    }, 5000);
    return () => clearInterval(iv);
  }, [location]);

  // ─── SLIDESHOW TIMER ──────────────────────────────────────────
  useEffect(() => {
    if (signageMode !== 'announcement') return;
    const contents = config?.contents;
    if (!contents?.length) return;
    const current = contents[currentIndex];
    if (current.type === 'video') return;

    setProgress(0);
    const duration = (current.duration || 5) * 1000;
    const start    = Date.now();
    const iv       = setInterval(() => setProgress(Math.min(((Date.now() - start) / duration) * 100, 100)), 100);
    const to       = setTimeout(() => {
      setFade(false);
      setTimeout(() => { setCurrentIndex(p => (p + 1) % contents.length); setFade(true); }, 300);
    }, duration);
    return () => { clearInterval(iv); clearTimeout(to); };
  }, [currentIndex, config, signageMode]);

  // ─── ANNOUNCEMENT MODE ────────────────────────────────────────
  if (signageMode === 'announcement' && config) {
    const contents = config.contents;
    const content  = contents?.length
      ? contents[currentIndex]
      : { type: config.content_type, value: config.content_value };

    return (
      <div className={`fixed inset-0 z-[9999] bg-black flex items-center justify-center transition-opacity duration-500 ${fade ? 'opacity-100' : 'opacity-0'}`}>
        {content?.type === 'image' && (
          <img src={content.image_path ? `/storage/${content.image_path}` : content.value} className="w-full h-full object-contain" />
        )}
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

  // ─── DASHBOARD MODE ───────────────────────────────────────────
  return (
    <div className="relative h-screen w-full bg-navy-900 text-white overflow-hidden">

      {/* Ambient background */}
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(45,212,191,0.04)_0%,_transparent_60%)] pointer-events-none" />
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_rgba(59,130,246,0.04)_0%,_transparent_60%)] pointer-events-none" />

      <div className="relative z-10 flex flex-col h-full px-5 pt-4 gap-3 max-w-[3200px] mx-auto">

        <div className="shrink-0">
          <Header customTitle={locationTitle} />
        </div>

        <div className="grid grid-cols-12 gap-4 flex-1 min-h-0">

          {/* ── KOLOM KIRI ── */}
          {showLeftCol && (
            <div className="col-span-3 flex flex-col gap-3 min-h-0">
              {showLectures && (
                <div className="flex-1 min-h-0">
                  <LecturesPanel data={lectures} />
                </div>
              )}
              {showPending && (
                // Trik Tailwind [&>div]:h-full memastikan anak di dalamnya ikut melar
                <div className={showLectures ? "shrink-0" : "flex-1 min-h-0 [&>div]:h-full"}>
                  <PendingRequestsWidget />
                </div>
              )}
            </div>
          )}

          {/* ── KOLOM TENGAH ── */}
          {showCenterCol && (
            <div className={`${centerColClass} min-h-0 flex flex-col transition-all duration-500`}>
              
              {/* Tab indicator */}
              <div className="shrink-0 flex items-center gap-2 mb-2">
                {showEvents && (
                  <button
                    onClick={() => { setPanelFade(false); setTimeout(() => { setCenterPanel('events'); setPanelFade(true); }, 400); }}
                    className={`text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full transition-all ${
                      centerPanel === 'events' ? 'bg-electric-500/20 text-electric-400 border border-electric-500/40' : 'text-white/20 hover:text-white/40'
                    }`}
                  >
                    Agenda Kegiatan
                  </button>
                )}
                {showAgenda && (
                  <button
                    onClick={() => { setPanelFade(false); setTimeout(() => { setCenterPanel('agenda'); setPanelFade(true); }, 400); }}
                    className={`text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full transition-all ${
                      centerPanel === 'agenda' ? 'bg-electric-500/20 text-electric-400 border border-electric-500/40' : 'text-white/20 hover:text-white/40'
                    }`}
                  >
                    Agenda Fakultas
                  </button>
                )}
                {/* TAMBAHAN TAB ROOMS */}
                {showRooms && (
                  <button
                    onClick={() => { setPanelFade(false); setTimeout(() => { setCenterPanel('rooms'); setPanelFade(true); }, 400); }}
                    className={`text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full transition-all ${
                      centerPanel === 'rooms' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'text-white/20 hover:text-white/40'
                    }`}
                  >
                    Ketersediaan Ruang
                  </button>
                )}
                
                {/* Indikator Auto */}
                {availableCenterPanels.length > 1 && (
                  <div className="ml-auto flex items-center gap-1.5 text-[10px] text-white/20 font-mono">
                    <span className="w-1 h-1 rounded-full bg-white/20 animate-pulse" />
                    auto
                  </div>
                )}
              </div>

              {/* Panel dengan fade transition */}
              <div className={`flex-1 min-h-0 transition-opacity duration-400 ${panelFade ? 'opacity-100' : 'opacity-0'}`}>
                {centerPanel === 'events' && showEvents ? <EventsPanel data={events} />
                : centerPanel === 'agenda' && showAgenda ? <AgendaFakultasPanel />
                : centerPanel === 'rooms' && showRooms ? <RoomAvailabilityPanel data={rooms} /> // <--- Tambahan render
                : <div className="h-full flex items-center justify-center text-white/20 text-sm font-mono tracking-widest uppercase">Panel Dinonaktifkan</div>
                }
              </div>

            </div>
          )}

          {/* ── KOLOM KANAN ── */}
          {showRightCol && (
            <div className="col-span-3 flex flex-col gap-3 min-h-0">
              {showMeetings && (
                <div className="flex-1 min-h-0">
                  <MeetingsPanel data={meetingsData} />
                </div>
              )}
              {showCars && (
                <div className={showMeetings ? "shrink-0" : "flex-1 min-h-0 [&>div]:h-full"}>
                  <CarStatusWidget />
                </div>
              )}
            </div>
          )}

        </div>

        {/* ── RUNNING TEXT (NEWS TICKER) ── */}
        {config?.running_text && (
          <div className="shrink-0 bg-navy-800/60 border border-white/5 rounded-lg flex items-center overflow-hidden h-9 mb-1 shadow-md">
            <div className="shrink-0 bg-rose-600 text-white text-[11px] font-bold px-4 h-full flex items-center justify-center uppercase tracking-widest z-10 shadow-lg relative">
              INFORMASI
              {/* Segitiga aksen kecil di sebelah kanan label */}
              <div className="absolute top-0 -right-2 w-0 h-0 border-t-[18px] border-t-transparent border-b-[18px] border-b-transparent border-l-[8px] border-l-rose-600"></div>
            </div>
            
            {/* Ticker menggunakan dangerouslySetInnerHTML untuk menghindari TS error pada tag marquee */}
            <div
              className="flex-1 flex items-center h-full px-4 overflow-hidden"
              dangerouslySetInnerHTML={{
                __html: `<marquee scrollamount="6" class="text-[13px] font-semibold tracking-wider text-white/90 pt-1.5">${config.running_text}</marquee>`
              }}
            />
          </div>
        )}

        {/* FOOTER */}
        <div className="shrink-0 h-7 flex items-center justify-between px-3 text-[11px] border-t border-white/5">
          <span className={`flex items-center gap-1.5 font-mono font-bold tracking-widest ${isOnline ? 'text-emerald-400' : 'text-red-400'}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-red-400'}`} />
            {isOnline ? 'ONLINE' : 'OFFLINE'}
          </span>
          <span className="text-white/30 font-mono">Last update: {lastUpdate}</span>
        </div>

      </div>
    </div>
  );
};

export default App;