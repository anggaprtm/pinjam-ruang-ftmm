import React, { useEffect, useState } from 'react';
import { BookOpen, MapPin, Clock, ClipboardList } from 'lucide-react';
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';
import AutoScrollList from './AutoScrollList';

interface LecturesPanelProps {
    data: AgendaItem[];          // jadwal kuliah biasa
    ujianData?: AgendaItem[];    // jadwal UTS/UAS hari ini
}

const LecturesPanel: React.FC<LecturesPanelProps> = ({ data, ujianData = [] }) => {
    const [, setTick] = useState(0);

    useEffect(() => {
        const timer = setInterval(() => setTick(t => t + 1), 60000);
        return () => clearInterval(timer);
    }, []);

    // Switch mode: kalau ada ujian hari ini, tampilkan ujian
    const isExamMode   = ujianData.length > 0;
    const displayData  = isExamMode ? ujianData : data;
    const panelTitle   = isExamMode ? 'JADWAL UJIAN' : 'AGENDA PERKULIAHAN';
    const panelIcon    = isExamMode
        ? <ClipboardList className="w-6 h-6 text-amber-400" />
        : <BookOpen className="w-6 h-6 text-electric-400" />;

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
            title={panelTitle}
            icon={panelIcon}
            className="h-full bg-navy-900/80 border-white/10"
        >
            <AutoScrollList
                data={displayData}
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
                            ${isNow && isExamMode  ? 'bg-amber-500/10 shadow-[0_0_20px_rgba(245,158,11,0.15)]' : ''}
                            ${isNow && !isExamMode ? 'bg-electric-500/10 shadow-[0_0_20px_rgba(0,242,255,0.1)]' : ''}
                            ${isFinished           ? 'opacity-40' : ''}
                            ${!isNow && !isFinished ? 'bg-white/4 hover:bg-white/7' : ''}
                        `}>
                            {/* Accent bar kiri */}
                            <div className={`shrink-0 w-1 ${
                                isNow && isExamMode  ? 'bg-amber-400'    :
                                isNow && !isExamMode ? 'bg-electric-500' :
                                isFinished           ? 'bg-gray-700'     : 'bg-white/15'
                            }`} />

                            <div className="flex-1 px-3 py-2.5 min-w-0">
                                {/* Baris 1: Judul + live badge */}
                                <div className="flex items-center gap-2 mb-1.5 min-w-0">
                                    <h3 className={`flex-1 font-bold text-sm leading-tight truncate ${
                                        isNow ? 'text-white' : 'text-gray-300'
                                    }`}>
                                        {lecture.title}
                                    </h3>
                                    {isNow && (
                                        <span className="relative flex h-2 w-2 shrink-0">
                                            <span className={`animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${
                                                isExamMode ? 'bg-amber-400' : 'bg-emerald-400'
                                            }`} />
                                            <span className={`relative inline-flex h-2 w-2 rounded-full ${
                                                isExamMode ? 'bg-amber-500' : 'bg-emerald-500'
                                            }`} />
                                        </span>
                                    )}
                                </div>

                                {/* Baris 2: Badge + Ruang + Jam */}
                                <div className="flex items-center gap-3 text-[11px]">
                                    <span className={`shrink-0 text-[9px] font-extrabold px-1.5 py-0.5 rounded tracking-wider font-mono ${
                                        isNow && isExamMode  ? 'bg-amber-400 text-black'         :
                                        isNow && !isExamMode ? 'bg-electric-500 text-black'       :
                                        isExamMode           ? 'bg-amber-500/20 text-amber-400'   :
                                                               'bg-white/10 text-white/40'
                                    }`}>
                                        {(lecture as any).course_code || `LEC-${String(index + 1).padStart(2,'0')}`}
                                    </span>
                                    <div className={`flex items-center gap-1 ${
                                        isNow ? (isExamMode ? 'text-amber-400/80' : 'text-electric-400/80') : 'text-gray-500'
                                    }`}>
                                        <MapPin size={10} className="shrink-0" />
                                        <span className="truncate">{lecture.room}</span>
                                    </div>
                                    <div className={`flex items-center gap-1 font-mono shrink-0 ${
                                        isNow ? 'text-gray-300' : 'text-gray-600'
                                    }`}>
                                        <Clock size={10} className="shrink-0" />
                                        {lecture.time}
                                    </div>
                                </div>

                                {/* Baris 3: Pengawas — hanya mode ujian */}
                                {isExamMode && (lecture as any).pengawas && (
                                    <div className={`mt-1 text-[10px] flex items-center gap-1 ${
                                        isNow ? 'text-amber-300/70' : 'text-gray-600'
                                    }`}>
                                        <span className="font-semibold">Pengawas:</span>
                                        <span className="truncate">{(lecture as any).pengawas}</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                }}
            />
        </GlassPanel>
    );
};

export default LecturesPanel;