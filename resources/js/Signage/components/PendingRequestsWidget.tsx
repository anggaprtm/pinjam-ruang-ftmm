import React, { useEffect, useState } from 'react';
import { Bell, Clock, User, Users } from 'lucide-react';
import AutoScrollList from './AutoScrollList';

const getSignageApiKey = () =>
    document.querySelector('meta[name="signage-api-key"]')?.getAttribute('content') || '';

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
    const [loading, setLoading]   = useState(true);

    const fetchRequests = async () => {
        try {
            const apiUrl = new URL('/api/v1/signage/requests', window.location.origin);
            const apiKey = getSignageApiKey();
            if (apiKey) apiUrl.searchParams.set('signage_key', apiKey);
            const response = await fetch(apiUrl.toString(), {
                headers: {
                    'Accept': 'application/json',
                    ...(apiKey ? { 'X-SIGNAGE-KEY': apiKey } : {}),
                },
            });
            if (!response.ok) throw new Error(`Signage API error: ${response.status}`);
            const data = await response.json();
            setRequests(data);
        } catch (error) {
            console.error('Gagal load request:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchRequests();
        const interval = setInterval(fetchRequests, 30000);
        return () => clearInterval(interval);
    }, []);

    if (loading || requests.length === 0) return null;

    return (
        <div className="rounded-2xl bg-surface-0 border border-warning-400/40 overflow-hidden flex flex-col h-full shadow-card">

            {/* Header */}
            <div className="p-4 shrink-0 flex items-center justify-between border-b border-warning-400/20 bg-warning-50">
                <div className="flex items-center gap-3">
                    <div className="p-2 rounded-xl bg-warning-400/20 text-warning-600">
                        <Bell size={18} className="animate-pulse" />
                    </div>
                    <div>
                        <h2 className="text-sm font-extrabold text-ink-primary tracking-wide leading-none">
                            PERMINTAAN BARU
                        </h2>
                        <span className="text-[10px] text-warning-600 font-mono font-bold">BUTUH RESPON</span>
                    </div>
                </div>
                <span className="bg-warning-600 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">
                    {requests.length}
                </span>
            </div>

            {/* List */}
            <div className="flex-1 min-h-0 p-3">
                <AutoScrollList
                    data={requests}
                    threshold={1}
                    gap="gap-2.5"
                    renderItem={(req) => (
                        <div className="relative w-full rounded-xl bg-surface-1 border border-surface-border p-3 overflow-hidden">

                            {/* Judul + Waktu */}
                            <div className="flex justify-between items-start mb-3">
                                <h4 className="font-bold text-sm text-ink-primary leading-tight line-clamp-2 flex-1 mr-2">
                                    {req.kegiatan}
                                </h4>
                                <div className="flex items-center gap-1 text-[10px] text-warning-600 font-mono shrink-0 bg-warning-50 px-2 py-1 rounded border border-warning-400/30">
                                    <Clock size={10} />
                                    <span>{req.waktu}</span>
                                </div>
                            </div>

                            {/* Info */}
                            <div className="flex flex-col gap-1.5 pt-2 border-t border-surface-border">
                                <div className="flex items-center text-xs">
                                    <div className="w-5 flex justify-center text-maroon-500">
                                        <User size={13} />
                                    </div>
                                    <span className="text-ink-muted mr-1">Pemohon:</span>
                                    <span className="text-ink-primary font-semibold truncate">{req.pemohon}</span>
                                </div>
                                <div className="flex items-center text-xs">
                                    <div className="w-5 flex justify-center text-success-600">
                                        <Users size={13} />
                                    </div>
                                    <span className="text-ink-muted mr-1">Peserta:</span>
                                    <span className="text-ink-primary font-bold">
                                        {req.jumlah_peserta ? `${req.jumlah_peserta} Orang` : '-'}
                                    </span>
                                </div>
                            </div>

                            {/* Badge jenis layanan */}
                            <div className="absolute right-0 bottom-0">
                                <span className="text-[10px] font-bold px-3 py-1 bg-maroon-600 text-white rounded-tl-xl rounded-br-xl shadow-sm">
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