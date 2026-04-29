import React, { useEffect, useRef, useState } from 'react';
import { Calendar, Clock, MapPin, AlignLeft, Sun } from 'lucide-react';

// ─── Types ───────────────────────────────────────────────────────────────────
interface DekanEvent {
    id: string;
    title: string;
    description?: string;
    location?: string;
    start_time: string | null;
    end_time: string | null;
    is_all_day: boolean;
    status: 'ongoing' | 'upcoming' | 'finished' | 'all_day';
}

interface DekanPanelProps {
    data: DekanEvent[];
}

// ─── Timeline Config ──────────────────────────────────────────────────────────
const HOUR_START  = 6;
const HOUR_END    = 22;
const TOTAL_HOURS = HOUR_END - HOUR_START;
const PX_PER_HOUR = 72; // piksel per jam di timeline
const TOTAL_PX    = TOTAL_HOURS * PX_PER_HOUR;

const timeToTop = (timeStr: string): number => {
    const [h, m] = timeStr.split(':').map(Number);
    return ((h - HOUR_START) + m / 60) * PX_PER_HOUR;
};

const durationToPx = (start: string, end: string): number => {
    const [sh, sm] = start.split(':').map(Number);
    const [eh, em] = end.split(':').map(Number);
    const mins = (eh * 60 + em) - (sh * 60 + sm);
    return Math.max((mins / 60) * PX_PER_HOUR, 32);
};

// ─── Warna per status (semua pakai palet #741847) ────────────────────────────
const statusTheme = {
    ongoing: {
        block:  'border-l-4 border-[#741847] bg-[#fdf4f7]',
        title:  'text-[#741847] font-bold',
        time:   'text-[#9c2456]',
        dot:    'bg-[#741847]',
    },
    upcoming: {
        block:  'border-l-4 border-[#c97fa0] bg-white',
        title:  'text-[#3d1227] font-semibold',
        time:   'text-[#9c2456]/70',
        dot:    'bg-[#c97fa0]',
    },
    finished: {
        block:  'border-l-4 border-gray-200 bg-gray-50 opacity-50',
        title:  'text-gray-400 line-through font-normal',
        time:   'text-gray-400',
        dot:    'bg-gray-300',
    },
    all_day: {
        block:  'border-l-4 border-[#e8a0ba] bg-[#fdf4f7]',
        title:  'text-[#741847] font-semibold',
        time:   'text-[#9c2456]',
        dot:    'bg-[#e8a0ba]',
    },
};

// ─── Jam sekarang dalam format HH:mm ─────────────────────────────────────────
const getNowStr = () => {
    const d = new Date();
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
};

// ─── Komponen Utama ───────────────────────────────────────────────────────────
const DekanPanel: React.FC<DekanPanelProps> = ({ data }) => {
    const scrollRef   = useRef<HTMLDivElement>(null);
    const nowLineRef  = useRef<HTMLDivElement>(null);
    const [nowStr, setNowStr] = useState(getNowStr());

    // Update "now" setiap menit
    useEffect(() => {
        const iv = setInterval(() => setNowStr(getNowStr()), 60_000);
        return () => clearInterval(iv);
    }, []);

    // Scroll ke garis "sekarang" saat mount
    useEffect(() => {
        nowLineRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, []);

    const nowH = parseInt(nowStr.split(':')[0]);
    const isNowInRange = nowH >= HOUR_START && nowH < HOUR_END;
    const nowTop = isNowInRange ? timeToTop(nowStr) : null;

    const allDayEvents = data.filter(e => e.is_all_day);
    const timedEvents  = data.filter(e => !e.is_all_day && e.start_time);
    const ongoingEvent = timedEvents.find(e => e.status === 'ongoing');

    const hourLabels = Array.from({ length: TOTAL_HOURS + 1 }, (_, i) => HOUR_START + i);

    const today = new Date().toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });

    return (
        <div
            className="relative flex flex-col h-full rounded-2xl overflow-hidden"
            style={{
                background: '#fffbfc',
                border: '1px solid #f0dce5',
                boxShadow: '0 2px 16px 0 rgba(116,24,71,0.07)',
            }}
        >
            {/* ── Header ─────────────────────────────────────────────────── */}
            <div
                className="shrink-0 flex items-center justify-between gap-3 px-5 py-3.5"
                style={{
                    background: 'linear-gradient(135deg, #741847 0%, #9c2456 100%)',
                    borderBottom: '1px solid #5a1038',
                }}
            >
                <div className="flex items-center gap-2.5">
                    <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15">
                        <Calendar className="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h2 className="text-[13px] font-extrabold tracking-widest text-white uppercase leading-none">
                            Agenda Dekan
                        </h2>
                        <p className="text-[10px] text-white/60 font-medium mt-0.5 capitalize">{today}</p>
                    </div>
                </div>

                {/* Badge jumlah event */}
                <div className="flex items-center gap-1.5 bg-white/15 px-3 py-1 rounded-full">
                    <Sun className="w-3 h-3 text-white/70" />
                    <span className="text-xs font-bold text-white">
                        {data.length} agenda
                    </span>
                </div>
            </div>

            {/* ── All-day strip ───────────────────────────────────────────── */}
            {allDayEvents.length > 0 && (
                <div className="shrink-0 px-4 pt-3 pb-0 flex flex-col gap-1.5">
                    <span className="text-[9px] font-extrabold tracking-widest text-[#741847]/50 uppercase">
                        Sepanjang Hari
                    </span>
                    {allDayEvents.map(ev => (
                        <div
                            key={ev.id}
                            className="flex items-center gap-2 px-3 py-2 rounded-lg"
                            style={{ background: '#fdf4f7', border: '1px solid #f0dce5' }}
                        >
                            <div className="w-1.5 h-1.5 rounded-full bg-[#c97fa0] shrink-0" />
                            <span className="text-sm font-semibold text-[#741847] truncate">{ev.title}</span>
                            <span className="ml-auto text-[10px] font-bold text-[#9c2456]/60 bg-[#f5e6ed] px-2 py-0.5 rounded-full shrink-0">
                                All Day
                            </span>
                        </div>
                    ))}
                </div>
            )}

            {/* ── Ongoing highlight card ──────────────────────────────────── */}
            {ongoingEvent && (
                <div
                    className="shrink-0 mx-4 mt-3 rounded-xl px-4 py-3"
                    style={{
                        background: 'linear-gradient(135deg, #fdf4f7 0%, #fff0f5 100%)',
                        border: '1px solid #e8b4cc',
                    }}
                >
                    <div className="flex items-center gap-2 mb-1.5">
                        {/* Pulse dot */}
                        <span className="relative flex h-2 w-2 shrink-0">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#741847] opacity-60" />
                            <span className="relative inline-flex h-2 w-2 rounded-full bg-[#741847]" />
                        </span>
                        <span className="text-[9px] font-extrabold tracking-widest text-[#741847] uppercase">
                            Sedang Berlangsung
                        </span>
                    </div>
                    <p className="font-extrabold text-[#3d1227] text-base leading-snug">{ongoingEvent.title}</p>
                    <div className="flex items-center gap-3 mt-1.5 flex-wrap">
                        <span className="flex items-center gap-1 text-xs text-[#9c2456]">
                            <Clock size={11} />
                            {ongoingEvent.start_time} – {ongoingEvent.end_time}
                        </span>
                        {ongoingEvent.location && (
                            <span className="flex items-center gap-1 text-xs text-[#9c2456]/70">
                                <MapPin size={11} />
                                {ongoingEvent.location}
                            </span>
                        )}
                    </div>
                    {ongoingEvent.description && (
                        <p className="mt-1.5 text-[11px] text-[#741847]/60 line-clamp-2 flex items-start gap-1">
                            <AlignLeft size={10} className="mt-0.5 shrink-0" />
                            {ongoingEvent.description}
                        </p>
                    )}
                </div>
            )}

            {/* ── Timeline ────────────────────────────────────────────────── */}
            <div
                ref={scrollRef}
                className="flex-1 overflow-y-auto min-h-0 px-4 pt-3 pb-4 scrollbar-hide"
            >
                {/* Divider label */}
                <div className="flex items-center gap-2 mb-3">
                    <span className="text-[9px] font-extrabold tracking-widest text-[#741847]/50 uppercase">
                        Timeline Hari Ini
                    </span>
                    <div className="flex-1 h-px bg-[#f0dce5]" />
                </div>

                {/* Timeline body */}
                <div className="relative" style={{ height: `${TOTAL_PX}px` }}>

                    {/* ── Grid lines + jam labels ── */}
                    {hourLabels.map(h => (
                        <div
                            key={h}
                            className="absolute left-0 right-0 flex items-start pointer-events-none"
                            style={{ top: `${(h - HOUR_START) * PX_PER_HOUR}px` }}
                        >
                            <span className="text-[10px] font-mono text-[#c97fa0]/80 w-10 shrink-0 -mt-2 text-right pr-2 select-none">
                                {String(h).padStart(2, '0')}:00
                            </span>
                            <div
                                className="flex-1 mt-0"
                                style={{ borderTop: h % 2 === 0 ? '1px solid #f0dce5' : '1px dashed #f8eaf0' }}
                            />
                        </div>
                    ))}

                    {/* ── Garis "Sekarang" ── */}
                    {nowTop !== null && (
                        <div
                            ref={nowLineRef}
                            className="absolute left-0 right-0 flex items-center z-20 pointer-events-none"
                            style={{ top: `${nowTop}px` }}
                        >
                            <span className="text-[9px] font-mono font-bold text-[#741847] w-10 shrink-0 text-right pr-1.5 select-none">
                                {nowStr}
                            </span>
                            <div className="flex-1 relative" style={{ height: '2px', background: '#741847' }}>
                                <div
                                    className="absolute -left-1 top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-[#741847]"
                                    style={{ boxShadow: '0 0 0 3px #fdf4f7, 0 0 0 4px #741847' }}
                                />
                            </div>
                        </div>
                    )}

                    {/* ── Event blocks ── */}
                    {timedEvents.map(ev => {
                        if (!ev.start_time || !ev.end_time) return null;
                        const top    = timeToTop(ev.start_time);
                        const height = durationToPx(ev.start_time, ev.end_time);
                        const theme  = statusTheme[ev.status];

                        return (
                            <div
                                key={ev.id}
                                className={`
                                    absolute left-10 right-0 ml-2 rounded-lg px-3 py-2
                                    flex flex-col overflow-hidden z-10
                                    ${theme.block}
                                `}
                                style={{ top: `${top}px`, height: `${height}px` }}
                            >
                                <p className={`text-[13px] leading-tight truncate ${theme.title}`}>
                                    {ev.title}
                                </p>
                                <div className={`flex items-center gap-2 mt-1 text-[11px] ${theme.time}`}>
                                    <span className="flex items-center gap-1 font-mono">
                                        <Clock size={10} className="shrink-0" />
                                        {ev.start_time} – {ev.end_time}
                                    </span>
                                    {ev.location && height > 48 && (
                                        <span className="flex items-center gap-1 truncate">
                                            <MapPin size={10} className="shrink-0" />
                                            {ev.location}
                                        </span>
                                    )}
                                </div>
                                {ev.description && height > 72 && (
                                    <p className={`mt-1 text-[10px] line-clamp-2 opacity-70 ${theme.time}`}>
                                        {ev.description}
                                    </p>
                                )}
                            </div>
                        );
                    })}

                    {/* ── Empty state ── */}
                    {timedEvents.length === 0 && (
                        <div
                            className="absolute left-10 right-0 ml-2 flex flex-col items-center justify-center gap-2 rounded-xl"
                            style={{ top: `${2 * PX_PER_HOUR}px`, height: `${4 * PX_PER_HOUR}px`, background: '#fdf4f7', border: '1px dashed #e8b4cc' }}
                        >
                            <Calendar className="w-8 h-8 text-[#c97fa0]/40" />
                            <p className="text-sm text-[#c97fa0] font-medium">Tidak ada agenda hari ini</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default DekanPanel;