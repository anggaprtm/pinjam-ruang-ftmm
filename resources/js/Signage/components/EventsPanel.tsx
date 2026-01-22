import React from 'react';
import { Calendar, ArrowRight, MapPin, User, ImageOff } from 'lucide-react'; // Tambah icon ImageOff buat jaga-jaga
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';
import AutoScrollList from './AutoScrollList';

// --- KONFIGURASI GAMBAR DEFAULT ---
// Ganti URL ini dengan gambar default pilihanmu sendiri nanti
const DEFAULT_EVENT_IMAGE = 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; // Contoh: Cyberpunk Abstract

interface EventsPanelProps {
    data: AgendaItem[];
}

const EventsPanel: React.FC<EventsPanelProps> = ({ data }) => {
    const highlightEvent = data.length > 0 ? data[0] : null;
    const otherEvents = data.length > 1 ? data.slice(1) : [];

    // Helper function untuk menentukan gambar (biar kodingan rapi)
    const getEventImage = (imgSrc: string | undefined) => {
        // Cek jika null, undefined, string kosong, atau placeholder '...' dari controller
        if (!imgSrc || imgSrc === '...' || imgSrc.length < 5) {
            return DEFAULT_EVENT_IMAGE;
        }
        return imgSrc;
    };

    return (
        <GlassPanel title="AGENDA MENDATANG" icon={<Calendar className="w-6 h-6 text-electric-400" />} className="h-full bg-navy-900/80 border-white/10">
            <div className="flex flex-col h-full gap-4">
                
                {/* STATE KOSONG */}
                {data.length === 0 && (
                    <div className="flex-1 flex flex-col items-center justify-center text-white/30 text-center animate-pulse">
                        <Calendar className="w-16 h-16 mb-4 opacity-20" />
                        <p className="text-lg font-mono uppercase tracking-widest">No Events on this Floor</p>
                    </div>
                )}

                {/* 1. HIGHLIGHT CARD (STATIS) */}
                {highlightEvent && (
                    // REVISI 1: Ubah 'aspect-video' jadi 'h-52' (tinggi fix 13rem/208px) biar lebih kecil
                    <div className="relative group overflow-hidden rounded-2xl h-52 shrink-0 border border-white/10 shadow-2xl bg-navy-950">
                        {/* Background Image dengan Fallback */}
                        <img 
                            // REVISI 3: Pakai helper function untuk cek gambar
                            src={getEventImage(highlightEvent.image)}
                            alt={highlightEvent.title}
                            className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-60 mix-blend-overlay"
                            // Safety tambahan: Kalau URL gambar ternyata rusak/404, ganti ke default
                            onError={(e) => { e.currentTarget.src = DEFAULT_EVENT_IMAGE; }}
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-navy-900 via-navy-900/50 to-transparent" />
                        
                        <div className="absolute bottom-0 left-0 p-5 w-full z-10">
                            {/* REVISI 2: Badge Kategori Dinamis */}
                            <span className="inline-block px-3 py-1 mb-2 text-[10px] font-bold tracking-widest text-navy-900 uppercase bg-electric-400 rounded-full shadow-[0_0_15px_rgba(45,212,191,0.4)]">
                                {highlightEvent.category || 'EVENT'} {/* Pakai data asli */}
                            </span>
                            
                            <h3 className="text-xl md:text-2xl font-bold text-white mb-1 leading-tight max-w-lg drop-shadow-md line-clamp-2">
                                {highlightEvent.title}
                            </h3>
                            
                            <div className="flex items-center gap-3 mb-2 min-w-0">
                            {/* Speaker */}
                            {highlightEvent.speaker && (
                                <div className="flex items-center gap-2 text-gray-300 text-sm min-w-0">
                                <User className="w-3.5 h-3.5 text-electric-500 shrink-0" />
                                <span className="font-medium truncate">
                                    {highlightEvent.speaker}
                                </span>
                                </div>
                            )}

                            {/* Lokasi */}
                            {highlightEvent.location && (
                                <div className="flex items-center gap-2 text-gray-300 min-w-0">
                                    <MapPin className="w-3 h-3 shrink-0" />
                                    <span className="font-medium truncate">{highlightEvent.location || highlightEvent.room}</span>
                                </div>
                            )}
                            </div>

                            
                            <div className="flex items-center gap-3 text-gray-300 text-xs font-mono mt-2">
                                <span className="flex items-center gap-1.5 bg-black/30 px-2 py-1 rounded backdrop-blur-sm">
                                    <Calendar className="w-3 h-3 text-electric-500" />
                                    {highlightEvent.date_day} {highlightEvent.date_month} • {highlightEvent.time}
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* 2. LIST OF OTHER EVENTS (AUTO SCROLL) */}
                {otherEvents.length > 0 && (
                    <div className="flex-1 min-h-0 flex flex-col relative">
                        <h4 className="text-xs text-electric-400 uppercase tracking-[0.2em] font-bold mb-2 pb-2 border-b border-white/5 shrink-0">
                            Acara Selanjutnya
                        </h4>
                        
                        <AutoScrollList 
                            data={otherEvents}
                            threshold={5}
                            gap="gap-3"
                            renderItem={(event) => (
                                <div className="group flex items-center p-3 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-electric-500/30 transition-all">
                                    {/* Kotak Tanggal */}
                                    <div className="flex flex-col items-center justify-center w-12 h-12 rounded-lg bg-white/5 border border-white/10 mr-4 group-hover:bg-electric-500/10 group-hover:border-electric-500/50 transition-colors">
                                        <span className="text-[10px] font-bold text-gray-400 uppercase group-hover:text-electric-400">
                                            {event.date_month}
                                        </span>
                                        <span className="text-xl font-bold text-white leading-none group-hover:text-electric-500">
                                            {event.date_day}
                                        </span>
                                    </div>
                                    
                                    {/* Detail Event */}
                                    <div className="flex-1 min-w-0">
                                        {/* Kategori kecil di atas judul */}
                                        <div className="text-[10px] text-electric-500 mb-0.5 uppercase tracking-wider truncate">
                                            {event.category}
                                        </div>
                                        <h5 className="text-base font-bold text-white truncate group-hover:text-electric-400 transition-colors leading-tight">
                                            {event.title}
                                        </h5>
                                        <div className="flex items-center gap-3 mt-1">
                                            <p className="text-sm text-gray-400 flex items-center gap-1 font-mono">
                                                {event.time}
                                            </p>
                                            <span className="text-gray-600 text-[10px]">•</span>
                                            <p className="text-sm text-gray-400 flex items-center gap-1 truncate">
                                                <User className="w-3 h-3" />
                                                {event.speaker}
                                            </p>
                                            <span className="text-gray-600 text-[10px]">•</span>
                                            <p className="text-sm text-gray-400 flex items-center gap-1 truncate">
                                                <MapPin className="w-3 h-3" />
                                                {event.location || event.room}
                                            </p>
                                        </div>
                                    </div>
                                    <ArrowRight className="w-5 h-5 text-gray-600 group-hover:text-electric-400 transition-transform group-hover:translate-x-1" />
                                </div>
                            )}
                        />
                    </div>
                )}
            </div>
        </GlassPanel>
    );
};

export default EventsPanel;