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
  const [cars, setCars]     = useState<CarData[]>([]);
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
    <div className="shrink-0 rounded-2xl border border-surface-border bg-surface-0 shadow-card overflow-hidden">

      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 border-b border-surface-border bg-surface-1">
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 rounded-md bg-success-50 flex items-center justify-center">
            <Car className="w-3.5 h-3.5 text-success-600" />
          </div>
          <span className="text-[11px] font-extrabold tracking-widest text-ink-primary uppercase">
            Mobil Dinas
          </span>
        </div>
        <Activity size={13} className="text-success-600 animate-pulse" />
      </div>

      {/* Cars list */}
      <div className="flex flex-col divide-y divide-surface-border">
        {cars.map((car) => {
          const isOnDuty      = car.status === 'dipakai';
          const isMaintenance = car.status === 'maintenance';

          return (
            <div key={car.id} className={`
              relative px-4 transition-all duration-500
              ${isOnDuty ? 'py-3 bg-danger-50' : 'py-2.5 bg-surface-0'}
            `}>
              {/* Status accent bar */}
              <div className={`absolute left-0 top-0 bottom-0 w-0.5 ${
                isOnDuty      ? 'bg-danger-600'
                : isMaintenance ? 'bg-warning-600'
                :                 'bg-success-600'
              }`} />

              {/* Standby / Maintenance: compact */}
              {!isOnDuty && (
                <div className="flex items-center gap-3">
                  {/* Plat */}
                  <span className="shrink-0 font-mono font-extrabold tracking-widest text-xs
                    px-2 py-0.5 rounded bg-ink-primary text-white shadow-sm">
                    {car.plat}
                  </span>

                  {/* Nama */}
                  <span className="flex-1 text-sm font-semibold text-ink-primary truncate">
                    {car.nama}
                  </span>

                  {/* Badge */}
                  {isMaintenance ? (
                    <span className="flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-warning-50 text-warning-800 border border-warning-400/40">
                      <Wrench size={9} />
                      Service
                    </span>
                  ) : (
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-success-50 text-success-600 border border-success-400/30">
                      Stand By
                    </span>
                  )}
                </div>
              )}

              {/* On Duty: expanded */}
              {isOnDuty && (
                <div className="flex flex-col gap-1.5">
                  <div className="flex items-center gap-2">
                    <span className="shrink-0 font-mono font-extrabold tracking-widest text-xs
                      px-2 py-0.5 rounded bg-ink-primary text-white shadow-sm">
                      {car.plat}
                    </span>
                    <span className="flex-1 text-sm font-bold text-danger-800 truncate">{car.nama}</span>
                    <span className="shrink-0 flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-danger-50 text-danger-600 border border-danger-400/30 animate-pulse">
                      On Duty
                    </span>
                  </div>

                  {car.detail_trip?.driver && (
                    <div className="flex items-center gap-1.5 text-xs text-ink-secondary">
                      <User size={11} className="text-ink-muted shrink-0" />
                      <span className="truncate">{car.detail_trip.driver}</span>
                    </div>
                  )}

                  {car.detail_trip?.tujuan && (
                    <div className="flex items-start gap-1.5 text-xs">
                      <MapPin size={11} className="text-danger-600 shrink-0 mt-0.5" />
                      <span className="text-danger-700 font-semibold line-clamp-1 leading-snug">
                        {car.detail_trip.tujuan}
                      </span>
                    </div>
                  )}

                  {car.detail_trip?.keperluan?.trim() && (
                    <div className="text-[11px] text-ink-muted line-clamp-1 pl-4 italic">
                      "{car.detail_trip.keperluan}"
                    </div>
                  )}

                  {car.detail_trip?.mulai && (
                    <div className="flex items-center gap-1.5 text-[11px] text-ink-muted font-mono">
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