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
  const [locationTitle, setLocationTitle] = useState("Gedung Nano • FTMM");

  const [isMainDashboard, setIsMainDashboard] = useState(true);
  const [signageMode, setSignageMode] = useState<'dashboard' | 'announcement'>('dashboard');
  const [config, setConfig] = useState<any>(null);

  // 🔥 SLIDESHOW STATE
  const [currentIndex, setCurrentIndex] = useState(0);

  const fetchData = async () => {
    if (signageMode !== 'dashboard') return;

    try {
      const params = new URLSearchParams(window.location.search);
      const lantai = params.get('lantai');
      const gedung = params.get('gedung');

      if (lantai || gedung) {
        setIsMainDashboard(false);
        setLocationTitle(`${'Gedung ' + gedung || 'Gedung'} • Lantai ${lantai || '-'}`);
      } else {
        setIsMainDashboard(true);
        setLocationTitle("Gedung Nano • FTMM");
      }

      const apiUrl = new URL('/api/v1/signage', window.location.origin);
      if (lantai) apiUrl.searchParams.append('lantai', lantai);
      if (gedung) apiUrl.searchParams.append('gedung', gedung);

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

  // FETCH DATA
  useEffect(() => {
    fetchData();
    const interval = setInterval(fetchData, 60000);
    return () => clearInterval(interval);
  }, [signageMode]);

  // FETCH CONFIG
  useEffect(() => {
    async function fetchConfig() {
      try {
        const params = new URLSearchParams(window.location.search);
        const lantai = params.get('lantai') || 'default';

        const res = await fetch(`/api/v1/display-config/${lantai}`);
        const data = await res.json();

        setSignageMode(data.mode || 'dashboard');
        setConfig(data);
        setCurrentIndex(0);
      } catch (err) {
        console.error(err);
        setSignageMode('dashboard');
      }
    }

    fetchConfig();
    const interval = setInterval(fetchConfig, 10000);
    return () => clearInterval(interval);
  }, []);

  // 🔥 SLIDESHOW EFFECT
  useEffect(() => {
    if (signageMode !== 'announcement') return;

    const contents = config?.contents;

    // fallback single content
    if (!contents || contents.length === 0) return;

    const duration = contents[currentIndex]?.duration || 5;

    const interval = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % contents.length);
    }, duration * 1000);

    return () => clearInterval(interval);
  }, [config, currentIndex, signageMode]);

  // 🔥 ANNOUNCEMENT MODE
  if (signageMode === 'announcement' && config) {
    const contents = config.contents;

    let content = null;

    // MULTI CONTENT
    if (contents && contents.length > 0) {
      content = contents[currentIndex];
    } 
    // BACKWARD COMPATIBLE
    else {
      content = {
        type: config.content_type,
        value: config.content_value
      };
    }

    return (
      <div className="fixed inset-0 z-[9999] bg-black flex items-center justify-center">

        {content?.type === 'image' && (
          <img
            src={content.image_path
              ? `/storage/${content.image_path}`
              : content.value}
            className="w-full h-full object-contain"
          />
        )}

        {content?.type === 'text' && (
          <div className="text-white text-6xl text-center px-20">
            {content.value}
          </div>
        )}

      </div>
    );
  }

  // DASHBOARD MODE
  return (
    <div className="relative h-screen w-full bg-navy-900 text-slate-900 overflow-hidden">

      <div className="relative z-10 flex flex-col h-full px-6 pt-6 gap-3 max-w-[2400px] mx-auto">
        
        <Header customTitle={locationTitle} />

        <div className="grid grid-cols-12 gap-6 flex-1 min-h-0">
          
          <div className="col-span-3 flex flex-col gap-4">
            <LecturesPanel data={lectures} />

            {isMainDashboard && <PendingRequestsWidget />}
          </div>

          <div className="col-span-6">
            <EventsPanel data={events} />
          </div>

          <div className="col-span-3 flex flex-col gap-4">
            <MeetingsPanel data={meetingsData} />

            {isMainDashboard && <CarStatusWidget />}
          </div>

        </div>

        <div className="h-8 text-xs flex items-center justify-between px-4 text-white/50">
          <span>{isOnline ? 'ONLINE' : 'OFFLINE'}</span>
          <span>{lastUpdate}</span>
        </div>

      </div>
    </div>
  );
};

export default App;