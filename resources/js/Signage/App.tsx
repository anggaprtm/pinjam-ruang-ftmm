import React from 'react';
import Header from './components/Header';
import LecturesPanel from './components/LecturesPanel';
import EventsPanel from './components/EventsPanel';
import MeetingsPanel from './components/MeetingsPanel';

const App: React.FC = () => {
  return (
    <div className="relative min-h-screen w-full bg-navy-900 text-white overflow-hidden selection:bg-electric-500 selection:text-white">
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
      
      {/* Vignette */}
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_transparent_0%,_rgba(5,10,20,0.8)_100%)] z-0 pointer-events-none" />

      {/* Main Container */}
      <div className="relative z-10 flex flex-col h-screen p-6 gap-6 max-w-[2400px] mx-auto">
        <Header />
        
        {/* Dashboard Grid - 3 Columns */}
        <div className="grid grid-cols-12 gap-6 flex-1 min-h-0">
          {/* Left Panel - Lectures */}
          <div className="col-span-3 h-full">
            <LecturesPanel />
          </div>
          
          {/* Center Panel - Events */}
          <div className="col-span-6 h-full">
            <EventsPanel />
          </div>
          
          {/* Right Panel - Meetings */}
          <div className="col-span-3 h-full">
            <MeetingsPanel />
          </div>
        </div>
        
        {/* Footer / Ticker (Optional aesthetic touch) */}
        <div className="h-8 flex items-center justify-between px-4 text-xs font-mono text-white/30 tracking-widest uppercase">
            <span>System Status: Online</span>
            <span>Display ID: E-LOBBY-01</span>
            <span>Network: Secure-Camp-5G</span>
        </div>
      </div>
    </div>
  );
};

export default App;