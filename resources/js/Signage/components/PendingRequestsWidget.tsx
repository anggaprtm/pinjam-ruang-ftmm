import React, { useEffect, useState } from 'react';
import { Bell, Clock, User, Users } from 'lucide-react';
import AutoScrollList from './AutoScrollList';

interface RequestItem {
    id: number;
    kegiatan: string;
    pemohon: string;
    jumlah_peserta: number;
    waktu: string;
    jenis_layanan: string;
}

const PendingRequestsWidget: React.FC = () => {
    const [requests, setRequests] = useState<RequestItem[]>([]);
    const [loading, setLoading] = useState(true);

    const fetchRequests = async () => {
        try {
            const apiUrl = new URL('/api/v1/signage/requests', window.location.origin);
            const response = await fetch(apiUrl.toString());
            const data = await response.json();
            setRequests(data);
            setLoading(false);
        } catch (error) {
            console.error("Gagal load request:", error);
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchRequests();
        const interval = setInterval(fetchRequests, 30000); 
        return () => clearInterval(interval);
    }, []);

    if (loading) return null;
    if (requests.length === 0) return null; 

    return (
        <div className="rounded-3xl bg-navy-950/50 border border-amber-500/30 backdrop-blur-md overflow-hidden flex flex-col h-full shadow-[0_0_20px_rgba(245,158,11,0.15)]">
            {/* Header */}
            <div className="p-4 shrink-0 flex items-center justify-between border-b border-amber-500/20 bg-amber-900/10">
                 <div className="flex items-center gap-3">
                    <div className="p-2 rounded-xl bg-amber-500/20 text-amber-400 animate-pulse">
                        <Bell size={20} />
                    </div>
                    <div>
                        <h2 className="text-lg font-bold text-white tracking-wide leading-none">PERMINTAAN BARU</h2>
                        <span className="text-[10px] text-amber-400 font-mono">BUTUH RESPON</span>
                    </div>
                </div>
                <span className="bg-amber-500 text-navy-900 text-xs font-bold px-2 py-1 rounded-full">
                    {requests.length}
                </span>
            </div>

            {/* Content List */}
            <div className="flex-1 min-h-0 p-3"> 
                <AutoScrollList 
                    data={requests}
                    threshold={1}
                    gap="gap-3"
                    renderItem={(req) => (
                        <div className="relative w-full rounded-xl bg-navy-900/60 border border-white/5 p-3 overflow-hidden">
                            
                            {/* Top: Judul Kegiatan & Waktu */}
                            <div className="flex justify-between items-start mb-3">
                                <h4 className="font-bold text-sm text-white leading-tight line-clamp-2 flex-1 mr-2">
                                    {req.kegiatan}
                                </h4>
                                <div className="flex items-center gap-1 text-[10px] text-amber-400 font-mono shrink-0 bg-amber-900/30 px-2 py-1 rounded border border-amber-500/20">
                                    <Clock size={10} />
                                    <span>{req.waktu}</span>
                                </div>
                            </div>

                            {/* Bottom: Info Sebelah-sebelahan */}
                            <div className="flex flex-col gap-2 pt-2 border-t border-white/10">
                                {/* Baris 1: Pemohon */}
                                <div className="flex items-center text-xs">
                                    <div className="w-5 flex justify-center text-electric-400">
                                        <User size={14} />
                                    </div>
                                    <span className="text-gray-400 mr-1">Pemohon:</span>
                                    <span className="text-white font-medium truncate">{req.pemohon}</span>
                                </div>

                                {/* Baris 2: Kebutuhan Peserta */}
                                <div className="flex items-center text-xs">
                                    <div className="w-5 flex justify-center text-emerald-400">
                                        <Users size={14} />
                                    </div>
                                    <span className="text-gray-400 mr-1">Kebutuhan Peserta:</span>
                                    <span className="text-white font-bold">{req.jumlah_peserta ? `${req.jumlah_peserta} Orang` : '-'}</span>
                                </div>
                            </div>
                            
                            {/* Badge Jenis Layanan (Selalu Muncul & Terang) */}
                             <div className="absolute right-0 bottom-0">
                                <span className="text-[10px] font-bold px-3 py-1 bg-amber-500 text-navy-900 rounded-tl-xl rounded-br-xl shadow-lg">
                                    {req.jenis_layanan}
                                </span>
                            </div>

                        </div>
                    )}
                />
            </div>
        </div>
    );
};

export default PendingRequestsWidget;