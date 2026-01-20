import React, { useState, useEffect } from 'react';
import { ShieldCheck, Clock } from 'lucide-react';

const Header: React.FC = () => {
  const [date, setDate] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => setDate(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);

  // Format Time: HH:MM
  const timeString = date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });

  // Format Date: Weekday, Month Day, Year
  const dateString = date.toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  });

  return (
    <header className="relative z-50 flex items-center justify-between px-8 py-5 mb-6 rounded-2xl bg-navy-900/60 backdrop-blur-md border border-white/10 shadow-lg">
      {/* Left: Logo */}
      <div className="flex items-center gap-4">
        <div className="w-12 h-12 flex items-center justify-center bg-electric-500 rounded-xl shadow-[0_0_15px_rgba(34,211,238,0.4)]">
           <ShieldCheck className="text-white w-7 h-7" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-white tracking-tight leading-none">TECH UNIVERSITY</h1>
          <p className="text-xs text-electric-400 font-medium tracking-widest uppercase mt-1">Excellence in Innovation</p>
        </div>
      </div>

      {/* Center: Location */}
      <div className="absolute left-1/2 transform -translate-x-1/2">
        <div className="px-6 py-2 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm">
          <span className="text-white/80 font-medium tracking-wide">Engineering Building &bull; Lobby A</span>
        </div>
      </div>

      {/* Right: Clock */}
      <div className="text-right">
        <div className="text-4xl font-bold text-white tabular-nums leading-none tracking-tight">
          {timeString}
        </div>
        <div className="text-sm text-gray-400 font-medium mt-1 flex items-center justify-end gap-2">
          <Clock className="w-3 h-3" />
          {dateString}
        </div>
      </div>
    </header>
  );
};

export default Header;