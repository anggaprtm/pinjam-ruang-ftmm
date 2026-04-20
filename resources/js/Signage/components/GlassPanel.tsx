import React from 'react';

interface GlassPanelProps {
  children: React.ReactNode;
  className?: string;
  title?: string;
  icon?: React.ReactNode;
  right?: React.ReactNode;
}

/**
 * LightPanel — replaces the old dark GlassPanel.
 * Design: white card surface, maroon-brand accent on header,
 * thin surface-border, soft shadow. Matches the FTMM admin blade style.
 */
const GlassPanel: React.FC<GlassPanelProps> = ({ children, className = '', title, icon, right }) => {
  return (
    <div
      className={`
        relative overflow-hidden rounded-2xl
        bg-surface-0 border border-surface-border
        shadow-card flex flex-col
        ${className}
      `}
    >
      {(title || icon) && (
        <div className="flex items-center gap-3 px-5 py-3.5 border-b border-surface-border bg-surface-0 shrink-0">
          {icon && (
            <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-maroon-50 text-maroon-600 shrink-0">
              {icon}
            </div>
          )}
          <h2 className="text-[13px] font-extrabold tracking-widest text-ink-primary uppercase flex-1">
            {title}
          </h2>
          {right && <div className="ml-auto">{right}</div>}
        </div>
      )}

      <div className="flex-1 p-4 overflow-y-auto scrollbar-hide min-h-0">
        {children}
      </div>
    </div>
  );
};

export default GlassPanel;