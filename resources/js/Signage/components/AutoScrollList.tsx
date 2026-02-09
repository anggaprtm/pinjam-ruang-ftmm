import React, { useEffect, useState, ReactNode } from 'react';

interface AutoScrollListProps<T> {
    data: T[];
    renderItem: (item: T, index: number) => ReactNode;
    threshold?: number;
    gap?: string;
    className?: string;
    speedPerItem?: number; // Tambahan: Kecepatan per item dalam detik
}

function AutoScrollList<T extends { id: number | string }>({ 
    data, 
    renderItem, 
    threshold = 4, 
    gap = 'gap-3',
    className = '',
    speedPerItem = 4 // Default: 4 detik per item (Cukup lambat & enak dibaca)
}: AutoScrollListProps<T>) {
    
    const [scrollingData, setScrollingData] = useState<T[]>([]);
    const shouldScroll = data.length > threshold;

    useEffect(() => {
        if (shouldScroll) {
            setScrollingData([...data, ...data]);
        } else {
            setScrollingData(data);
        }
    }, [data, shouldScroll]);

    // LOGIC BARU: Hitung total durasi berdasarkan jumlah data
    // Misal: 10 item * 4 detik = 40 detik durasi total.
    // Misal: 50 item * 4 detik = 200 detik durasi total.
    // Hasilnya: Kecepatan gulir visualnya akan SELALU SAMA, berapapun datanya.
    const durationStyle = shouldScroll 
        ? { animationDuration: `${data.length * speedPerItem}s` } 
        : {};

    return (
        <div className={`relative h-full overflow-hidden flex flex-col px-1 ${className}`}>
            {data.length === 0 ? (
                <div className="flex flex-col items-center justify-center h-full text-slate-500 gap-2 opacity-50">
                    <span className="text-sm">Tidak ada data untuk ditampilkan.</span>
                </div>
            ) : (
                <div 
                    className={`
                        flex flex-col ${gap} py-1
                        ${shouldScroll ? 'animate-marquee-vertical hover:[animation-play-state:paused]' : ''}
                    `}
                    style={durationStyle} // Terapkan durasi dinamis disini
                >
                    {scrollingData.map((item, index) => (
                        <div key={`${item.id}-${index}`}>
                            {renderItem(item, index)}
                        </div>
                    ))}
                </div>
            )}
            
            {shouldScroll && (
                <>
                    <div className="absolute top-0 left-0 w-full h-12 bg-gradient-to-b from-slate-900 via-slate-900/80 to-transparent z-10 pointer-events-none" />
                    <div className="absolute bottom-0 left-0 w-full h-12 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent z-10 pointer-events-none" />
                </>
            )}
        </div>
    );
}

export default AutoScrollList;