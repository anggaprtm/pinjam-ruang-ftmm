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
      icon={<Users className="w-6 h-6 text-electric-400" />}
      className="h-full bg-navy-900/80 border-white/10"
    >
      <AutoScrollList
        data={data}
        threshold={4}
        renderItem={(meeting) => {
          const isOccupied = meeting.status === 'Occupied';
          const isFinished = meeting.status === 'Finished';
          const dateFlag   = meeting.date_flag ?? 'today';
          const dateLabel  = meeting.date_label ?? 'HARI INI';

          // ── Accent bar + bg berdasarkan status & tanggal ──
          const accentCls = isOccupied
            ? 'bg-red-500'
            : dateFlag === 'today'
              ? 'bg-electric-500'
              : 'bg-amber-500';

          const cardBgCls = isOccupied
            ? 'bg-red-900/20'
            : dateFlag === 'today'
              ? 'bg-white/5'
              : 'bg-amber-900/20';

          // ── Date badge ──
          const dateBadgeCls = dateFlag === 'today'
            ? 'bg-electric-500 text-navy-900'
            : 'bg-amber-500/20 text-amber-300';

          // ── Status badge ──
          const statusMap: Record<string, { label: string; cls: string }> = {
            Occupied: { label: '● Berlangsung', cls: 'bg-red-600/80 text-white' },
            Reserved: { label: 'Dijadwalkan',   cls: 'bg-white/8 text-white/40' },
            Finished: { label: '✔ Selesai',     cls: 'bg-emerald-900/40 text-emerald-400' },
          };
          const statusInfo = statusMap[meeting.status] ?? statusMap['Reserved'];

          return (
            <div className={`relative flex flex-col rounded-xl overflow-hidden transition-all duration-300 ${cardBgCls} ${isFinished ? 'opacity-50' : ''}`}>

              {/* Accent bar kiri */}
              <div className={`absolute left-0 top-0 bottom-0 w-1 ${accentCls}`} />

              <div className="pl-4 pr-3 pt-3 pb-3 flex flex-col gap-2">

                {/* ── Baris 1: Date badge + Status ── */}
                <div className="flex items-center justify-between gap-2">
                  <span className={`text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded ${dateBadgeCls}`}>
                    {dateLabel}
                  </span>
                  <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full ${statusInfo.cls}`}>
                    {statusInfo.label}
                  </span>
                </div>

                {/* ── Baris 2: Nama Ruang + Jenis ── */}
                <div className="flex items-center justify-between gap-2">
                  <span className={`font-bold text-sm line-clamp-1 ${isOccupied ? 'text-red-100' : 'text-white'}`}>
                    {meeting.room}
                  </span>
                  <span className={`shrink-0 text-[10px] font-bold px-2 py-0.5 rounded ${
                    isOccupied
                      ? 'bg-red-500/20 text-red-300'
                      : 'bg-electric-500/15 text-electric-400'
                  }`}>
                    {meeting.jenis}
                  </span>
                </div>

                {/* ── Judul Kegiatan ── */}
                <div className="text-sm text-gray-300 leading-snug line-clamp-2">
                  {meeting.title}
                </div>

                {/* ── Detail Sidang/Seminar ── */}
                {(meeting.jenis?.includes('Sidang') || meeting.jenis?.includes('Seminar')) && (
                  <div className="flex flex-col gap-1 pt-2 border-t border-white/5">
                    {meeting.pic && (
                      <div className="flex items-center gap-1.5 text-xs text-gray-300">
                        <Users className="w-3 h-3 text-electric-400 shrink-0" />
                        <span className="font-semibold truncate">{meeting.pic}</span>
                      </div>
                    )}
                    {meeting.pembimbing && (
                      <div className="flex items-start gap-1.5 text-[11px] text-gray-400">
                        <BookOpen className="w-3 h-3 mt-0.5 text-emerald-400 shrink-0" />
                        <span className="line-clamp-1">
                          <span className="text-emerald-400/80">Pembimbing: </span>
                          {meeting.pembimbing}
                        </span>
                      </div>
                    )}
                    {meeting.penguji && (
                      <div className="flex items-start gap-1.5 text-[11px] text-gray-400">
                        <UserCheck className="w-3 h-3 mt-0.5 text-rose-400 shrink-0" />
                        <span className="line-clamp-1">
                          <span className="text-rose-400/80">Penguji: </span>
                          {meeting.penguji}
                        </span>
                      </div>
                    )}
                  </div>
                )}

                {/* ── Jam ── */}
                <div className="flex items-center gap-1.5 text-xs font-mono text-electric-400/80 w-fit">
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