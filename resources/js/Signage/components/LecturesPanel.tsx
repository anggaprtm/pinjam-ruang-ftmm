import React, { useEffect, useState } from 'react';
import { BookOpen, User, MapPin } from 'lucide-react'; // Pastikan sudah npm install lucide-react
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';

interface LecturesPanelProps {
    data: AgendaItem[];
}

const LecturesPanel: React.FC<LecturesPanelProps> = ({ data }) => {
    // State untuk memicu re-render perhitungan waktu setiap menit
    const [, setTick] = useState(0);

    useEffect(() => {
        const timer = setInterval(() => setTick(t => t + 1), 60000);
        return () => clearInterval(timer);
    }, []);

    // Helper: Hitung status (Now/Upcoming/Finished) berdasarkan string jam "13:00 - 15:00"
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
            return 'Upcoming'; // Default fallback
        }
    };

    return (
        <GlassPanel title="AGENDA PERKULIAHAN" icon={<BookOpen className="w-6 h-6" />} className="h-full">
            <div className="space-y-4">
                {data.length === 0 ? (
                    <div className="text-white/30 text-center mt-20 font-mono text-sm animate-pulse">
                        -- NO DATA STREAM --
                    </div>
                ) : (
                    data.map((lecture, index) => {
                        const status = getStatus(lecture.time);

                        // Jangan tampilkan yang sudah selesai (Opsional, kalau mau bersih)
                        // if (status === 'Finished') return null; 

                        return (
                            <div 
                                key={index} 
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
                                        {/* Gunakan data course_code dari controller, atau default */}
                                        {(lecture as any).course_code || `LEC-0${index + 1}`}
                                    </span>
                                </div>
                                
                                {/* Judul Mata Kuliah */}
                                <h3 className={`text-lg font-bold mb-3 leading-tight ${status === 'Now' ? 'text-white' : 'text-gray-300'}`}>
                                    {lecture.title}
                                </h3>
                                
                                {/* Detail Info (Dosen, Ruang, Waktu) */}
                                <div className="flex flex-col gap-2 mt-2 border-t border-white/5 pt-3">
                                    {/* Dosen */}
                                    <div className="flex items-center gap-2 text-sm text-gray-300 group-hover:text-white transition-colors">
                                        <User className={`w-3.5 h-3.5 ${status === 'Now' ? 'text-electric-400' : 'text-gray-500'}`} />
                                        <span>{lecture.pic !== '-' ? lecture.pic : 'TBA'}</span>
                                    </div>
                                    
                                    {/* Ruangan */}
                                    <div className="flex items-center gap-2 text-sm text-gray-300 group-hover:text-white transition-colors">
                                        <MapPin className={`w-3.5 h-3.5 ${status === 'Now' ? 'text-electric-400' : 'text-gray-500'}`} />
                                        <span>{lecture.room}</span>
                                    </div>

                                    {/* Waktu */}
                                    <div className={`text-xs mt-1 font-mono flex items-center gap-2 ${status === 'Now' ? 'text-electric-300' : 'text-gray-500'}`}>
                                        <div className={`w-1 h-1 rounded-full ${status === 'Now' ? 'bg-electric-500' : 'bg-gray-600'}`}></div>
                                        {lecture.time}
                                    </div>
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </GlassPanel>
    );
};

export default LecturesPanel;