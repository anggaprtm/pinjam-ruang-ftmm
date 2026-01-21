import React from 'react';

interface GlassPanelProps {
  children: React.ReactNode;
  className?: string;
  title?: string;
  icon?: React.ReactNode;
}

const GlassPanel: React.FC<GlassPanelProps> = ({ children, className = '', title, icon }) => {
  return (
    <div className={`relative overflow-hidden rounded-3xl border border-white/10 bg-navy-900/40 backdrop-blur-xl shadow-2xl flex flex-col ${className}`}>
      {/* Glossy overlay gradient */}
      <div className="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent pointer-events-none" />
      
      {(title || icon) && (
        <div className="relative z-10 flex items-center gap-3 p-6 pb-2 border-b border-white/5">
          {icon && <div className="text-electric-400">{icon}</div>}
          <h2 className="text-xl font-semibold tracking-wide text-white/90 uppercase">{title}</h2>
        </div>
      )}
      
      <div className="relative z-10 flex-1 p-6 overflow-y-auto">
        {children}
      </div>
    </div>
  );
};

export default GlassPanel;