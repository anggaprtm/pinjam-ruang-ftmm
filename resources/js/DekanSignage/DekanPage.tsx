import React, { useEffect, useRef, useState, useCallback } from 'react';
import {
    Calendar, Clock, MapPin,
    Cloud, CloudRain, CloudSnow, CloudLightning,
    Wind, Droplets, Thermometer, Users, ChevronLeft, ChevronRight, Sun, RefreshCw
} from 'lucide-react';

// ─── Config ───────────────────────────────────────────────────────────────────
const API_BASE    = '/api/v1';
const SIGNAGE_KEY = (import.meta as any).env?.VITE_SIGNAGE_KEY ?? '';
const REFRESH_MS  = 5 * 60 * 1000;
const hdrs        = { 'X-Signage-Key': SIGNAGE_KEY };

// ─── Types ────────────────────────────────────────────────────────────────────
interface DekanEvent {
    id: string;
    title: string;
    description?: string;
    location?: string;
    start_time: string | null;
    end_time: string | null;
    is_all_day: boolean;
    status: 'ongoing' | 'upcoming' | 'finished' | 'all_day';
    date: string;
}

interface RapatItem {
    id: number;
    title: string;
    room: string;
    time: string;
    status: 'Occupied' | 'Reserved' | 'Finished';
    pic: string;
    date_label: string;
    date_flag: 'today' | 'tomorrow' | 'future';
}

interface WeatherData {
    temp: number;
    feelsLike: number;
    humidity: number;
    windspeed: number;
    weatherCode: number;
}

// ─── Timeline Config ──────────────────────────────────────────────────────────
const HOUR_START  = 5;
const HOUR_END    = 22;
const TOTAL_HOURS = HOUR_END - HOUR_START;
const PX_PER_HOUR = 80;
const TOTAL_PX    = TOTAL_HOURS * PX_PER_HOUR;

const pad = (n: number) => String(n).padStart(2, '0');

const timeToMinutes = (t: string) => {
    const [h, m] = t.split(':').map(Number);
    return h * 60 + m;
};
const timeToTop = (t: string) =>
    ((timeToMinutes(t) - HOUR_START * 60) / 60) * PX_PER_HOUR;

const durationToPx = (s: string, e: string) =>
    Math.max(((timeToMinutes(e) - timeToMinutes(s)) / 60) * PX_PER_HOUR, 24);

// ─── Event Color Palette ──────────────────────────────────────────────────────
// Palet harmonis berbasis tema #741847 — semua light & readable
const EVENT_COLORS = [
    { bg: '#fdf2f6', border: '#d4608a', title: '#7a1040', time: '#a03060' }, // Rose (brand)
    { bg: '#f0f4ff', border: '#6b8dd6', title: '#2d4a8a', time: '#4a6ab0' }, // Periwinkle blue
    { bg: '#f0fbf4', border: '#52a876', title: '#1d6b40', time: '#3a8a5a' }, // Sage green
    { bg: '#fff8f0', border: '#d4874a', title: '#8a4010', time: '#b05830' }, // Warm coral
    { bg: '#f5f0fb', border: '#9b72cc', title: '#4a2080', time: '#6b40a0' }, // Soft violet
    { bg: '#f0fafc', border: '#40a8c0', title: '#1a6070', time: '#2a8090' }, // Teal
    { bg: '#fffbf0', border: '#c8a030', title: '#705010', time: '#906820' }, // Amber
    { bg: '#fdf0f8', border: '#c060a0', title: '#702060', time: '#902880' }, // Mauve
];

// Warna untuk event finished — selalu abu-abu
const FINISHED_COLOR = { bg: '#f5f5f5', border: '#c0c0c0', title: '#909090', time: '#b0b0b0' };

// Assign warna berdasarkan hash ID event agar konsisten antar render
const getEventColor = (id: string, status: string) => {
    if (status === 'finished') return FINISHED_COLOR;
    let hash = 0;
    for (let i = 0; i < id.length; i++) hash = (hash * 31 + id.charCodeAt(i)) >>> 0;
    return EVENT_COLORS[hash % EVENT_COLORS.length];
};

// ─── Collision Layout (Google Calendar style) ─────────────────────────────────
interface LayoutEvent extends DekanEvent {
    col: number;
    totalCols: number;
}

function computeLayout(events: DekanEvent[]): LayoutEvent[] {
    if (events.length === 0) return [];

    // Sort by start time, then by duration desc (longer events get earlier columns)
    const sorted = [...events].sort((a, b) => {
        const startDiff = timeToMinutes(a.start_time!) - timeToMinutes(b.start_time!);
        if (startDiff !== 0) return startDiff;
        // longer duration first
        return (timeToMinutes(b.end_time!) - timeToMinutes(b.start_time!))
             - (timeToMinutes(a.end_time!) - timeToMinutes(a.start_time!));
    });

    // Assign column slots using greedy interval coloring
    // columns[i] = end time of last event placed in column i
    const columns: number[] = [];
    const assigned: { ev: DekanEvent; col: number }[] = [];

    for (const ev of sorted) {
        const start = timeToMinutes(ev.start_time!);
        const end   = timeToMinutes(ev.end_time!);

        // Find first free column (no overlap)
        let placed = false;
        for (let c = 0; c < columns.length; c++) {
            if (columns[c] <= start) {
                columns[c] = end;
                assigned.push({ ev, col: c });
                placed = true;
                break;
            }
        }
        if (!placed) {
            assigned.push({ ev, col: columns.length });
            columns.push(end);
        }
    }

    // Now compute totalCols for each event:
    // An event's totalCols = max number of columns that overlap with its time range
    const result: LayoutEvent[] = assigned.map(({ ev, col }) => {
        const start = timeToMinutes(ev.start_time!);
        const end   = timeToMinutes(ev.end_time!);

        // Count how many assigned events overlap with this event
        const overlappingCols = new Set<number>();
        for (const other of assigned) {
            const os = timeToMinutes(other.ev.start_time!);
            const oe = timeToMinutes(other.ev.end_time!);
            if (os < end && oe > start) {
                overlappingCols.add(other.col);
            }
        }

        return { ...ev, col, totalCols: overlappingCols.size };
    });

    return result;
}

// ─── Week Helpers ─────────────────────────────────────────────────────────────
const getWeekDays = (offset: number) => {
    const now    = new Date();
    const day    = now.getDay();
    const diff   = day === 0 ? -6 : 1 - day;
    const monday = new Date(now);
    monday.setDate(now.getDate() + diff + offset * 7);
    return Array.from({ length: 5 }, (_, i) => {
        const d = new Date(monday);
        d.setDate(monday.getDate() + i);
        return d;
    });
};
const toDateStr = (d: Date) =>
    `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

const DAY_LABELS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum'];
const MONTH_ID   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

// ─── Weather ──────────────────────────────────────────────────────────────────
const getWeatherInfo = (code: number) => {
    if (code === 0)  return { label: 'Cerah',       icon: <Sun size={13} />,            color: '#f59e0b' };
    if (code <= 3)   return { label: 'Berawan',     icon: <Cloud size={13} />,          color: '#94a3b8' };
    if (code <= 49)  return { label: 'Berkabut',    icon: <Wind size={13} />,           color: '#94a3b8' };
    if (code <= 69)  return { label: 'Hujan',       icon: <CloudRain size={13} />,      color: '#60a5fa' };
    if (code <= 79)  return { label: 'Salju',       icon: <CloudSnow size={13} />,      color: '#93c5fd' };
    if (code <= 84)  return { label: 'Hujan Lebat', icon: <CloudRain size={13} />,      color: '#3b82f6' };
    if (code <= 99)  return { label: 'Badai',       icon: <CloudLightning size={13} />, color: '#f59e0b' };
    return                  { label: 'N/A',         icon: <Cloud size={13} />,          color: '#94a3b8' };
};

// All-day chip colors — cycling palette (lighter variants)
const ALLDAY_COLORS = [
    { bg: '#fdf2f6', border: '#e8a0ba', text: '#741847' },
    { bg: '#f0f4ff', border: '#a0b4e8', text: '#2d4a8a' },
    { bg: '#f0fbf4', border: '#90c8a0', text: '#1d6b40' },
    { bg: '#fff8f0', border: '#e8c090', text: '#8a4010' },
    { bg: '#f5f0fb', border: '#c0a0e0', text: '#4a2080' },
];

// ══════════════════════════════════════════════════════════════════════
// HEADER
// ══════════════════════════════════════════════════════════════════════
const DekanHeader: React.FC<{ weather: WeatherData | null }> = ({ weather }) => {
    const [time, setTime] = useState(new Date());
    useEffect(() => {
        const iv = setInterval(() => setTime(new Date()), 1000);
        return () => clearInterval(iv);
    }, []);
    const wInfo = weather ? getWeatherInfo(weather.weatherCode) : null;

    return (
        <div className="shrink-0 relative overflow-hidden rounded-2xl"
            style={{ background: 'linear-gradient(135deg,#741847 0%,#9c2456 55%,#741847 100%)' }}>
            <div className="absolute -top-10 -right-10 w-44 h-44 rounded-full pointer-events-none"
                style={{ background: 'rgba(255,255,255,0.04)' }} />
            <div className="absolute -bottom-16 left-1/3 w-56 h-56 rounded-full pointer-events-none"
                style={{ background: 'rgba(255,255,255,0.03)' }} />
            <div className="relative z-10 flex items-center justify-between gap-6 px-6 py-3.5">
                {/* Logo */}
                <div className="shrink-0">
                    <img src="/images/logo-ftmm.png" alt="Logo FTMM"
                        className="h-12 w-auto object-contain"
                        onError={e => { (e.target as HTMLImageElement).style.display = 'none'; }} />
                </div>
                {/* Center */}
                <div className="flex-1 min-w-0 flex flex-col items-center gap-1.5">
                    <div className="px-5 py-1.5 rounded-full"
                        style={{ background: 'rgba(255,255,255,0.15)', border: '1px solid rgba(255,255,255,0.2)' }}>
                        <span className="text-white/90 font-semibold text-sm tracking-wide">
                            Agenda Dekan • Fakultas Teknologi Maju dan Multidisiplin
                        </span>
                    </div>
                    {weather && wInfo && (
                        <div className="flex items-center gap-3 px-4 py-1.5 rounded-full"
                            style={{ background: 'rgba(255,255,255,0.10)', border: '1px solid rgba(255,255,255,0.15)' }}>
                            <span className="flex items-center gap-1.5 text-xs font-semibold" style={{ color: wInfo.color }}>
                                {wInfo.icon}
                                <span className="text-white/90">{wInfo.label}</span>
                            </span>
                            <div className="w-px h-3 bg-white/20" />
                            <span className="flex items-center gap-1 text-white text-sm font-bold">
                                <Thermometer size={11} className="opacity-60" />
                                {weather.temp}°C <span className="text-[11px] opacity-50">/ {weather.feelsLike}°C</span>
                            </span>
                            <div className="w-px h-3 bg-white/20" />
                            <span className="flex items-center gap-1 text-white/80 text-xs">
                                <Droplets size={11} className="opacity-60" />{weather.humidity}%
                            </span>
                            <div className="w-px h-3 bg-white/20" />
                            <span className="flex items-center gap-1 text-white/80 text-xs">
                                <Wind size={11} className="opacity-60" />{weather.windspeed} km/h
                            </span>
                            <div className="w-px h-3 bg-white/20" />
                            <span className="text-[10px] text-white/40 font-mono uppercase tracking-widest">Surabaya</span>
                        </div>
                    )}
                </div>
                {/* Clock */}
                <div className="shrink-0 text-right">
                    <div className="text-4xl font-bold text-white tracking-tight font-mono leading-none">
                        {pad(time.getHours())}:{pad(time.getMinutes())}:{pad(time.getSeconds())} WIB
                    </div>
                    <div className="text-sm font-medium mt-1 uppercase tracking-wide text-white/60">
                        {time.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                    </div>
                </div>
            </div>
        </div>
    );
};

// ══════════════════════════════════════════════════════════════════════
// PANEL RAPAT
// ══════════════════════════════════════════════════════════════════════
const RapatPanel: React.FC<{ data: RapatItem[] }> = ({ data }) => {
    const st = {
        Occupied: { dot: '#ef4444', label: 'Berlangsung', bg: '#fef2f2', text: '#dc2626' },
        Reserved: { dot: '#c97fa0', label: 'Dijadwalkan', bg: '#fdf4f7', text: '#741847' },
        Finished: { dot: '#d1d5db', label: 'Selesai',     bg: '#f9fafb', text: '#9ca3af' },
    };
    return (
        <div className="flex flex-col h-full rounded-2xl overflow-hidden"
            style={{ background: '#fffbfc', border: '1px solid #f0dce5', boxShadow: '0 2px 12px rgba(116,24,71,0.06)' }}>
            <div className="shrink-0 flex items-center gap-2.5 px-4 py-3 border-b" style={{ borderColor: '#f0dce5' }}>
                <div className="flex items-center justify-center w-7 h-7 rounded-lg" style={{ background: '#fdf4f7' }}>
                    <Users className="w-4 h-4" style={{ color: '#741847' }} />
                </div>
                <div>
                    <h2 className="text-[11px] font-extrabold tracking-widest uppercase" style={{ color: '#741847' }}>
                        Rapat Lt. 10
                    </h2>
                    <p className="text-[9px] font-medium" style={{ color: 'rgba(116,24,71,0.45)' }}>
                        Jadwal penggunaan ruang
                    </p>
                </div>
            </div>
            <div className="flex-1 overflow-y-auto min-h-0 p-3 flex flex-col gap-2 scrollbar-hide">
                {data.length === 0 && (
                    <div className="flex-1 flex flex-col items-center justify-center gap-2 opacity-40">
                        <Calendar className="w-8 h-8" style={{ color: '#c97fa0' }} />
                        <p className="text-xs font-medium" style={{ color: '#741847' }}>Tidak ada rapat</p>
                    </div>
                )}
                {data.map(item => {
                    const s = st[item.status] ?? st.Reserved;
                    const isOcc = item.status === 'Occupied';
                    return (
                        <div key={item.id}
                            className="relative rounded-xl overflow-hidden flex flex-col gap-1.5 px-3 py-2.5"
                            style={{ background: s.bg, border: `1px solid ${isOcc ? '#fecaca' : '#f0dce5'}` }}>
                            <div className="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl" style={{ background: s.dot }} />
                            <div className="flex items-center justify-between gap-1 pl-1">
                                <span className="text-[9px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded"
                                    style={item.date_flag === 'today'
                                        ? { background: '#741847', color: 'white' }
                                        : { background: '#fdf4f7', color: '#741847', border: '1px solid #f0dce5' }}>
                                    {item.date_label}
                                </span>
                                <span className="text-[9px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full"
                                    style={{ background: s.bg, color: s.text, border: `1px solid ${s.dot}40` }}>
                                    {isOcc ? <span className="animate-pulse">{s.label}</span> : s.label}
                                </span>
                            </div>
                            <div className="pl-1">
                                <p className="text-xs font-bold leading-tight truncate"
                                    style={{ color: isOcc ? '#dc2626' : '#3d1227' }}>{item.room}</p>
                                <p className="text-[11px] leading-snug line-clamp-2 mt-0.5"
                                    style={{ color: '#741847', opacity: 0.7 }}>{item.title}</p>
                            </div>
                            <div className="pl-1 flex items-center justify-between gap-2">
                                <span className="flex items-center gap-1 text-[10px] font-mono" style={{ color: '#9c2456' }}>
                                    <Clock size={9} />{item.time}
                                </span>
                                {item.pic && item.pic !== '-' && (
                                    <span className="text-[9px] truncate max-w-[100px]"
                                        style={{ color: 'rgba(116,24,71,0.5)' }}>{item.pic}</span>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

// ══════════════════════════════════════════════════════════════════════
// MAIN PAGE
// ══════════════════════════════════════════════════════════════════════
const DekanPage: React.FC = () => {
    const [dekanData, setDekanData]   = useState<DekanEvent[]>([]);
    const [rapatData, setRapatData]   = useState<RapatItem[]>([]);
    const [weather, setWeather]       = useState<WeatherData | null>(null);
    const [loading, setLoading]       = useState(true);
    const [weekOffset, setWeekOffset] = useState(0);
    const [lastSync, setLastSync]     = useState<Date | null>(null);
    const [refreshing, setRefreshing] = useState(false);
    const nowLineRef                  = useRef<HTMLDivElement>(null);

    const [nowStr, setNowStr] = useState(() => {
        const d = new Date();
        return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
    });

    const scrollToNow = useCallback(() => {
        nowLineRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, []);

    useEffect(() => {
        const iv = setInterval(() => {
            const d = new Date();
            setNowStr(`${pad(d.getHours())}:${pad(d.getMinutes())}`);
            // Auto-scroll ke garis jam terkini setiap menit (hanya saat lihat minggu ini)
            if (weekOffset === 0) scrollToNow();
        }, 60_000);
        return () => clearInterval(iv);
    }, [weekOffset, scrollToNow]);

    const fetchDekan = useCallback(async () => {
        try {
            const res  = await fetch(
                `${API_BASE}/signage/agenda-dekan?week_offset=${weekOffset}`,
                { headers: hdrs }
            );
            const json = await res.json();
            setDekanData(json.data ?? []);
            setLastSync(new Date());
        } catch {}
    }, [weekOffset]);

    const fetchRapat = useCallback(async () => {
        try {
            const res  = await fetch(`${API_BASE}/signage/vertical-data?signage_key=${SIGNAGE_KEY}`);
            const json = await res.json();
            setRapatData(Array.isArray(json) ? json : []);
        } catch {}
    }, []);

    const fetchWeather = useCallback(async () => {
        try {
            const res = await fetch(
                'https://api.open-meteo.com/v1/forecast'
                + '?latitude=-7.2575&longitude=112.7521'
                + '&current=temperature_2m,apparent_temperature,relative_humidity_2m,wind_speed_10m,weather_code'
                + '&timezone=Asia%2FJakarta'
            );
            const d   = await res.json();
            const cur = d.current;
            setWeather({
                temp: Math.round(cur.temperature_2m),
                feelsLike: Math.round(cur.apparent_temperature),
                humidity: cur.relative_humidity_2m,
                windspeed: Math.round(cur.wind_speed_10m),
                weatherCode: cur.weather_code,
            });
        } catch {}
    }, []);

    const handleRefresh = useCallback(async () => {
        setRefreshing(true);
        try {
            const res  = await fetch(
                `${API_BASE}/signage/agenda-dekan/refresh?week_offset=${weekOffset}`,
                { method: 'POST', headers: hdrs }
            );
            const json = await res.json();
            setDekanData(json.data ?? []);
            setLastSync(new Date());
        } catch {}
        finally { setRefreshing(false); }
    }, [weekOffset]);

    useEffect(() => {
        Promise.all([fetchDekan(), fetchRapat(), fetchWeather()])
            .finally(() => setLoading(false));
        const ivD = setInterval(() => { fetchDekan(); fetchRapat(); }, REFRESH_MS);
        const ivW = setInterval(fetchWeather, 10 * 60 * 1000);
        return () => { clearInterval(ivD); clearInterval(ivW); };
    }, [fetchDekan, fetchRapat, fetchWeather]);

    useEffect(() => {
        if (!loading && weekOffset === 0) {
            setTimeout(() => scrollToNow(), 300);
        }
    }, [loading, weekOffset]);

    const weekDays   = getWeekDays(weekOffset);
    const todayStr   = toDateStr(new Date());
    const hourLabels = Array.from({ length: TOTAL_HOURS + 1 }, (_, i) => HOUR_START + i);
    const nowH       = parseInt(nowStr.split(':')[0]);
    const nowTop     = (nowH >= HOUR_START && nowH < HOUR_END) ? timeToTop(nowStr) : null;

    // Cari event ongoing hari ini (untuk Opsi 1 & 2)
    const ongoingEvent = dekanData.find(
        ev => ev.status === 'ongoing' && !ev.is_all_day
    ) ?? null;

    // Group events by date
    const byDate: Record<string, DekanEvent[]> = {};
    for (const ev of dekanData) {
        if (!ev.date) continue;
        if (!byDate[ev.date]) byDate[ev.date] = [];
        byDate[ev.date].push(ev);
    }

    if (loading) return (
        <div className="w-screen h-screen flex items-center justify-center" style={{ background: '#fffbfc' }}>
            <div className="flex flex-col items-center gap-3">
                <div className="w-10 h-10 rounded-full border-2 animate-spin"
                    style={{ borderColor: '#741847', borderTopColor: 'transparent' }} />
                <p className="text-sm font-medium" style={{ color: 'rgba(116,24,71,0.5)' }}>
                    Memuat agenda dekan...
                </p>
            </div>
        </div>
    );

    return (
        <div className="w-screen h-screen overflow-hidden flex flex-col gap-3 p-4"
            style={{ background: 'linear-gradient(160deg,#fdf4f7 0%,#fff8fa 50%,#f9f0f4 100%)' }}>

            {/* Header */}
            <DekanHeader weather={weather} />

            {/* Body */}
            <div className="flex-1 min-h-0 flex gap-3">

                {/* ── Week Timeline ── */}
                <div className="flex-1 min-w-0 flex flex-col rounded-2xl overflow-hidden"
                    style={{ background: '#fffbfc', border: '1px solid #f0dce5', boxShadow: '0 2px 12px rgba(116,24,71,0.06)' }}>

                    {/* Nav bar */}
                    <div className="shrink-0 flex items-center gap-2 px-4 py-2.5 border-b" style={{ borderColor: '#f0dce5' }}>
                        <button onClick={() => setWeekOffset(w => w - 1)}
                            className="flex items-center justify-center w-7 h-7 rounded-lg"
                            style={{ border: '1px solid #f0dce5', color: '#741847', background: 'transparent', cursor: 'pointer' }}>
                            <ChevronLeft size={14} />
                        </button>

                        <div className="flex-1 grid" style={{ gridTemplateColumns: '40px repeat(5, 1fr)' }}>
                            <div />
                            {weekDays.map((d, i) => {
                                const isToday = toDateStr(d) === todayStr;
                                return (
                                    <div key={i} className="flex flex-col items-center gap-0.5 py-0.5">
                                        <span className="text-[10px] font-bold uppercase tracking-widest"
                                            style={{ color: isToday ? '#741847' : 'rgba(116,24,71,0.4)' }}>
                                            {DAY_LABELS[i]}
                                        </span>
                                        <span className="text-sm font-extrabold w-7 h-7 flex items-center justify-center rounded-full"
                                            style={isToday
                                                ? { background: '#741847', color: 'white' }
                                                : { color: '#3d1227' }}>
                                            {d.getDate()}
                                        </span>
                                        <span className="text-[9px]" style={{ color: 'rgba(116,24,71,0.35)' }}>
                                            {MONTH_ID[d.getMonth()]}
                                        </span>
                                    </div>
                                );
                            })}
                        </div>

                        <button onClick={() => setWeekOffset(w => w + 1)}
                            className="flex items-center justify-center w-7 h-7 rounded-lg"
                            style={{ border: '1px solid #f0dce5', color: '#741847', background: 'transparent', cursor: 'pointer' }}>
                            <ChevronRight size={14} />
                        </button>

                        {/* Tombol Refresh Manual */}
                        <button
                            onClick={handleRefresh}
                            disabled={refreshing}
                            title="Refresh data dari Google Calendar"
                            className="flex items-center justify-center w-7 h-7 rounded-lg"
                            style={{
                                border: '1px solid #f0dce5',
                                color: refreshing ? '#c97fa0' : '#741847',
                                background: refreshing ? '#fdf4f7' : 'transparent',
                                cursor: refreshing ? 'not-allowed' : 'pointer',
                                transition: 'all 0.2s',
                            }}>
                            <RefreshCw size={14} style={{ animation: refreshing ? 'spin 0.8s linear infinite' : 'none' }} />
                        </button>

                        {weekOffset !== 0 && (
                            <button onClick={() => setWeekOffset(0)}
                                className="text-[10px] font-bold px-3 py-1 rounded-full"
                                style={{ background: '#fdf4f7', color: '#741847', border: '1px solid #f0dce5', cursor: 'pointer' }}>
                                Hari Ini
                            </button>
                        )}
                    </div>

                    {/* ── OPSI 1: Sticky banner ongoing di bawah nav bar — hapus blok ini jika pilih Opsi 2 ── */}
                    {ongoingEvent && weekOffset === 0 && (
                        <div className="shrink-0 mx-3 mt-2.5 mb-0 flex items-center gap-3 px-4 py-2.5 rounded-xl"
                            style={{
                                background: 'linear-gradient(90deg, #fdf0f4 0%, #fff8fa 100%)',
                                border: '1px solid #e8b4cc',
                                borderLeft: '4px solid #741847',
                            }}>
                            {/* Pulse dot */}
                            <span className="relative flex h-2.5 w-2.5 shrink-0">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#741847] opacity-40" />
                                <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#741847]" />
                            </span>
                            <div className="flex flex-col min-w-0 flex-1">
                                <span className="text-[9px] font-extrabold tracking-widest text-[#741847]/50 uppercase leading-none mb-0.5">
                                    Sedang Berlangsung
                                </span>
                                <span className="text-sm font-bold text-[#3d1227] truncate leading-tight">
                                    {ongoingEvent.title}
                                </span>
                            </div>
                            <div className="flex items-center gap-3 shrink-0">
                                <span className="flex items-center gap-1 text-xs font-mono text-[#9c2456]">
                                    <Clock size={11} />
                                    {ongoingEvent.start_time} – {ongoingEvent.end_time}
                                </span>
                                {ongoingEvent.location && (
                                    <>
                                        <div className="w-px h-3 bg-[#e8b4cc]" />
                                        <span className="flex items-center gap-1 text-xs text-[#9c2456]/70 max-w-[200px] truncate">
                                            <MapPin size={11} className="shrink-0" />
                                            {ongoingEvent.location}
                                        </span>
                                    </>
                                )}
                            </div>
                        </div>
                    )}
                    {/* ── END OPSI 1 ── */}

                    {/* Grid */}
                    <div className="flex-1 overflow-y-auto min-h-0 scrollbar-hide">
                        <div className="relative" style={{ height: `${TOTAL_PX}px`, display: 'grid', gridTemplateColumns: '40px repeat(5, 1fr)' }}>

                            {/* Jam labels + horizontal lines */}
                            {hourLabels.map(h => (
                                <React.Fragment key={h}>
                                    <div className="absolute z-10 flex items-start"
                                        style={{ top: (h - HOUR_START) * PX_PER_HOUR, left: 0, width: 40 }}>
                                        <span className="text-[10px] font-mono w-full text-right pr-2 select-none"
                                            style={{ marginTop: -7, color: 'rgba(201,127,160,0.7)' }}>
                                            {pad(h)}:00
                                        </span>
                                    </div>
                                    <div className="absolute pointer-events-none"
                                        style={{
                                            top: (h - HOUR_START) * PX_PER_HOUR,
                                            left: 40, right: 0, height: 0,
                                            borderTop: h % 2 === 0 ? '1px solid #f0dce5' : '1px dashed #f8eaf0',
                                        }} />
                                </React.Fragment>
                            ))}

                            {/* Vertical column separators */}
                            {[1, 2, 3, 4].map(i => (
                                <div key={`vs-${i}`} className="absolute top-0 bottom-0 pointer-events-none"
                                    style={{ left: `calc(40px + ${i} * ((100% - 40px) / 5))`, borderLeft: '1px solid #f8eaf0' }} />
                            ))}

                            {/* Now line */}
                            {weekOffset === 0 && nowTop !== null && (
                                <div ref={nowLineRef} className="absolute z-20 pointer-events-none"
                                    style={{ top: nowTop, left: 40, right: 0 }}>
                                    <div style={{ position: 'relative', height: 2, background: '#741847' }}>
                                        <div style={{
                                            position: 'absolute', left: -4, top: '50%', transform: 'translateY(-50%)',
                                            width: 10, height: 10, borderRadius: '50%', background: '#741847',
                                            boxShadow: '0 0 0 3px #fffbfc, 0 0 0 4px #741847',
                                        }} />
                                    </div>
                                </div>
                            )}

                            {/* Events per day */}
                            {weekDays.map((day, di) => {
                                const ds      = toDateStr(day);
                                const isToday = ds === todayStr;
                                const timed   = (byDate[ds] ?? []).filter(e => !e.is_all_day && e.start_time && e.end_time);
                                const allDay  = (byDate[ds] ?? []).filter(e => e.is_all_day);
                                const laid    = computeLayout(timed);

                                // Column geometry helpers
                                const colFrac = `(100% - 40px) / 5`;
                                const dayLeft = `calc(40px + ${di} * (${colFrac}))`;
                                const dayW    = `calc(${colFrac})`;

                                return (
                                    <React.Fragment key={ds}>
                                        {/* Today bg highlight */}
                                        {isToday && (
                                            <div className="absolute top-0 bottom-0 pointer-events-none"
                                                style={{
                                                    left: dayLeft,
                                                    width: dayW,
                                                    background: 'rgba(116,24,71,0.025)',
                                                }} />
                                        )}

                                        {/* All-day chips */}
                                        {allDay.map((ev, ai) => {
                                            const aColor = ALLDAY_COLORS[ai % ALLDAY_COLORS.length];
                                            return (
                                                <div key={ev.id} className="absolute z-10"
                                                    style={{
                                                        left: `calc(40px + ${di} * (${colFrac}) + 2px)`,
                                                        width: `calc(${colFrac} - 4px)`,
                                                        top: ai * 22, height: 20,
                                                        borderRadius: 4,
                                                        background: aColor.bg,
                                                        border: `1px solid ${aColor.border}`,
                                                        padding: '0 6px',
                                                        display: 'flex', alignItems: 'center', overflow: 'hidden',
                                                    }}>
                                                    <span style={{
                                                        fontSize: 10, fontWeight: 700, color: aColor.text,
                                                        overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                                                    }}>
                                                        {ev.title}
                                                    </span>
                                                </div>
                                            );
                                        })}

                                        {/* Timed events — Google Calendar style collision layout */}
                                        {laid.map(ev => {
                                            const top    = timeToTop(ev.start_time!);
                                            const height = durationToPx(ev.start_time!, ev.end_time!);
                                            const color  = getEventColor(ev.id, ev.status);

                                            // Each event takes its slot fraction of the day column
                                            // with a 2px gap between slots and 2px padding from column edge
                                            const GAP      = 2;
                                            const PAD      = 2;
                                            const slotW    = `calc((${colFrac} - ${PAD * 2}px - ${GAP * (ev.totalCols - 1)}px) / ${ev.totalCols})`;
                                            const slotLeft = `calc(40px + ${di} * (${colFrac}) + ${PAD}px + ${ev.col} * ((${colFrac} - ${PAD * 2}px - ${GAP * (ev.totalCols - 1)}px) / ${ev.totalCols} + ${GAP}px))`;

                                            const isOngoing = ev.status === 'ongoing';

                                            return (
                                                <div key={ev.id} className="absolute z-10 overflow-hidden"
                                                    style={{
                                                        top,
                                                        height,
                                                        left: slotLeft,
                                                        width: slotW,
                                                        borderRadius: 7,
                                                        background: color.bg,
                                                        borderLeft: `3px solid ${color.border}`,
                                                        outline: isOngoing ? `1.5px solid ${color.border}` : `1px solid ${color.border}40`,
                                                        outlineOffset: isOngoing ? '0px' : undefined,
                                                        padding: '4px 6px',
                                                        opacity: ev.status === 'finished' ? 0.45 : 1,
                                                        boxShadow: isOngoing
                                                            ? `0 2px 8px 0 ${color.border}40`
                                                            : '0 1px 3px 0 rgba(0,0,0,0.04)',
                                                    }}>

                                                    {/* Ongoing pulse indicator */}
                                                    {isOngoing && (
                                                        <span className="absolute top-1.5 right-1.5 flex h-1.5 w-1.5">
                                                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full opacity-60"
                                                                style={{ background: color.border }} />
                                                            <span className="relative inline-flex h-1.5 w-1.5 rounded-full"
                                                                style={{ background: color.border }} />
                                                        </span>
                                                    )}

                                                    <p style={{
                                                        fontSize: 11, fontWeight: 700,
                                                        color: color.title,
                                                        lineHeight: 1.25,
                                                        overflow: 'hidden',
                                                        display: '-webkit-box',
                                                        WebkitLineClamp: 2,
                                                        WebkitBoxOrient: 'vertical',
                                                        paddingRight: isOngoing ? 12 : 0,
                                                    } as React.CSSProperties}>
                                                        {ev.title}
                                                    </p>

                                                    {height > 36 && (
                                                        <p style={{ fontSize: 10, color: color.time, fontFamily: 'monospace', marginTop: 2 }}>
                                                            {ev.start_time}–{ev.end_time}
                                                        </p>
                                                    )}

                                                    {height > 58 && ev.location && (
                                                        <p style={{
                                                            fontSize: 10, color: color.time, opacity: 0.8,
                                                            marginTop: 1, overflow: 'hidden', textOverflow: 'ellipsis',
                                                            whiteSpace: 'nowrap', display: 'flex', alignItems: 'center', gap: 2,
                                                        }}>
                                                            <MapPin size={9} style={{ flexShrink: 0 }} />{ev.location}
                                                        </p>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </React.Fragment>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Panel Rapat */}
                <div className="w-64 shrink-0 min-h-0">
                    <RapatPanel data={rapatData} />
                </div>
            </div>

            {/* Footer */}
            <div className="shrink-0 flex items-center justify-between px-1">
                <span className="text-[10px] font-mono uppercase tracking-widest"
                    style={{ color: 'rgba(116,24,71,0.25)' }}>
                    FTMM – Universitas Airlangga
                </span>
                {lastSync && (
                    <span className="text-[10px] font-mono" style={{ color: 'rgba(116,24,71,0.25)' }}>
                        Diperbarui: {lastSync.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} WIB
                    </span>
                )}
            </div>
        </div>
    );
};

export default DekanPage;