import React, { useEffect, useState } from 'react';
import { Car, MapPin, User, Clock, Activity } from 'lucide-react';
import GlassPanel from './GlassPanel';

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
      const response = await fetch(apiUrl.toString());
      const data = await response.json();
      setCars(data);
      setLoading(false);
    } catch (error) {
      console.error('Gagal memuat data mobil', error);
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchCars();
    const interval = setInterval(fetchCars, 30000);
    return () => clearInterval(interval);
  }, []);

  if (loading) {
    return (
      <GlassPanel
        title="STATUS MOBIL DINAS"
        icon={<Car className="w-6 h-6 text-emerald-400" />}
        className="h-full bg-navy-900/80 border-white/10"
      >
        <div className="text-white/40 animate-pulse">Loading status...</div>
      </GlassPanel>
    );
  }

  if (cars.length === 0) return null;

  return (
    <GlassPanel
      title="STATUS MOBIL DINAS"
      icon={<Car className="w-6 h-6 text-emerald-400" />}
      className="h-full bg-navy-900/80 border-white/10"
      right={<Activity size={18} className="text-emerald-500 animate-pulse" />}
    >
      {/* wrapper: NO SCROLLBAR */}
      <div className="flex flex-col gap-3 overflow-hidden flex-1 min-h-0">
        {cars.map((car) => (
          <div
            key={car.id}
            className={`
              relative w-full rounded-xl border transition-all duration-500 overflow-hidden group
              ${car.status === 'dipakai'
                ? 'bg-navy-900/80 border-red-500/40 shadow-[0_0_10px_rgba(239,68,68,0.20)]'
                : 'bg-navy-900/40 border-emerald-500/30 hover:border-emerald-500/50'
              }
            `}
          >
            {/* Status Indicator Line */}
            <div
              className={`absolute left-0 top-0 bottom-0 w-1 ${
                car.status === 'dipakai'
                  ? 'bg-red-500 animate-pulse'
                  : 'bg-emerald-500'
              }`}
            />

            <div className="p-4 pl-5">
              {/* HEADER */}
              <div className="flex items-start justify-between gap-3 mb-3">
                <div className="min-w-0">
                  <h4
                    className={`font-extrabold text-base leading-tight tracking-wide ${
                      car.status === 'dipakai'
                        ? 'text-red-50'
                        : 'text-emerald-50'
                    }`}
                  >
                    {car.nama}
                  </h4>

                  {/* Plat + Driver sejajar */}
                  <div className="mt-2 flex items-center gap-3 min-w-0">
                    {/* PLAT BADGE */}
                    <span
                      className="
                        inline-flex items-center shrink-0
                        rounded-md px-2.5 py-1
                        bg-white text-black
                        font-extrabold tracking-[0.22em]
                        text-xs sm:text-sm
                        shadow-[0_6px_18px_rgba(0,0,0,0.35)]
                        border border-black/20
                      "
                      style={{
                        fontFamily:
                          'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
                      }}
                    >
                      {car.plat}
                    </span>

                    {/* DRIVER (sebelah plat) */}
                    {car.status === 'dipakai' && car.detail_trip?.driver ? (
                      <div className="flex items-center gap-2 min-w-0">
                        <User
                          size={14}
                          className="text-electric-400 shrink-0"
                        />
                        <span className="text-slate-200 font-semibold truncate text-sm">
                          {car.detail_trip.driver}
                        </span>
                      </div>
                    ) : null}
                  </div>
                </div>

                {/* STATUS BADGE */}
                {car.status === 'dipakai' ? (
                  <span className="shrink-0 text-[11px] font-extrabold px-3 py-1 rounded-full bg-red-500/20 text-red-200 border border-red-500/25 shadow-[0_0_18px_rgba(239,68,68,0.15)]">
                    ON DUTY
                  </span>
                ) : (
                  <span className="shrink-0 text-[11px] font-extrabold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-500/25 shadow-[0_0_18px_rgba(16,185,129,0.12)]">
                    STAND BY
                  </span>
                )}
              </div>

              {/* CONTENT */}
              {car.status === 'dipakai' && car.detail_trip ? (
                <div className="grid grid-cols-1 gap-2 pt-3 border-t border-white/10">
                  {/* TUJUAN */}
                  <div className="flex items-center gap-2 text-sm">
                    <MapPin size={14} className="text-red-400 shrink-0" />
                    <span className="text-red-100 font-extrabold truncate tracking-wide line-clamp-1">
                      {car.detail_trip.tujuan}
                    </span>
                  </div>

                  {/* KEPERLUAN */}
                  <div className="flex items-start gap-2 text-sm leading-snug">
                    <span className="mt-[2px] inline-flex h-5 w-5 items-center justify-center rounded-md bg-white/5 border border-white/10 text-white/70 text-[11px] shrink-0">
                      i
                    </span>

                    <div className="min-w-0">
                      <div className="text-[11px] text-white/40 font-semibold uppercase tracking-widest">
                        Keperluan
                      </div>

                      <div className="text-slate-100 font-semibold line-clamp-2">
                        {car.detail_trip.keperluan?.trim()
                          ? car.detail_trip.keperluan
                          : '-'}
                      </div>
                    </div>
                  </div>

                  {/* JAM BERANGKAT */}
                  <div className="mt-1 flex items-center justify-between gap-3 rounded-xl px-3 py-2 bg-navy-950/40 border border-white/10">
                    <div className="flex items-center gap-2 text-white/70">
                      <Clock size={14} className="shrink-0" />
                      <span className="text-[12px] font-semibold tracking-wide">
                        Berangkat
                      </span>
                    </div>

                    <div className="text-base font-extrabold text-white tracking-[0.15em] font-mono">
                      {car.detail_trip.mulai}
                    </div>
                  </div>
                </div>
              ) : (
                // READY STATE (lebih ringkas biar panel gak tinggi)
                <div className="pt-3 border-t border-white/10 flex items-center justify-between">
                  <span className="text-sm text-white/45">
                    Tidak ada pemakaian aktif
                  </span>
                </div>
              )}
            </div>
          </div>
        ))}
      </div>
    </GlassPanel>
  );
};

export default CarStatusWidget;
