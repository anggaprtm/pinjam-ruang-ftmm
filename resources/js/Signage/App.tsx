import React, { useEffect, useState } from 'react';
import Header from './components/Header';
import LecturesPanel from './components/LecturesPanel';
import EventsPanel from './components/EventsPanel';
import MeetingsPanel from './components/MeetingsPanel';
import CarStatusWidget from './components/CarStatusWidget'; // 1. IMPORT WIDGET
import { AgendaItem, ApiResponse, Meeting } from './types';

const App: React.FC = () => {
  // ... state lainnya tetap sama ...
  const [lectures, setLectures] = useState<AgendaItem[]>([]);
  const [events, setEvents] = useState<AgendaItem[]>([]);
  const [meetingsData, setMeetingsData] = useState<Meeting[]>([]);
  const [isOnline, setIsOnline] = useState<boolean>(navigator.onLine);
  const [lastUpdate, setLastUpdate] = useState<string>(new Date().toLocaleTimeString());
  const [locationTitle, setLocationTitle] = useState("Gedung Nano • Fakultas Teknologi Maju dan Multidisiplin");
  
  // 2. STATE UNTUK DETEKSI DASHBOARD UTAMA
  const [isMainDashboard, setIsMainDashboard] = useState(true);

  const fetchData = async () => {
    try {
      const params = new URLSearchParams(window.location.search);
      const lantai = params.get('lantai');
      const gedung = params.get('gedung');

      // 3. LOGIC PENENTUAN: Jika ada filter lantai/gedung, berarti BUKAN Dashboard Utama
      if (lantai || gedung) {
         setIsMainDashboard(false); // Sembunyikan Mobil
         const title = `${gedung ? 'Gedung ' + gedung : 'Engineering Building'} • ${lantai ? 'Lantai ' + lantai : 'All Levels'}`;
         setLocationTitle(title);
      } else {
         setIsMainDashboard(true); // Tampilkan Mobil
         setLocationTitle("Gedung Nano • Fakultas Teknologi Maju dan Multidisiplin");
      }

      const apiUrl = new URL('/api/v1/signage', window.location.origin); 
      if (lantai) apiUrl.searchParams.append('lantai', lantai);
      if (gedung) apiUrl.searchParams.append('gedung', gedung);

      const response = await fetch(apiUrl.toString());
      const data: ApiResponse = await response.json();

      setLectures(data.jadwal_kuliah_hari_ini);
      setEvents(data.kegiatan_mendatang);
      setMeetingsData(data.sidang_rapat);

      setIsOnline(true);
      setLastUpdate(
        new Date().toLocaleTimeString('id-ID', {
          timeZone: 'Asia/Jakarta',
          hour: '2-digit',
          minute: '2-digit',
          hour12: false,
        }) + ' WIB'
      );
      
    } catch (error) {
      console.error("Error fetching signage data:", error);
      setIsOnline(false);
    }
  };

  // ... useEffect dan event listener lainnya tetap sama ...
  useEffect(() => {
    fetchData(); 
    const interval = setInterval(() => {
      fetchData();
    }, 60000);
    return () => clearInterval(interval);
  }, []);

  const handleOnline = () => setIsOnline(true);
  const handleOffline = () => setIsOnline(false);

  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);

  return (
    <div className="relative h-screen w-full bg-navy-900 text-slate-900 overflow-hidden selection:bg-electric-500 selection:text-white font-sans">
      
      {/* Background (Tetap sama) */}
      <div 
        className="absolute inset-0 bg-cover bg-center z-0 scale-105"
        style={{
          backgroundImage: `url('https://picsum.photos/1920/1080?grayscale&blur=2')`,
          filter: 'blur(8px) brightness(0.4)',
        }}
      />
      <div className="absolute inset-0 bg-navy-900/70 z-0 mix-blend-multiply" />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_0%,_rgba(5,10,20,0.8)_100%)] z-0 pointer-events-none" />

      <div className="relative z-10 flex flex-col h-full px-6 pt-6 gap-3 max-w-[2400px] mx-auto">
        <div className="shrink-0">
            <Header customTitle={locationTitle} />
        </div>
        
        {/* Tambahkan padding-bottom (pb-24) agar konten paling bawah tidak ketutupan Widget Mobil */}
        <div className="grid grid-cols-12 gap-6 flex-1 min-h-0">
          <div className="col-span-3 h-full overflow-hidden">
            <LecturesPanel data={lectures} />
          </div>
          <div className="col-span-6 h-full overflow-hidden">
            <EventsPanel data={events} />
          </div>
          <div className="col-span-3 h-full flex flex-col gap-6 overflow-hidden">
            
            {/* Panel Sidang/Rapat (Ambil sisa space yang ada) */}
            <div className="flex-1 overflow-hidden min-h-0">
                <MeetingsPanel data={meetingsData} />
            </div>

            {/* Panel Mobil (Conditionally Rendered) */}
            {/* Shrink-0 agar tingginya menyesuaikan konten mobil, tidak memaksa full height */}
            {isMainDashboard && (
                 <div className="shrink-0 max-h-[45%] overflow-hidden">
                    <CarStatusWidget />
                 </div>
            )}
          </div>
        </div>
        
        {/* Footer (Tetap sama) */}
        <div className={`h-8 shrink-0 flex items-center px-4 text-xs font-mono tracking-widest uppercase rounded-lg border backdrop-blur-sm transition-colors duration-500 ${
            isOnline ? 'bg-navy-950/50 border-white/5 text-white/30' : 'bg-red-900/80 border-red-500 text-white'
        }`}>
            {/* ... isi footer ... */}
             <div className="flex items-center gap-6 flex-1">
              <div className="flex items-center gap-2">
                <span>STATUS SISTEM:</span>
                {isOnline ? (
                  <span className="text-emerald-400 font-bold flex items-center gap-1.5">
                    <span className="relative flex h-2 w-2">
                      <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    ONLINE
                  </span>
                ) : (
                  <span className="text-white font-bold flex items-center gap-2 animate-pulse">
                    <span className="h-2 w-2 rounded-full bg-white"></span>
                    OFFLINE / ERROR
                  </span>
                )}
              </div>
              <span className="whitespace-nowrap">• SYNC: {lastUpdate}</span>
            </div>
            <div className="flex-1 text-center text-white/60 font-semibold">FAKULTAS TEKNOLOGI MAJU DAN MULTIDISIPLIN</div>
            <div className="flex-1 flex justify-end whitespace-nowrap"><span>© {new Date().getFullYear()} • USI FTMM</span></div>
        </div>

      </div>
    </div>
  );
};

export default App;