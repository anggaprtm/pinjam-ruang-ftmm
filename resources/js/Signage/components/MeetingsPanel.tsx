import React from 'react';
import { Users, Clock, BookOpen, UserCheck } from 'lucide-react';
import { Meeting } from '../types';
import GlassPanel from './GlassPanel';
import AutoScrollList from './AutoScrollList';

interface MeetingsPanelProps {
    data: Meeting[];
}

const MeetingsPanel: React.FC<MeetingsPanelProps> = ({ data }) => {
  return (
    <GlassPanel
      title="SIDANG & RAPAT"
      icon={<Users className="w-5 h-5" />}
      className="h-full"
    >
      <AutoScrollList
        data={data}
        threshold={4}
        renderItem={(meeting) => {
          const isOccupied = meeting.status === 'Occupied';
          const isFinished = meeting.status === 'Finished';
          const dateFlag   = meeting.date_flag ?? 'today';
          const dateLabel  = meeting.date_label ?? 'HARI INI';

          // ── Accent bar ──
          const accentCls = isOccupied
            ? 'bg-danger-600'
            : dateFlag === 'today'
              ? 'bg-maroon-600'
              : 'bg-warning-600';

          // ── Card background ──
          const cardBgCls = isOccupied
            ? 'bg-danger-50 border-danger-400/30'
            : dateFlag === 'today'
              ? 'bg-surface-1 border-surface-border'
              : 'bg-warning-50 border-warning-400/30';

          // ── Date badge ──
          const dateBadgeCls = dateFlag === 'today'
            ? 'bg-maroon-600 text-white'
            : 'bg-warning-50 text-warning-800 border border-warning-400/40';

          // ── Status badge ──
          const statusMap: Record<string, { label: string; cls: string }> = {
            Occupied: { label: '● Berlangsung', cls: 'bg-danger-50 text-danger-600 border border-danger-400/40' },
            Reserved: { label: 'Dijadwalkan',   cls: 'bg-surface-2 text-ink-secondary border border-surface-border' },
            Finished: { label: '✔ Selesai',     cls: 'bg-success-50 text-success-600 border border-success-400/40' },
          };
          const statusInfo = statusMap[meeting.status] ?? statusMap['Reserved'];

          return (
            <div className={`
              relative flex flex-col rounded-xl overflow-hidden border transition-all duration-300
              ${cardBgCls}
              ${isFinished ? 'opacity-50' : ''}
            `}>
              {/* Accent bar kiri */}
              <div className={`absolute left-0 top-0 bottom-0 w-1 ${accentCls}`} />

              <div className="pl-4 pr-3 pt-3 pb-3 flex flex-col gap-2">

                {/* Baris 1: Date badge + Status */}
                <div className="flex items-center justify-between gap-2">
                  <span className={`text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded ${dateBadgeCls}`}>
                    {dateLabel}
                  </span>
                  <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full ${statusInfo.cls}`}>
                    {isOccupied ? <span className="animate-pulse">{statusInfo.label}</span> : statusInfo.label}
                  </span>
                </div>

                {/* Baris 2: Nama Ruang + Jenis */}
                <div className="flex items-center justify-between gap-2">
                  <span className={`font-bold text-sm line-clamp-1 ${isOccupied ? 'text-danger-800' : 'text-ink-primary'}`}>
                    {meeting.room}
                  </span>
                  <span className={`shrink-0 text-[10px] font-bold px-2 py-0.5 rounded ${
                    isOccupied
                      ? 'bg-danger-50 text-danger-600 border border-danger-400/30'
                      : 'bg-maroon-50 text-maroon-600 border border-maroon-200'
                  }`}>
                    {meeting.jenis}
                  </span>
                </div>

                {/* Judul Kegiatan */}
                <div className="text-sm text-ink-secondary leading-snug line-clamp-2">
                  {meeting.title}
                </div>

                {/* Detail Sidang/Seminar */}
                {(meeting.jenis?.includes('Sidang') || meeting.jenis?.includes('Seminar')) && (
                  <div className="flex flex-col gap-1 pt-2 border-t border-surface-border">
                    {meeting.pic && (
                      <div className="flex items-center gap-1.5 text-xs text-ink-secondary">
                        <Users className="w-3 h-3 text-maroon-500 shrink-0" />
                        <span className="font-semibold truncate">{meeting.pic}</span>
                      </div>
                    )}
                    {meeting.pembimbing && (
                      <div className="flex items-start gap-1.5 text-[11px] text-ink-secondary">
                        <BookOpen className="w-3 h-3 mt-0.5 text-success-600 shrink-0" />
                        <span className="line-clamp-1">
                          <span className="text-success-600 font-semibold">Pembimbing: </span>
                          {meeting.pembimbing}
                        </span>
                      </div>
                    )}
                    {meeting.penguji && (
                      <div className="flex items-start gap-1.5 text-[11px] text-ink-secondary">
                        <UserCheck className="w-3 h-3 mt-0.5 text-danger-600 shrink-0" />
                        <span className="line-clamp-1">
                          <span className="text-danger-600 font-semibold">Penguji: </span>
                          {meeting.penguji}
                        </span>
                      </div>
                    )}
                  </div>
                )}

                {/* Jam */}
                <div className="flex items-center gap-1.5 text-xs font-mono text-maroon-500 w-fit">
                  <Clock className="w-3 h-3 shrink-0" />
                  {meeting.time}
                </div>

              </div>
            </div>
          );
        }}
      />
    </GlassPanel>
  );
};

export default MeetingsPanel;