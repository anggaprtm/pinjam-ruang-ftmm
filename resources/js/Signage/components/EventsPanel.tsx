// resources/js/Signage/components/EventsPanel.tsx

import React from 'react';
import { Calendar, ArrowRight, MapPin, User } from 'lucide-react';
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';

interface EventsPanelProps {
    data: AgendaItem[];
}

const EventsPanel: React.FC<EventsPanelProps> = ({ data }) => {
    // Logic: Event pertama jadi "Highlight", sisanya jadi "List"
    const highlightEvent = data.length > 0 ? data[0] : null;
    const otherEvents = data.length > 1 ? data.slice(1) : [];

    return (
        <GlassPanel title="AGENDA MENDATANG" icon={<Calendar className="w-6 h-6" />} className="h-full">
            <div className="flex flex-col h-full gap-6">
                
                {/* STATE KOSONG (JIKA TIDAK ADA DATA SETELAH FILTER) */}
                {data.length === 0 && (
                    <div className="flex-1 flex flex-col items-center justify-center text-white/30 text-center animate-pulse">
                        <Calendar className="w-16 h-16 mb-4 opacity-20" />
                        <p className="text-lg font-mono uppercase tracking-widest">No Events on this Floor</p>
                    </div>
                )}

                {/* 1. HIGHLIGHT CARD (Event Paling Dekat) */}
                {highlightEvent && (
                    <div className="relative group overflow-hidden rounded-2xl aspect-video shrink-0 border border-white/10 shadow-2xl">
                        {/* Background Image */}
                        <img 
                            src={highlightEvent.image} 
                            alt={highlightEvent.title}
                            className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-60 mix-blend-overlay"
                        />
                        {/* Gradient Overlay biar teks terbaca */}
                        <div className="absolute inset-0 bg-gradient-to-t from-navy-900 via-navy-900/50 to-transparent" />
                        
                        <div className="absolute bottom-0 left-0 p-6 w-full z-10">
                            {/* Category Badge */}
                            <span className="inline-block px-3 py-1 mb-3 text-[10px] font-bold tracking-widest text-navy-900 uppercase bg-electric-400 rounded-full shadow-[0_0_15px_rgba(45,212,191,0.4)]">
                                {highlightEvent.category || 'EVENT'}
                            </span>
                            
                            {/* Title */}
                            <h3 className="text-2xl md:text-3xl font-bold text-white mb-2 leading-tight max-w-lg drop-shadow-md">
                                {highlightEvent.title}
                            </h3>
                            
                            {/* Speaker */}
                            {highlightEvent.speaker && (
                                <div className="flex items-center gap-2 mb-2 text-electric-300">
                                    <User className="w-4 h-4" />
                                    <span className="font-medium">{highlightEvent.speaker}</span>
                                </div>
                            )}

                            {/* Waktu & Lokasi */}
                            <div className="flex items-center gap-4 text-gray-300 text-sm font-mono mt-3">
                                <span className="flex items-center gap-1.5 bg-black/30 px-2 py-1 rounded backdrop-blur-sm">
                                    <Calendar className="w-3.5 h-3.5 text-electric-500" />
                                    {highlightEvent.date_day} {highlightEvent.date_month} • {highlightEvent.time}
                                </span>
                                <span className="flex items-center gap-1.5 bg-black/30 px-2 py-1 rounded backdrop-blur-sm">
                                    <MapPin className="w-3.5 h-3.5 text-electric-500" />
                                    {highlightEvent.location || highlightEvent.room}
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* 2. LIST OF OTHER EVENTS (Next Up) */}
                {otherEvents.length > 0 && (
                    <div className="flex-1 space-y-3 overflow-y-auto pr-1 scrollbar-hide">
                        <h4 className="text-xs text-electric-400 uppercase tracking-[0.2em] font-bold mb-2 sticky top-0 bg-navy-900/90 backdrop-blur-md py-2 z-10 border-b border-white/5">
                            Next Up
                        </h4>
                        
                        {otherEvents.map((event, index) => (
                            <div key={index} className="group flex items-center p-3 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-electric-500/30 transition-all cursor-pointer">
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
                                    <h5 className="text-base font-bold text-white truncate group-hover:text-electric-400 transition-colors">
                                        {event.title}
                                    </h5>
                                    <div className="flex items-center gap-3 mt-1">
                                        <p className="text-xs text-gray-400 flex items-center gap-1 font-mono">
                                            {event.time}
                                        </p>
                                        <span className="text-gray-600 text-[10px]">•</span>
                                        <p className="text-xs text-gray-400 flex items-center gap-1">
                                            <MapPin className="w-3 h-3" />
                                            {event.location || event.room}
                                        </p>
                                    </div>
                                </div>
                                
                                <ArrowRight className="w-5 h-5 text-gray-600 group-hover:text-electric-400 transition-transform group-hover:translate-x-1" />
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </GlassPanel>
    );
};

export default EventsPanel;