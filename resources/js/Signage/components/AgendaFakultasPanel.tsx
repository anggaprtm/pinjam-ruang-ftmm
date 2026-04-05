import React, { useEffect, useState } from 'react';
import { CalendarDays, Clock, Timer, Tag, ChevronRight } from 'lucide-react';
import GlassPanel from './GlassPanel';
import AutoScrollList from './AutoScrollList';

const getSignageApiKey = () =>
    document.querySelector('meta[name="signage-api-key"]')?.getAttribute('content') || '';

interface AgendaItem {
    id: number;
    judul: string;
    deskripsi?: string;
    kategori: string;
    warna: string;
    tanggal_mulai: string;
    tanggal_selesai?: string;
    waktu_mulai?: string;
    waktu_selesai?: string;
    is_all_day: boolean;
    is_ongoing: boolean;
    sisa_hari: number;
    sisa_waktu_label: string;
    date_day: string;
    date_month: string;
    date_full: string;
}

interface CountdownItem {
    id: number;
    judul: string;
    kategori: string;
    warna: string;
    sisa_hari: number;
    sisa_waktu_label: string;
    date_full: string;
    is_ongoing: boolean;
}

interface AgendaFakultasData {
    agendas: AgendaItem[];
    countdowns: CountdownItem[];
}

const AgendaFakultasPanel: React.FC = () => {
    const [data, setData]       = useState<AgendaFakultasData>({ agendas: [], countdowns: [] });
    const [loading, setLoading] = useState(true);

    const fetchAgenda = async () => {
        try {
            const apiUrl = new URL('/api/v1/signage/agenda-fakultas', window.location.origin);
            const apiKey = getSignageApiKey();
            if (apiKey) apiUrl.searchParams.set('signage_key', apiKey);

            const res  = await fetch(apiUrl.toString());
            const json = await res.json();
            setData(json);
        } catch (e) {
            console.error('Gagal fetch agenda fakultas', e);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchAgenda();
        const iv = setInterval(fetchAgenda, 5 * 60 * 1000); // refresh 5 menit
        return () => clearInterval(iv);
    }, []);

    if (loading) {
        return (
            <GlassPanel
                title="AGENDA FAKULTAS"
                icon={<CalendarDays className="w-6 h-6 text-electric-400" />}
                className="h-full bg-navy-900/80 border-white/10"
            >
                <div className="animate-pulse text-white/30 text-sm">Memuat agenda...</div>
            </GlassPanel>
        );
    }

    const { agendas, countdowns } = data;
    const highlightAgenda = agendas[0] ?? null;
    const otherAgendas    = agendas.slice(1);

    return (
        <GlassPanel
            title="AGENDA FAKULTAS"
            icon={<CalendarDays className="w-6 h-6 text-electric-400" />}
            className="h-full bg-navy-900/80 border-white/10"
        >
            <div className="flex flex-col h-full gap-4 overflow-hidden">

                {/* ── EMPTY STATE ── */}
                {agendas.length === 0 && countdowns.length === 0 && (
                    <div className="flex-1 flex flex-col items-center justify-center text-white/20 text-center">
                        <CalendarDays className="w-16 h-16 mb-3 opacity-20" />
                        <p className="text-sm font-mono uppercase tracking-widest">
                            Tidak ada agenda mendatang
                        </p>
                    </div>
                )}

                {/* ── COUNTDOWN STRIP (jika ada) ── */}
                {countdowns.length > 0 && (
                    <div className="shrink-0 flex flex-col gap-2">
                        {countdowns.map(cd => (
                            <div
                                key={cd.id}
                                className="flex items-center gap-3 px-4 py-2.5 rounded-xl border"
                                style={{
                                    borderColor: cd.warna + '40',
                                    background:  cd.warna + '12',
                                }}
                            >
                                <Timer size={16} style={{ color: cd.warna }} className="shrink-0" />

                                <div className="flex-1 min-w-0">
                                    <div className="text-xs font-bold text-white/50 uppercase tracking-wider truncate">
                                        {cd.kategori}
                                    </div>
                                    <div className="text-sm font-bold text-white truncate leading-tight">
                                        {cd.judul}
                                    </div>
                                    <div className="text-[11px] text-white/40 font-mono">{cd.date_full}</div>
                                </div>

                                {/* Countdown badge */}
                                <div
                                    className="shrink-0 text-right"
                                    style={{ color: cd.warna }}
                                >
                                    {cd.is_ongoing ? (
                                        <span className="text-xs font-bold px-2 py-1 rounded-full bg-white/10 animate-pulse">
                                            Sedang berlangsung
                                        </span>
                                    ) : (
                                        <>
                                            <div className="text-2xl font-black leading-none font-mono">
                                                {cd.sisa_hari}
                                            </div>
                                            <div className="text-[10px] font-semibold opacity-70">
                                                hari lagi
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* ── HIGHLIGHT CARD (agenda terdekat) ── */}
                {highlightAgenda && (
                    <div
                        className="shrink-0 rounded-2xl border overflow-hidden"
                        style={{
                            borderColor: highlightAgenda.warna + '50',
                            background:  `linear-gradient(135deg, ${highlightAgenda.warna}18 0%, transparent 70%)`,
                        }}
                    >
                        <div className="p-4">
                            {/* Kategori badge + sisa hari */}
                            <div className="flex items-center justify-between mb-2">
                                <span
                                    className="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full"
                                    style={{
                                        background: highlightAgenda.warna + '25',
                                        color:      highlightAgenda.warna,
                                        border:     `1px solid ${highlightAgenda.warna}40`,
                                    }}
                                >
                                    <Tag size={10} />
                                    {highlightAgenda.kategori}
                                </span>

                                {/* Sisa hari */}
                                <span className="text-[11px] font-mono text-white/40">
                                    {highlightAgenda.is_ongoing
                                        ? '● Sedang berlangsung'
                                        : highlightAgenda.sisa_waktu_label
                                    }
                                </span>
                            </div>

                            {/* Judul */}
                            <h3 className="text-lg font-bold text-white leading-tight mb-2 line-clamp-2">
                                {highlightAgenda.judul}
                            </h3>

                            {/* Tanggal & Waktu */}
                            <div className="flex items-center gap-3 text-xs text-white/50 font-mono">
                                <div className="flex items-center gap-1.5">
                                    <CalendarDays size={12} style={{ color: highlightAgenda.warna }} />
                                    {highlightAgenda.date_full}
                                </div>
                                {!highlightAgenda.is_all_day && highlightAgenda.waktu_mulai && (
                                    <>
                                        <span className="text-white/20">•</span>
                                        <div className="flex items-center gap-1.5">
                                            <Clock size={12} style={{ color: highlightAgenda.warna }} />
                                            {highlightAgenda.waktu_mulai}
                                            {highlightAgenda.waktu_selesai && ` - ${highlightAgenda.waktu_selesai}`}
                                        </div>
                                    </>
                                )}
                            </div>

                            {/* Deskripsi singkat */}
                            {highlightAgenda.deskripsi && (
                                <p className="mt-2 text-xs text-white/40 line-clamp-2 leading-relaxed">
                                    {highlightAgenda.deskripsi}
                                </p>
                            )}
                        </div>

                        {/* Bottom accent bar */}
                        <div className="h-0.5 w-full" style={{ background: highlightAgenda.warna + '60' }} />
                    </div>
                )}

                {/* ── LIST AGENDA LAINNYA ── */}
               {otherAgendas.length > 0 && (
                    <div className="flex-1 min-h-0 flex flex-col overflow-hidden">
                        <div className="shrink-0 text-[10px] text-white/30 uppercase tracking-widest font-bold mb-2 pb-1.5 border-b border-white/5">
                            Agenda Selanjutnya
                        </div>
                        
                        {/* 🔥 WRAPPER BARU: Mengunci tinggi dan menyembunyikan scrollbar bawaan browser 🔥 */}
                        <div className="flex-1 min-h-0 overflow-hidden [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                            <AutoScrollList
                                data={otherAgendas}
                                threshold={4}
                                gap="gap-2"
                                renderItem={(agenda) => (
                                    <div className="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/5 border border-white/5 hover:bg-white/8 transition-all group">
                                        {/* Kotak tanggal */}
                                        <div
                                            className="shrink-0 flex flex-col items-center justify-center w-10 h-10 rounded-lg text-center"
                                            style={{
                                                background: agenda.warna + '20',
                                                border:     `1px solid ${agenda.warna}30`,
                                            }}
                                        >
                                            <span className="text-[9px] font-bold uppercase" style={{ color: agenda.warna }}>
                                                {agenda.date_month}
                                            </span>
                                            <span className="text-base font-black text-white leading-none">
                                                {agenda.date_day}
                                            </span>
                                        </div>

                                        {/* Detail */}
                                        <div className="flex-1 min-w-0">
                                            <div className="text-[10px] font-bold uppercase tracking-wider mb-0.5 truncate"
                                                style={{ color: agenda.warna }}>
                                                {agenda.kategori}
                                            </div>
                                            <div className="text-sm font-semibold text-white truncate leading-tight">
                                                {agenda.judul}
                                            </div>
                                            {!agenda.is_all_day && agenda.waktu_mulai && (
                                                <div className="text-[11px] text-white/30 font-mono mt-0.5">
                                                    {agenda.waktu_mulai}{agenda.waktu_selesai ? ` - ${agenda.waktu_selesai}` : ''}
                                                </div>
                                            )}
                                        </div>

                                        {/* Sisa hari */}
                                        <div className="shrink-0 text-right">
                                            <span className="text-[10px] font-mono text-white/30">
                                                {agenda.sisa_waktu_label}
                                            </span>
                                        </div>

                                        <ChevronRight size={14} className="shrink-0 text-white/20 group-hover:text-white/40 transition-colors" />
                                    </div>
                                )}
                            />
                        </div>
                    </div>
                )}

            </div>
        </GlassPanel>
    );
};

export default AgendaFakultasPanel;