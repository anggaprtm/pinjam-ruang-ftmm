import React from 'react';
import { Users, Clock, GraduationCap } from 'lucide-react'; // Tambah icon GraduationCap buat variasi
import { Meeting } from '../types';
import GlassPanel from './GlassPanel';

// MOCK DATA (Nanti ini diganti API Laravel)
const MOCK_MEETINGS: Meeting[] = [
  { id: '1', room: '304', title: 'Rapat Dept. Informatika', time: '09:00 - 11:00', status: 'Occupied' },
  { id: '2', room: '401', title: 'Sidang Skripsi: Budi Santoso', time: '13:00 - 14:30', status: 'Reserved', student: 'Budi S.' },
  { id: '3', room: '202', title: 'Study Group AI', time: '14:00 - 15:30', status: 'Available' },
  { id: '4', room: '105', title: 'Rapat Senat Fakultas', time: '15:30 - 17:00', status: 'Reserved' },
  { id: '5', room: '308', title: 'Maintenance Lab Jarkom', time: 'All Day', status: 'Occupied' },
  { id: '6', room: '205', title: 'Seminar KP: Siti Aminah', time: '10:00 - 11:00', status: 'Reserved' },
];

const MeetingsPanel: React.FC = () => {
  return (
    <GlassPanel title="SIDANG & RAPAT" icon={<Users className="w-6 h-6" />} className="h-full">
      <div className="flex flex-col h-full">
        
        {/* LIST RAPAT / SIDANG */}
        <div className="grid gap-3 flex-1 overflow-y-auto pr-1 scrollbar-hide">
            {MOCK_MEETINGS.map((meeting) => (
                <div key={meeting.id} className="flex flex-col p-4 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                    <div className="flex justify-between items-center mb-2">
                        <span className="text-xl font-bold text-white font-mono">{meeting.room}</span>
                        <span className={`px-2 py-1 rounded-md text-[10px] uppercase font-bold tracking-wider ${
                            meeting.status === 'Occupied' ? 'bg-red-500/20 text-red-400 border border-red-500/30' :
                            meeting.status === 'Available' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' :
                            'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30'
                        }`}>
                            {meeting.status}
                        </span>
                    </div>
                    
                    {/* Judul Kegiatan */}
                    <div className="text-sm font-medium text-gray-200 truncate flex items-center gap-2">
                        {meeting.title.includes('Sidang') || meeting.title.includes('Seminar') ? (
                            <GraduationCap className="w-3 h-3 text-electric-400" />
                        ) : null}
                        {meeting.title}
                    </div>

                    {/* Waktu */}
                     <div className="flex items-center gap-2 mt-2 text-xs text-gray-500 font-mono">
                        <Clock className="w-3 h-3" />
                        {meeting.time}
                     </div>
                </div>
            ))}
        </div>

        {/* QUICK STATS (Hardcoded dulu sesuai request) */}
        <div className="mt-4 p-4 rounded-xl bg-gradient-to-r from-electric-500/10 to-transparent border border-electric-500/20 shrink-0">
            <h4 className="text-[10px] font-bold text-electric-400 uppercase tracking-widest mb-2">Room Availability</h4>
            <div className="flex justify-between items-end">
                <div>
                      <span className="text-3xl font-bold text-white leading-none">4</span>
                      <span className="text-xs text-gray-400 ml-1">Open</span>
                </div>
                 <div className="h-1.5 flex-1 mx-4 bg-white/10 rounded-full overflow-hidden self-center">
                    <div className="h-full bg-electric-500 w-[35%] shadow-[0_0_10px_rgba(0,242,255,0.5)]"></div>
                 </div>
                 <div className="text-right">
                    <span className="text-lg font-bold text-white/60 leading-none">12</span>
                     <span className="text-xs text-gray-400 ml-1">Total</span>
                 </div>
            </div>
        </div>

      </div>
    </GlassPanel>
  );
};

export default MeetingsPanel;