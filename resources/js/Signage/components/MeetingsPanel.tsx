import React from 'react';
import { Users, Clock, BookOpen, UserCheck } from 'lucide-react';
import { Meeting } from '../types';
import GlassPanel from './GlassPanel';
import AutoScrollList from './AutoScrollList'; // Import komponen baru

interface MeetingsPanelProps {
    data: Meeting[];
}

const MeetingsPanel: React.FC<MeetingsPanelProps> = ({ data }) => {
  return (
    <GlassPanel title="SIDANG & RAPAT" icon={<Users className="w-6 h-6 text-electric-400" />} className="h-full bg-navy-900/80 border-white/10">
      
      {/* PANGGIL AUTO SCROLL DISINI */}
      <AutoScrollList 
        data={data}
        threshold={4} // Kalau item > 4, dia bakal scroll
        renderItem={(meeting) => (
            // Masukkan tampilan KARTU SIDANG (Copy paste yg tadi) disini
            <div className={`
                flex flex-col p-4 rounded-xl border transition-all duration-300
                ${meeting.status === 'Occupied' 
                    ? 'bg-red-900/20 border-red-500/50 shadow-[0_0_15px_rgba(239,68,68,0.2)]' 
                    : 'bg-white/5 border-white/10'
                }
            `}>
                {/* HEADER */}
                <div className="flex justify-between items-start mb-1">
                    <span className="text-2xl font-bold text-white font-mono tracking-tight">{meeting.room}</span>
                    <span className={`px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border
                        ${meeting.status === 'Occupied'
                            ? 'bg-red-600 text-white border-red-400 animate-pulse'
                            : 'bg-electric-500/20 text-electric-400 border-electric-500/50'
                        }
                    `}>
                        {meeting.jenis}
                    </span>
                </div>
                
                <div className="text-base font-bold text-gray-200 leading-snug mb-3 line-clamp-2">{meeting.title}</div>

                {(meeting.jenis.includes('Sidang') || meeting.jenis.includes('Seminar')) && (
                    <div className="mb-3 pt-3 border-t border-white/10 flex flex-col gap-1.5">
                        <div className="flex items-start gap-2 text-xs text-gray-300">
                            <Users className="w-3.5 h-3.5 mt-0.5 text-electric-400" />
                            <span className="font-semibold text-white">{meeting.pic}</span>
                        </div>
                        {meeting.pembimbing && (
                            <div className="flex items-start gap-2 text-[11px] text-gray-400">
                                <BookOpen className="w-3.5 h-3.5 mt-0.5 text-emerald-400" />
                                <span><span className="text-emerald-400/80">Pembimbing:</span> {meeting.pembimbing}</span>
                            </div>
                        )}
                        {meeting.penguji && (
                             <div className="flex items-start gap-2 text-[11px] text-gray-400">
                                <UserCheck className="w-3.5 h-3.5 mt-0.5 text-rose-400" />
                                <span><span className="text-rose-400/80">Penguji:</span> {meeting.penguji}</span>
                            </div>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-2 text-xs font-mono bg-black/30 w-fit px-2 py-1 rounded text-electric-400">
                    <Clock className="w-3 h-3" />
                    {meeting.time}
                </div>
            </div>
        )}
      />
    </GlassPanel>
  );
};

export default MeetingsPanel;