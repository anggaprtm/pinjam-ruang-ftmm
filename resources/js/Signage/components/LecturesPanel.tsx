import React, { useEffect, useState } from 'react';
import { BookOpen, MapPin, Clock, ClipboardList } from 'lucide-react';
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';
import AutoScrollList from './AutoScrollList';

interface LecturesPanelProps {
    data: AgendaItem[];
    ujianData?: AgendaItem[];
}

const LecturesPanel: React.FC<LecturesPanelProps> = ({ data, ujianData = [] }) => {
    const [, setTick] = useState(0);

    const urlParams = new URLSearchParams(window.location.search);
    const isSpesifikLantai = urlParams.has('lantai');

    useEffect(() => {
        const timer = setInterval(() => setTick(t => t + 1), 60000);
        return () => clearInterval(timer);
    }, []);

    const isExamMode  = ujianData.length > 0;
    const displayData = isExamMode ? ujianData : data;
    const panelTitle  = isExamMode ? 'JADWAL UJIAN' : 'AGENDA PERKULIAHAN';
    const panelIcon   = isExamMode
        ? <ClipboardList className="w-5 h-5" />
        : <BookOpen className="w-5 h-5" />;

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
            className="h-full"
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

                    // ── Warna aksen bar kiri ──
                    const accentCls = isNow && isExamMode  ? 'bg-warning-600'
                                    : isNow && !isExamMode ? 'bg-maroon-600'
                                    : isFinished           ? 'bg-surface-3'
                                    :                        'bg-surface-border';

                    // ── Card background ──
                    const cardBgCls = isNow && isExamMode  ? 'bg-warning-50 border-warning-400/40'
                                    : isNow && !isExamMode ? 'bg-maroon-50 border-maroon-200'
                                    : isFinished           ? 'bg-surface-1 border-surface-border opacity-50'
                                    :                        'bg-surface-1 border-surface-border hover:bg-surface-2';

                    return (
                        <div className={`
                            relative flex items-stretch rounded-lg overflow-hidden
                            border transition-all duration-300 ${cardBgCls}
                        `}>
                            {/* Accent bar kiri */}
                            <div className={`shrink-0 w-1 ${accentCls}`} />

                            <div className="flex-1 px-3 py-2.5 min-w-0">
                                {/* Baris 1: Judul + live badge */}
                                <div className="flex items-center gap-2 mb-1.5 min-w-0">
                                    <h3 className={`flex-1 font-bold text-sm leading-tight truncate ${
                                        isFinished ? 'text-ink-muted line-through' : 'text-ink-primary'
                                    }`}>
                                        {lecture.title}
                                    </h3>
                                    {isNow && (
                                        <span className="relative flex h-2 w-2 shrink-0">
                                            <span className={`animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${
                                                isExamMode ? 'bg-warning-400' : 'bg-maroon-400'
                                            }`} />
                                            <span className={`relative inline-flex h-2 w-2 rounded-full ${
                                                isExamMode ? 'bg-warning-600' : 'bg-maroon-600'
                                            }`} />
                                        </span>
                                    )}
                                </div>

                                {/* Baris 2: Badge kode + Ruang + Jam */}
                                <div className="flex items-center gap-2.5 text-[11px] flex-wrap">
                                    {/* Kode matkul */}
                                    <span className={`shrink-0 text-[9px] font-extrabold px-1.5 py-0.5 rounded tracking-wider font-mono ${
                                        isNow && isExamMode  ? 'bg-warning-400 text-warning-800'
                                        : isNow && !isExamMode ? 'bg-maroon-600 text-white'
                                        : isExamMode           ? 'bg-warning-50 text-warning-600 border border-warning-400/40'
                                        :                        'bg-surface-2 text-ink-secondary border border-surface-border'
                                    }`}>
                                        {(lecture as any).course_code || `LEC-${String(index + 1).padStart(2,'0')}`}
                                    </span>

                                    {/* Ruang */}
                                    <div className={`flex items-center gap-1 ${
                                        isNow ? (isExamMode ? 'text-warning-600' : 'text-maroon-600') : 'text-ink-secondary'
                                    }`}>
                                        <MapPin size={10} className="shrink-0" />
                                        <span className="truncate">{lecture.room}</span>
                                    </div>

                                    {/* Jam */}
                                    <div className={`flex items-center gap-1 font-mono shrink-0 ${
                                        isNow ? 'text-ink-primary' : 'text-ink-muted'
                                    }`}>
                                        <Clock size={10} className="shrink-0" />
                                        {lecture.time}
                                    </div>
                                </div>

                                {/* Baris 3: Pengawas (hanya ujian) */}
                                {isExamMode && !isSpesifikLantai && (lecture as any).pengawas && (
                                    <div className={`mt-1 text-[10px] flex items-center gap-1 ${
                                        isNow ? 'text-warning-600' : 'text-ink-muted'
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