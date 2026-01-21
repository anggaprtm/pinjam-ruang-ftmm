import React, { useEffect, useState } from 'react';
import { BookOpen, User, MapPin } from 'lucide-react';
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';
import AutoScrollList from './AutoScrollList'; // Pastikan path import benar

interface LecturesPanelProps {
    data: AgendaItem[];
}

const LecturesPanel: React.FC<LecturesPanelProps> = ({ data }) => {
    // State tick biar jam/status "Now" selalu update real-time
    const [, setTick] = useState(0);

    useEffect(() => {
        const timer = setInterval(() => setTick(t => t + 1), 60000);
        return () => clearInterval(timer);
    }, []);

    // Helper: Hitung status (Now/Upcoming/Finished)
    const getStatus = (timeString: string) => {
        try {
            const now = new Date();
            const [startStr, endStr] = timeString.split(' - ');
            const [startH, startM] = startStr.split(':').map(Number);
            const [endH, endM] = endStr.split(':').map(Number);

            const startDate = new Date(); startDate.setHours(startH, startM, 0);
            const endDate = new Date(); endDate.setHours(endH, endM, 0);

            if (now >= startDate && now <= endDate) return 'Now';
            if (now < startDate) return 'Upcoming';
            return 'Finished';
        } catch (e) {
            return 'Upcoming';
        }
    };

    return (
        <GlassPanel title="AGENDA PERKULIAHAN" icon={<BookOpen className="w-6 h-6 text-electric-400" />} className="h-full bg-navy-900/80 border-white/10">
            
            <AutoScrollList 
                data={data}
                threshold={3} // Kalau lebih dari 5 matkul, dia scroll
                renderItem={(lecture, index) => {
                    const status = getStatus(lecture.time);
                    
                    // Opsional: Skip yang Finished biar list bersih
                    // if (status === 'Finished') return null;

                    return (
                        <div 
                            className={`relative p-4 rounded-xl border transition-all duration-500 group ${
                                status === 'Now' 
                                    ? 'bg-electric-500/10 border-electric-500/50 shadow-[0_0_30px_rgba(0,242,255,0.15)] scale-[1.02]' 
                                    : 'bg-white/5 border-white/5 hover:bg-white/10 opacity-80 hover:opacity-100'
                            }`}
                        >
                            {/* Indikator Status "Now" (Pulsing Dot) */}
                            {status === 'Now' && (
                                <div className="absolute top-4 right-4 flex items-center gap-1.5 z-10">
                                    <span className="relative flex h-2.5 w-2.5">
                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </span>
                                    <span className="text-[10px] font-bold text-emerald-400 uppercase tracking-widest">LIVE</span>
                                </div>
                            )}

                            {/* Header Kartu: Kode Matkul */}
                            <div className="flex justify-between items-start mb-2">
                                <span className={`text-[10px] font-bold px-2 py-0.5 rounded tracking-wider ${
                                    status === 'Now' ? 'bg-electric-500 text-black' : 'bg-white/10 text-white/50'
                                }`}>
                                    {(lecture as any).course_code || `LEC-0${index + 1}`}
                                </span>
                            </div>
                            
                            {/* Judul Mata Kuliah */}
                            <h3 className={`text-lg font-bold mb-3 leading-tight ${status === 'Now' ? 'text-white' : 'text-gray-300'}`}>
                                {lecture.title}
                            </h3>
                            
                            {/* Detail Info */}
                            <div className="flex flex-col gap-2 mt-2 border-t border-white/5 pt-3">
                                <div className="flex items-center gap-2 text-sm text-gray-300 group-hover:text-white transition-colors">
                                    <User className={`w-3.5 h-3.5 ${status === 'Now' ? 'text-electric-400' : 'text-gray-500'}`} />
                                    <span>{lecture.pic !== '-' ? lecture.pic : '-'}</span>
                                </div>
                                <div className="flex items-center gap-2 text-sm text-gray-300 group-hover:text-white transition-colors">
                                    <MapPin className={`w-3.5 h-3.5 ${status === 'Now' ? 'text-electric-400' : 'text-gray-500'}`} />
                                    <span>{lecture.room}</span>
                                </div>
                                <div className={`text-xs mt-1 font-mono flex items-center gap-2 ${status === 'Now' ? 'text-gray-300' : 'text-gray-500'}`}>
                                    <div className={`w-1 h-1 rounded-full ${status === 'Now' ? 'bg-electric-500' : 'bg-gray-600'}`}></div>
                                    {lecture.time}
                                </div>
                            </div>
                        </div>
                    );
                }}
            />
        </GlassPanel>
    );
};

export default LecturesPanel;