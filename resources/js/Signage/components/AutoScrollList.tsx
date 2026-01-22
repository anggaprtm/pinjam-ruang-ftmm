import React, { useEffect, useState, ReactNode } from 'react';

interface AutoScrollListProps<T> {
    data: T[];
    renderItem: (item: T, index: number) => ReactNode; // Fungsi buat render tampilan per item
    threshold?: number; // Minimal item buat trigger scroll (Default 4)
    gap?: string; // Jarak antar item (Default gap-3)
    className?: string;
}

// Kita pakai Generic <T> biar bisa terima data tipe apa aja (Meeting, Lecture, Event, dll)
function AutoScrollList<T extends { id: number | string }>({ 
    data, 
    renderItem, 
    threshold = 4, 
    gap = 'gap-3',
    className = '' 
}: AutoScrollListProps<T>) {
    
    const [scrollingData, setScrollingData] = useState<T[]>([]);
    const shouldScroll = data.length > threshold;

    useEffect(() => {
        if (shouldScroll) {
            // Duplikasi data biar looping seamless
            setScrollingData([...data, ...data]);
        } else {
            setScrollingData(data);
        }
    }, [data, shouldScroll]);

    return (
        <div className={`relative h-full overflow-hidden flex flex-col px-1 ${className}`}>
            {data.length === 0 ? (
                <div className="flex flex-col items-center justify-center h-full text-slate-500 gap-2 opacity-50">
                    <span className="text-sm">Tidak ada data untuk ditampilkan.</span>
                </div>
            ) : (
                <div className={`
                    flex flex-col ${gap} py-1
                    ${shouldScroll ? 'animate-marquee-vertical hover:[animation-play-state:paused]' : ''}
                `}>
                    {scrollingData.map((item, index) => (
                        // WRAPPER PER ITEM
                        <div key={`${item.id}-${index}`}>
                            {renderItem(item, index)}
                        </div>
                    ))}
                </div>
            )}
            
            {/* Gradient Masking Atas Bawah biar makin sinematik */}
            {shouldScroll && (
                <>
                    <div className="absolute top-0 left-0 w-full h-8 bg-gradient-to-b from-navy-900 to-transparent z-10 pointer-events-none" />
                    <div className="absolute bottom-0 left-0 w-full h-8 bg-gradient-to-t from-navy-900 to-transparent z-10 pointer-events-none" />
                </>
            )}
        </div>
    );
}

export default AutoScrollList;