import React, { useEffect, useState } from 'react';
import Header from './components/Header';
import LecturesPanel from './components/LecturesPanel';
import EventsPanel from './components/EventsPanel';
import MeetingsPanel from './components/MeetingsPanel';
import { AgendaItem, ApiResponse, Meeting } from './types'; // Tambahkan Meeting di import

const App: React.FC = () => {
  const [lectures, setLectures] = useState<AgendaItem[]>([]);
  const [events, setEvents] = useState<AgendaItem[]>([]);
  const [meetingsData, setMeetingsData] = useState<Meeting[]>([]);
  const [isOnline, setIsOnline] = useState<boolean>(navigator.onLine);
  const [lastUpdate, setLastUpdate] = useState<string>(new Date().toLocaleTimeString());
  const [locationTitle, setLocationTitle] = useState("Gedung Nano • Fakultas Teknologi Maju dan Multidisiplin");

  const fetchData = async () => {
    try {
      const params = new URLSearchParams(window.location.search);
      const lantai = params.get('lantai');
      const gedung = params.get('gedung');

      if (lantai || gedung) {
         const title = `${gedung ? 'Gedung ' + gedung : 'Engineering Building'} • ${lantai ? 'Lantai ' + lantai : 'All Levels'}`;
         setLocationTitle(title);
      }

      // Pastikan URL API sesuai dengan environment kamu
      const apiUrl = new URL('/api/v1/signage', window.location.origin); 
      if (lantai) apiUrl.searchParams.append('lantai', lantai);
      if (gedung) apiUrl.searchParams.append('gedung', gedung);

      const response = await fetch(apiUrl.toString());
      const data: ApiResponse = await response.json();

      setLectures(data.jadwal_kuliah_hari_ini);
      setEvents(data.kegiatan_mendatang);
      setMeetingsData(data.sidang_rapat);

      // JIKA BERHASIL FETCH:
      setIsOnline(true); // Set status Online
      setLastUpdate(
        new Date().toLocaleTimeString('id-ID', {
          timeZone: 'Asia/Jakarta',
          hour: '2-digit',
          minute: '2-digit',
          hour12: false,
        }) + ' WIB'
      ); // Update jam terakhir sync
      
    } catch (error) {
      console.error("Error fetching signage data:", error);
      setIsOnline(false);
    }
  };

  useEffect(() => {
    fetchData(); 
    const interval = setInterval(() => {
      fetchData();
    }, 60000);
    return () => clearInterval(interval);
  }, []);

  // 2. EVENT LISTENER BROWSER (Buat deteksi kabel dicabut real-time)
  const handleOnline = () => setIsOnline(true);
  const handleOffline = () => setIsOnline(false);

  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);

  return (
    // FIX 1: Ganti 'min-h-screen' jadi 'h-screen' & Tambah 'overflow-hidden'
    // Ini mengunci layar agar tidak bisa discroll oleh browser (Fix Footer Kepotong)
    <div className="relative h-screen w-full bg-navy-900 text-slate-900 overflow-hidden selection:bg-electric-500 selection:text-white font-sans">
      
      {/* Background Section (Biarkan sama) */}
      <div 
        className="absolute inset-0 bg-cover bg-center z-0 scale-105"
        style={{
          backgroundImage: `url('https://picsum.photos/1920/1080?grayscale&blur=2')`,
          filter: 'blur(8px) brightness(0.4)',
        }}
      />
      <div className="absolute inset-0 bg-navy-900/70 z-0 mix-blend-multiply" />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_0%,_rgba(5,10,20,0.8)_100%)] z-0 pointer-events-none" />

      {/* Main Container */}
      {/* FIX 2: Ganti 'h-screen' jadi 'h-full'. Karena parent sudah h-screen, anak cukup h-full. */}
      <div className="relative z-10 flex flex-col h-full p-6 gap-6 max-w-[2400px] mx-auto">
        
        {/* Header: shrink-0 agar tidak gepeng */}
        <div className="shrink-0">
            <Header customTitle={locationTitle} />
        </div>
        
        {/* Dashboard Grid - 3 Columns */}
        {/* flex-1 dan min-h-0 WAJIB ADA agar scroll bar bekerja di dalam sini, bukan di window */}
        <div className="grid grid-cols-12 gap-6 flex-1 min-h-0">
          
          {/* Left Panel - Lectures */}
          <div className="col-span-3 h-full overflow-hidden">
            <LecturesPanel data={lectures} />
          </div>
          
          {/* Center Panel - Events */}
          <div className="col-span-6 h-full overflow-hidden">
            <EventsPanel data={events} />
          </div>
          
          {/* Right Panel - Meetings */}
          <div className="col-span-3 h-full overflow-hidden">
            <MeetingsPanel data={meetingsData} />
          </div>

        </div>
        
        {/* Footer / Ticker (Status Bar) */}
        {/* FOOTER / TICKER (STATUS BAR) YANG SUDAH REAL */}
        <div
          className={`h-8 shrink-0 flex items-center px-4 text-xs font-mono tracking-widest uppercase rounded-lg border backdrop-blur-sm transition-colors duration-500 ${
            isOnline
              ? 'bg-navy-950/50 border-white/5 text-white/30'
              : 'bg-red-900/80 border-red-500 text-white'
          }`}
        >
          {/* KIRI: STATUS + LAST SYNC */}
          <div className="flex items-center gap-6 flex-1">
              {/* STATUS KONEKSI */}
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

              {/* JAM TERAKHIR SYNC */}
              <span className="whitespace-nowrap">
                • SYNC: {lastUpdate}
              </span>
            </div>

            {/* TENGAH: NAMA FAKULTAS */}
            <div className="flex-1 text-center text-white/60 font-semibold">
              FAKULTAS TEKNOLOGI MAJU DAN MULTIDISIPLIN
            </div>

            {/* KANAN: COPYRIGHT */}
            <div className="flex-1 flex justify-end whitespace-nowrap">
              <span>© {new Date().getFullYear()} • USI FTMM</span>
            </div>
          </div>


      </div>
    </div>
  );
};

export default App;