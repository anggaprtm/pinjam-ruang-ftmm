import React, { useEffect, useState } from 'react';
import { Car, MapPin, User, Clock, Activity, Wrench } from 'lucide-react';

const getSignageApiKey = () =>
  document.querySelector('meta[name="signage-api-key"]')?.getAttribute('content') || '';

interface CarData {
  id: number;
  nama: string;
  plat: string;
  status: 'tersedia' | 'dipakai' | 'maintenance';
  detail_trip?: {
    driver: string;
    tujuan: string;
    mulai: string;
    keperluan?: string;
  };
}

const CarStatusWidget: React.FC = () => {
  const [cars, setCars] = useState<CarData[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchCars = async () => {
    try {
      const apiUrl = new URL('/api/v1/signage/cars', window.location.origin);
      const apiKey = getSignageApiKey();
      if (apiKey) apiUrl.searchParams.set('signage_key', apiKey);

      const response = await fetch(apiUrl.toString(), {
        headers: {
          'Accept': 'application/json',
          ...(apiKey ? { 'X-SIGNAGE-KEY': apiKey } : {}),
        },
      });

      if (!response.ok) throw new Error(`API error: ${response.status}`);
      const data = await response.json();
      setCars(data);
    } catch (error) {
      console.error('Gagal memuat data mobil', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchCars();
    const interval = setInterval(fetchCars, 30000);
    return () => clearInterval(interval);
  }, []);

  if (loading || cars.length === 0) return null;

  return (
    <div className="shrink-0 rounded-2xl border border-white/10 bg-navy-900/60 backdrop-blur-xl overflow-hidden">
      
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 border-b border-white/5">
        <div className="flex items-center gap-2">
          <Car className="w-4 h-4 text-emerald-400" />
          <span className="text-xs font-bold tracking-widest text-white/70 uppercase">
            Mobil Dinas
          </span>
        </div>
        <Activity size={14} className="text-emerald-500 animate-pulse" />
      </div>

      {/* Cars List */}
      <div className="flex flex-col divide-y divide-white/5">
        {cars.map((car) => {
          const isOnDuty     = car.status === 'dipakai';
          const isMaintenance = car.status === 'maintenance';

          return (
            <div key={car.id} className={`
              relative px-4 transition-all duration-500
              ${isOnDuty ? 'py-3' : 'py-2.5'}
            `}>
              {/* Status accent bar */}
              <div className={`absolute left-0 top-0 bottom-0 w-0.5 ${
                isOnDuty      ? 'bg-red-500'     :
                isMaintenance ? 'bg-amber-500'   :
                                'bg-emerald-500'
              }`} />

              {/* ── STANDBY / MAINTENANCE: compact 1 baris ── */}
              {!isOnDuty && (
                <div className="flex items-center gap-3">
                  {/* Plat */}
                  <span className="
                    shrink-0 font-mono font-extrabold tracking-[0.18em]
                    text-xs px-2 py-0.5 rounded
                    bg-white text-black
                    shadow-sm border border-black/10
                  ">
                    {car.plat}
                  </span>

                  {/* Nama */}
                  <span className="flex-1 text-sm font-semibold text-white/80 truncate">
                    {car.nama}
                  </span>

                  {/* Badge */}
                  {isMaintenance ? (
                    <span className="flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                      <Wrench size={9} />
                      Service
                    </span>
                  ) : (
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/20">
                      Stand By
                    </span>
                  )}
                </div>
              )}

              {/* ── ON DUTY: expanded ── */}
              {isOnDuty && (
                <div className="flex flex-col gap-2">
                  {/* Baris atas: Plat + Nama + Badge */}
                  <div className="flex items-center gap-2">
                    <span className="
                      shrink-0 font-mono font-extrabold tracking-[0.18em]
                      text-xs px-2 py-0.5 rounded
                      bg-white text-black
                      shadow-sm border border-black/10
                    ">
                      {car.plat}
                    </span>
                    <span className="flex-1 text-sm font-bold text-red-100 truncate">
                      {car.nama}
                    </span>
                    <span className="shrink-0 flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-red-500/20 text-red-300 border border-red-500/30 animate-pulse">
                      On Duty
                    </span>
                  </div>

                  {/* Driver */}
                  {car.detail_trip?.driver && (
                    <div className="flex items-center gap-1.5 text-xs text-white/70">
                      <User size={11} className="text-white/40 shrink-0" />
                      <span className="truncate">{car.detail_trip.driver}</span>
                    </div>
                  )}

                  {/* Tujuan */}
                  {car.detail_trip?.tujuan && (
                    <div className="flex items-start gap-1.5 text-xs">
                      <MapPin size={11} className="text-red-400 shrink-0 mt-0.5" />
                      <span className="text-red-200 font-semibold line-clamp-1 leading-snug">
                        {car.detail_trip.tujuan}
                      </span>
                    </div>
                  )}

                  {/* Keperluan */}
                  {car.detail_trip?.keperluan?.trim() && (
                    <div className="text-[11px] text-white/50 line-clamp-1 pl-4 italic">
                      "{car.detail_trip.keperluan}"
                    </div>
                  )}

                  {/* Jam berangkat */}
                  {car.detail_trip?.mulai && (
                    <div className="flex items-center gap-1.5 text-[11px] text-white/40 font-mono">
                      <Clock size={10} className="shrink-0" />
                      Berangkat {car.detail_trip.mulai}
                    </div>
                  )}
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default CarStatusWidget;