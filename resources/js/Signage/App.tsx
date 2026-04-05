import React, { useEffect, useState } from 'react';
import Header from './components/Header';
import LecturesPanel from './components/LecturesPanel';
import EventsPanel from './components/EventsPanel';
import MeetingsPanel from './components/MeetingsPanel';
import CarStatusWidget from './components/CarStatusWidget';
import PendingRequestsWidget from './components/PendingRequestsWidget';
import { AgendaItem, ApiResponse, Meeting } from './types';

const getSignageApiKey = () =>
  document.querySelector('meta[name="signage-api-key"]')?.getAttribute('content') || '';

const App: React.FC = () => {
  const [lectures, setLectures] = useState<AgendaItem[]>([]);
  const [events, setEvents] = useState<AgendaItem[]>([]);
  const [meetingsData, setMeetingsData] = useState<Meeting[]>([]);
  const [isOnline, setIsOnline] = useState<boolean>(navigator.onLine);
  const [lastUpdate, setLastUpdate] = useState<string>(new Date().toLocaleTimeString());
  const [signageMode, setSignageMode] = useState<'dashboard' | 'announcement'>('dashboard');
  const [config, setConfig] = useState<any>(null);
  const [fade, setFade] = useState(true);
  const [progress, setProgress] = useState(0);
  const [currentIndex, setCurrentIndex] = useState(0);

  // ✅ FIX 1: Baca URL params SEKALI di luar, tidak perlu state async
  const urlParams = new URLSearchParams(window.location.search);
  const lantai = urlParams.get('lantai');   // null kalau tidak ada param
  const gedung  = urlParams.get('gedung');  // null kalau tidak ada param

  // ✅ FIX 2: isMainDashboard dihitung langsung dari URL, bukan dari fetchData
  // Dashboard utama = tidak ada param lantai maupun gedung
  const isMainDashboard = !lantai && !gedung;

  // ✅ FIX 3: locationTitle dihitung langsung, tidak perlu state
  const locationTitle = isMainDashboard
    ? "Gedung Nano • FTMM"
    : `Gedung ${gedung ?? '-'} • Lantai ${lantai ?? '-'}`;

  const location = `lantai${lantai ?? '0'}`;

  const fetchData = async () => {
    if (signageMode !== 'dashboard') return;

    try {
      const apiUrl = new URL('/api/v1/signage', window.location.origin);
      if (lantai) apiUrl.searchParams.append('lantai', lantai);
      if (gedung)  apiUrl.searchParams.append('gedung', gedung);

      const apiKey = getSignageApiKey();
      if (apiKey) apiUrl.searchParams.set('signage_key', apiKey);

      const response = await fetch(apiUrl.toString());
      const data: ApiResponse = await response.json();

      setLectures(data.jadwal_kuliah_hari_ini);
      setEvents(data.kegiatan_mendatang);
      setMeetingsData(data.sidang_rapat);

      setIsOnline(true);
      setLastUpdate(new Date().toLocaleTimeString('id-ID') + ' WIB');
    } catch (error) {
      console.error(error);
      setIsOnline(false);
    }
  };

  // FETCH DATA setiap 60 detik
  useEffect(() => {
    fetchData();
    const interval = setInterval(fetchData, 60000);
    return () => clearInterval(interval);
  }, [signageMode]);

  // FETCH CONFIG (mode: dashboard / announcement) setiap 30 detik
  useEffect(() => {
    async function fetchConfig() {
      try {
        const configLantai = lantai ?? 'default';
        const res  = await fetch(`/api/v1/display-config/${configLantai}`);
        const data = await res.json();

        setSignageMode(data.mode || 'dashboard');
        setConfig(prev => {
          if (JSON.stringify(prev) === JSON.stringify(data)) return prev;
          setCurrentIndex(0);
          return data;
        });
      } catch (err) {
        console.error(err);
        setSignageMode('dashboard');
      }
    }

    fetchConfig();
    const interval = setInterval(fetchConfig, 30000);
    return () => clearInterval(interval);
  }, []);

  // Sembunyikan cursor di announcement mode
  useEffect(() => {
    document.body.style.cursor = signageMode === 'announcement' ? 'none' : 'default';
  }, [signageMode]);

  // Device command polling setiap 5 detik
  useEffect(() => {
    const interval = setInterval(async () => {
      try {
        const res  = await fetch(`/api/device-command/${location}`);
        const data = await res.json();
        if (!data.command) return;
        if (data.command === 'reload' || data.command === 'restart') {
          window.location.reload();
        }
      } catch (err) {
        console.error('Command error:', err);
      }
    }, 5000);
    return () => clearInterval(interval);
  }, [location]);

  // SLIDESHOW timer (untuk non-video)
  useEffect(() => {
    if (signageMode !== 'announcement') return;

    const contents = config?.contents;
    if (!contents || contents.length === 0) return;

    const current = contents[currentIndex];
    if (current.type === 'video') return; // video pakai onEnded

    setProgress(0);
    const duration = (current.duration || 5) * 1000;
    const start    = Date.now();

    const interval = setInterval(() => {
      setProgress(Math.min(((Date.now() - start) / duration) * 100, 100));
    }, 100);

    const timeout = setTimeout(() => {
      setFade(false);
      setTimeout(() => {
        setCurrentIndex(prev => (prev + 1) % contents.length);
        setFade(true);
      }, 300);
    }, duration);

    return () => {
      clearTimeout(timeout);
      clearInterval(interval);
    };
  }, [currentIndex, config, signageMode]);

  // ──────────────────────────────────────────
  // ANNOUNCEMENT MODE
  // ──────────────────────────────────────────
  if (signageMode === 'announcement' && config) {
    const contents = config.contents;
    const content  = (contents && contents.length > 0)
      ? contents[currentIndex]
      : { type: config.content_type, value: config.content_value };

    return (
      <div className={`fixed inset-0 z-[9999] bg-black flex items-center justify-center transition-opacity duration-500 ${fade ? 'opacity-100' : 'opacity-0'}`}>
        {content?.type === 'image' && (
          <img
            src={content.image_path ? `/storage/${content.image_path}` : content.value}
            className="w-full h-full object-contain"
          />
        )}
        {content?.type === 'video' && (
          <video
            key={currentIndex}
            src={`/storage/${content.image_path}`}
            autoPlay muted playsInline
            className="w-full h-full object-contain"
            onTimeUpdate={(e) => {
              const v = e.currentTarget;
              if (v.duration) setProgress((v.currentTime / v.duration) * 100);
            }}
            onEnded={() => {
              setFade(false);
              setTimeout(() => {
                setCurrentIndex(prev => (prev + 1) % config.contents.length);
                setFade(true);
              }, 300);
            }}
          />
        )}
        {content?.type === 'text' && (
          <div className="text-white text-6xl text-center px-20">{content.value}</div>
        )}
        <div className="absolute bottom-0 left-0 w-full h-2 bg-white/20">
          <div className="h-full bg-white transition-all duration-100" style={{ width: `${progress}%` }} />
        </div>
      </div>
    );
  }

  // ──────────────────────────────────────────
  // DASHBOARD MODE
  // ──────────────────────────────────────────
  return (
    <div className="relative h-screen w-full bg-navy-900 text-slate-900 overflow-hidden">
      <div className="relative z-10 flex flex-col h-full px-6 pt-6 gap-3 max-w-[2400px] mx-auto">

        {/* ✅ FIX 4: Pakai locationTitle, bukan string template langsung */}
        <Header customTitle={locationTitle} />

        <div className="grid grid-cols-12 gap-6 flex-1 min-h-0">

          {/* KOLOM KIRI */}
          <div className="col-span-3 flex flex-col gap-4 min-h-0">
            <LecturesPanel data={lectures} />
            {isMainDashboard && <PendingRequestsWidget />}
          </div>

          {/* KOLOM TENGAH */}
          <div className="col-span-6 min-h-0">
            <EventsPanel data={events} />
          </div>

          {/* KOLOM KANAN */}
          <div className="col-span-3 flex flex-col gap-4 min-h-0">
            <MeetingsPanel data={meetingsData} />
            {isMainDashboard && <CarStatusWidget />}
          </div>

        </div>

        {/* ✅ FIX 5: Footer pakai shrink-0 biar tidak ditimpa grid */}
        <div className="shrink-0 h-8 text-xs flex items-center justify-between px-4 text-white/50 border-t border-white/5">
          <span className={isOnline ? 'text-emerald-400' : 'text-red-400'}>
            {isOnline ? '● ONLINE' : '● OFFLINE'}
          </span>
          <span>Last update: {lastUpdate}</span>
        </div>

      </div>
    </div>
  );
};

export default App;