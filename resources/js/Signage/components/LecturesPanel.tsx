import React, { useEffect, useState } from 'react';
import { BookOpen, MapPin, Clock } from 'lucide-react';
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';
import AutoScrollList from './AutoScrollList';

interface LecturesPanelProps {
    data: AgendaItem[];
}

const LecturesPanel: React.FC<LecturesPanelProps> = ({ data }) => {
    const [, setTick] = useState(0);

    useEffect(() => {
        const timer = setInterval(() => setTick(t => t + 1), 60000);
        return () => clearInterval(timer);
    }, []);

    const getStatus = (timeString: string) => {
        try {
            const now = new Date();
            const [startStr, endStr] = timeString.split(' - ');
            const [startH, startM]  = startStr.split(':').map(Number);
            const [endH, endM]      = endStr.split(':').map(Number);
            const start = new Date(); start.setHours(startH, startM, 0);
            const end   = new Date(); end.setHours(endH, endM, 0);
            if (now >= start && now <= end) return 'Now';
            if (now < start) return 'Upcoming';
            return 'Finished';
        } catch { return 'Upcoming'; }
    };

    return (
        <GlassPanel
            title="AGENDA PERKULIAHAN"
            icon={<BookOpen className="w-6 h-6 text-electric-400" />}
            className="h-full bg-navy-900/80 border-white/10"
        >
            <AutoScrollList
                data={data}
                threshold={6}
                speedPerItem={4}
                gap="gap-1.5"
                renderItem={(lecture, index) => {
                    const status     = getStatus(lecture.time);
                    const isNow      = status === 'Now';
                    const isFinished = status === 'Finished';

                    return (
                        <div className={`
                            relative flex items-stretch rounded-lg overflow-hidden transition-all duration-500
                            ${isNow      ? 'bg-electric-500/10 shadow-[0_0_20px_rgba(0,242,255,0.1)]' : ''}
                            ${isFinished ? 'opacity-40' : ''}
                            ${!isNow && !isFinished ? 'bg-white/4 hover:bg-white/7' : ''}
                        `}>
                            {/* Accent bar kiri */}
                            <div className={`shrink-0 w-1 ${
                                isNow      ? 'bg-electric-500' :
                                isFinished ? 'bg-gray-700'     : 'bg-white/15'
                            }`} />

                            <div className="flex-1 px-3 py-2.5 min-w-0">
                                {/* Baris 1: Kode + Nama matkul */}
                                <div className="flex items-center gap-2 mb-1.5 min-w-0">
                                    <h3 className={`flex-1 font-bold text-sm leading-tight truncate ${
                                        isNow ? 'text-white' : 'text-gray-300'
                                    }`}>
                                        {lecture.title}
                                    </h3>

                                    {/* Status badge */}
                                    {isNow && (
                                        <div className="shrink-0 flex items-center gap-1">
                                            <span className="relative flex h-2 w-2">
                                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                                                <span className="relative inline-flex h-2 w-2 rounded-full bg-emerald-500" />
                                            </span>
                                        </div>
                                    )}
                                </div>

                                {/* Baris 2: Ruang + Jam */}
                                <div className="flex items-center gap-3 text-[11px]">
                                    <span className={`shrink-0 text-[9px] font-extrabold px-1.5 py-0.5 rounded tracking-wider font-mono ${
                                        isNow ? 'bg-electric-500 text-black' : 'bg-white/10 text-white/40'
                                    }`}>
                                        {(lecture as any).course_code || `LEC-${String(index + 1).padStart(2,'0')}`}
                                    </span>
                                    <div className={`flex items-center gap-1 ${isNow ? 'text-electric-400/80' : 'text-gray-500'}`}>
                                        <MapPin size={10} className="shrink-0" />
                                        <span className="truncate">{lecture.room}</span>
                                    </div>
                                    <div className={`flex items-center gap-1 font-mono shrink-0 ${isNow ? 'text-gray-300' : 'text-gray-600'}`}>
                                        <Clock size={10} className="shrink-0" />
                                        {lecture.time}
                                    </div>
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