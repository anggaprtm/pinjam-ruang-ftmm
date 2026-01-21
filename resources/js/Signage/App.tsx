import React, { useEffect, useState } from 'react';
import Header from './components/Header';
import LecturesPanel from './components/LecturesPanel';
import EventsPanel from './components/EventsPanel';
import MeetingsPanel from './components/MeetingsPanel';
import { AgendaItem, ApiResponse } from './types'; // Pastikan file types.ts sudah sesuai instruksi sebelumnya

const App: React.FC = () => {
  const [lectures, setLectures] = useState<AgendaItem[]>([]);
  const [events, setEvents] = useState<AgendaItem[]>([]);
  
  // State untuk Judul Lokasi (Default jika tidak ada filter)
  const [locationTitle, setLocationTitle] = useState("Engineering Building • Lobby A");

  const fetchData = async () => {
    try {
      // 1. Ambil Parameter dari URL Browser saat ini
      const params = new URLSearchParams(window.location.search);
      const lantai = params.get('lantai');
      const gedung = params.get('gedung');

      // 2. Susun Judul Dinamis
      if (lantai || gedung) {
         // Contoh hasil: "Gedung Nano • Lantai 7"
         const title = `${gedung ? 'Gedung ' + gedung : 'Engineering Building'} • ${lantai ? 'Lantai ' + lantai : 'All Levels'}`;
         setLocationTitle(title);
      }

      // 3. Panggil API dengan parameter tersebut
      // Hasil URL: /api/v1/signage?lantai=7&gedung=Nano
      const apiUrl = new URL('http://localhost:8000/api/v1/signage');
      if (lantai) apiUrl.searchParams.append('lantai', lantai);
      if (gedung) apiUrl.searchParams.append('gedung', gedung);

      const response = await fetch(apiUrl.toString());
      const data: ApiResponse = await response.json();

      setLectures(data.jadwal_kuliah_hari_ini);
      setEvents(data.kegiatan_mendatang);
      
    } catch (error) {
      console.error("Error fetching signage data:", error);
    }
  };
  // 3. useEffect untuk menjalankan fetch saat pertama kali & set interval
  useEffect(() => {
    fetchData(); // Load data pertama kali

    // Set interval untuk refresh otomatis tiap 60 detik (60000 ms)
    const interval = setInterval(() => {
      fetchData();
    }, 60000);

    // Bersihkan interval saat komponen dimatikan (cleanup)
    return () => clearInterval(interval);
  }, []);

  return (
    <div className="relative min-h-screen w-full bg-navy-900 text-slate-900 overflow-hidden selection:bg-electric-500 selection:text-white">
      {/* Background Image with Overlay */}
      <div 
        className="absolute inset-0 bg-cover bg-center z-0 scale-105"
        style={{
          backgroundImage: `url('https://picsum.photos/1920/1080?grayscale&blur=2')`,
          filter: 'blur(8px) brightness(0.4)',
        }}
      />
      {/* Dark tint overlay for better contrast */}
      <div className="absolute inset-0 bg-navy-900/70 z-0 mix-blend-multiply" />
      
      {/* Vignette Effect */}
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_0%,_rgba(5,10,20,0.8)_100%)] z-0 pointer-events-none" />

      {/* Main Container */}
      <div className="relative z-10 flex flex-col h-screen p-6 gap-6 max-w-[2400px] mx-auto">
        <Header customTitle={locationTitle} />
        
        {/* Dashboard Grid - 3 Columns */}
        <div className="grid grid-cols-12 gap-6 flex-1 min-h-0">
          
          {/* Left Panel - Lectures */}
          <div className="col-span-3 h-full">
            {/* Kirim data lectures ke komponen */}
            <LecturesPanel data={lectures} />
          </div>
          
          {/* Center Panel - Events */}
          <div className="col-span-6 h-full">
            {/* Kirim data events ke komponen */}
            <EventsPanel data={events} />
          </div>
          
          {/* Right Panel - Meetings */}
          <div className="col-span-3 h-full">
            {/* MeetingsPanel sementara biarkan kosong/statis dulu karena belum ada API khususnya */}
            <MeetingsPanel />
          </div>

        </div>
        
        {/* Footer / Ticker (Status Bar) */}
        <div className="h-8 flex items-center justify-between px-4 text-xs font-mono text-white/30 tracking-widest uppercase">
            <span>System Status: <span className="text-emerald-400 font-bold">Online</span></span>
            <span>Display ID: E-LOBBY-01</span>
            <span>Network: Secure-Camp-5G</span>
        </div>
      </div>
    </div>
  );
};

export default App;