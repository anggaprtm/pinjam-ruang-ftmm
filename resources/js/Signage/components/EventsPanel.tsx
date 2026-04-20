import React from 'react';
import { Calendar, ArrowRight, MapPin, User, ImageOff } from 'lucide-react';
import GlassPanel from './GlassPanel';
import { AgendaItem } from '../types';
import AutoScrollList from './AutoScrollList';

const DEFAULT_EVENT_IMAGE = 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop';

interface EventsPanelProps {
    data: AgendaItem[];
}

const EventsPanel: React.FC<EventsPanelProps> = ({ data }) => {
    const highlightEvent = data.length > 0 ? data[0] : null;
    const otherEvents    = data.length > 1 ? data.slice(1) : [];

    const getEventImage = (imgSrc: string | undefined) => {
        if (!imgSrc || imgSrc === '...' || imgSrc.length < 5) return DEFAULT_EVENT_IMAGE;
        return imgSrc;
    };

    return (
        <GlassPanel
            title="AGENDA MENDATANG"
            icon={<Calendar className="w-5 h-5" />}
            className="h-full"
        >
            <div className="flex flex-col h-full gap-4">

                {/* Empty state */}
                {data.length === 0 && (
                    <div className="flex-1 flex flex-col items-center justify-center text-ink-muted text-center">
                        <Calendar className="w-14 h-14 mb-4 opacity-20" />
                        <p className="text-sm font-mono uppercase tracking-widest">Tidak ada agenda.</p>
                    </div>
                )}

                {/* Highlight card */}
                {highlightEvent && (
                    <div className="relative group overflow-hidden rounded-2xl h-48 shrink-0 border border-surface-border shadow-card">
                        {/* Background image */}
                        <img
                            src={getEventImage(highlightEvent.image)}
                            alt={highlightEvent.title}
                            className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                            onError={(e) => { e.currentTarget.src = DEFAULT_EVENT_IMAGE; }}
                        />
                        {/* Overlay gradient maroon */}
                        <div className="absolute inset-0 bg-gradient-to-t from-maroon-900/90 via-maroon-800/40 to-transparent" />

                        <div className="absolute bottom-0 left-0 p-4 w-full z-10">
                            {/* Kategori badge */}
                            <span className="inline-block px-3 py-1 mb-2 text-[10px] font-bold tracking-widest text-white uppercase bg-maroon-600 rounded-full shadow-sm-brand">
                                {highlightEvent.category || 'EVENT'}
                            </span>

                            <h3 className="text-xl font-bold text-white mb-1 leading-tight max-w-lg drop-shadow line-clamp-2">
                                {highlightEvent.title}
                            </h3>

                            <div className="flex items-center gap-3 mb-2 min-w-0">
                                {highlightEvent.speaker && (
                                    <div className="flex items-center gap-1.5 text-white/80 text-sm min-w-0">
                                        <User className="w-3.5 h-3.5 text-white/60 shrink-0" />
                                        <span className="font-medium truncate">{highlightEvent.speaker}</span>
                                    </div>
                                )}
                                {highlightEvent.location && (
                                    <div className="flex items-center gap-1.5 text-white/80 text-sm min-w-0">
                                        <MapPin className="w-3 h-3 text-white/60 shrink-0" />
                                        <span className="truncate">{highlightEvent.location || highlightEvent.room}</span>
                                    </div>
                                )}
                            </div>

                            <div className="flex items-center gap-2 text-white/70 text-xs font-mono">
                                <span className="flex items-center gap-1.5 bg-black/30 px-2 py-1 rounded backdrop-blur-sm">
                                    <Calendar className="w-3 h-3 text-white/60" />
                                    {highlightEvent.date_day} {highlightEvent.date_month} • {highlightEvent.time}
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* List event lainnya */}
                {otherEvents.length > 0 && (
                    <div className="flex-1 min-h-0 flex flex-col relative">
                        <h4 className="text-[10px] text-ink-muted uppercase tracking-widest font-bold mb-2 pb-1.5 border-b border-surface-border shrink-0">
                            Acara Selanjutnya
                        </h4>

                        <AutoScrollList
                            data={otherEvents}
                            threshold={2}
                            gap="gap-2.5"
                            renderItem={(event) => (
                                <div className="group flex items-center p-3 rounded-xl bg-surface-1 border border-surface-border hover:bg-maroon-50 hover:border-maroon-200 transition-all">
                                    {/* Kotak Tanggal */}
                                    <div className="flex flex-col items-center justify-center w-11 h-11 rounded-lg bg-maroon-50 border border-maroon-100 mr-3 group-hover:bg-maroon-100 transition-colors shrink-0">
                                        <span className="text-[9px] font-bold text-maroon-500 uppercase">
                                            {event.date_month}
                                        </span>
                                        <span className="text-lg font-bold text-maroon-700 leading-none">
                                            {event.date_day}
                                        </span>
                                    </div>

                                    {/* Detail Event */}
                                    <div className="flex-1 min-w-0">
                                        <div className="text-[10px] text-maroon-500 mb-0.5 uppercase tracking-wider truncate font-bold">
                                            {event.category}
                                        </div>
                                        <h5 className="text-sm font-bold text-ink-primary truncate group-hover:text-maroon-700 transition-colors leading-tight">
                                            {event.title}
                                        </h5>
                                        <div className="flex items-center gap-2 mt-0.5 text-xs text-ink-secondary">
                                            <span className="font-mono">{event.time}</span>
                                            {event.speaker && (
                                                <>
                                                    <span className="text-surface-3">•</span>
                                                    <span className="flex items-center gap-1 truncate">
                                                        <User className="w-3 h-3 shrink-0" />
                                                        {event.speaker}
                                                    </span>
                                                </>
                                            )}
                                            {(event.location || event.room) && (
                                                <>
                                                    <span className="text-surface-3">•</span>
                                                    <span className="flex items-center gap-1 truncate">
                                                        <MapPin className="w-3 h-3 shrink-0" />
                                                        {event.location || event.room}
                                                    </span>
                                                </>
                                            )}
                                        </div>
                                    </div>

                                    <ArrowRight className="w-4 h-4 text-ink-muted group-hover:text-maroon-500 transition-all group-hover:translate-x-0.5 shrink-0 ml-1" />
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