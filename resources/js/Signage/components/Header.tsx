import React, { useState, useEffect } from 'react';
import { Cloud, Sun, CloudRain, CloudSnow, CloudLightning, Wind, Droplets, Thermometer } from 'lucide-react';

interface HeaderProps {
    customTitle?: string;
}

interface WeatherData {
    temp: number;
    feelsLike: number;
    humidity: number;
    windspeed: number;
    weatherCode: number;
    description: string;
}

const getWeatherInfo = (code: number): { label: string; icon: React.ReactNode; colorClass: string } => {
    if (code === 0)  return { label: 'Cerah',       icon: <Sun size={15} />,             colorClass: 'text-warning-600' };
    if (code <= 2)   return { label: 'Berawan',     icon: <Cloud size={15} />,           colorClass: 'text-ink-secondary' };
    if (code <= 3)   return { label: 'Mendung',     icon: <Cloud size={15} />,           colorClass: 'text-ink-secondary' };
    if (code <= 49)  return { label: 'Berkabut',    icon: <Wind size={15} />,            colorClass: 'text-ink-secondary' };
    if (code <= 59)  return { label: 'Gerimis',     icon: <CloudRain size={15} />,       colorClass: 'text-info-600' };
    if (code <= 69)  return { label: 'Hujan',       icon: <CloudRain size={15} />,       colorClass: 'text-info-600' };
    if (code <= 79)  return { label: 'Salju',       icon: <CloudSnow size={15} />,       colorClass: 'text-info-400' };
    if (code <= 84)  return { label: 'Hujan Lebat', icon: <CloudRain size={15} />,       colorClass: 'text-info-800' };
    if (code <= 99)  return { label: 'Badai Petir', icon: <CloudLightning size={15} />, colorClass: 'text-warning-600' };
    return                  { label: 'N/A',         icon: <Cloud size={15} />,           colorClass: 'text-ink-muted' };
};

const Header: React.FC<HeaderProps> = ({ customTitle }) => {
    const [time, setTime]         = useState(new Date());
    const [weather, setWeather]   = useState<WeatherData | null>(null);
    const [weatherError, setWeatherError] = useState(false);

    useEffect(() => {
        const timer = setInterval(() => setTime(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    useEffect(() => {
        const fetchWeather = async () => {
            try {
                const url =
                    'https://api.open-meteo.com/v1/forecast'
                    + '?latitude=-7.2575&longitude=112.7521'
                    + '&current=temperature_2m,apparent_temperature,relative_humidity_2m,wind_speed_10m,weather_code'
                    + '&timezone=Asia%2FJakarta';
                const res  = await fetch(url);
                const data = await res.json();
                const cur  = data.current;
                setWeather({
                    temp:        Math.round(cur.temperature_2m),
                    feelsLike:   Math.round(cur.apparent_temperature),
                    humidity:    cur.relative_humidity_2m,
                    windspeed:   Math.round(cur.wind_speed_10m),
                    weatherCode: cur.weather_code,
                    description: getWeatherInfo(cur.weather_code).label,
                });
                setWeatherError(false);
            } catch {
                setWeatherError(true);
            }
        };
        fetchWeather();
        const iv = setInterval(fetchWeather, 10 * 60 * 1000);
        return () => clearInterval(iv);
    }, []);

    const weatherInfo = weather ? getWeatherInfo(weather.weatherCode) : null;

    const pad = (n: number) => String(n).padStart(2, '0');

    return (
        /* ── Outer wrapper: gradient maroon ── */
        <div
            className="relative overflow-hidden rounded-2xl shadow-lg-brand"
            style={{ background: 'linear-gradient(135deg, #741847 0%, #9c2456 55%, #741847 100%)' }}
        >
            {/* Dekorasi lingkaran transparan */}
            <div className="absolute -top-10 -right-10 w-44 h-44 rounded-full bg-white/5 pointer-events-none" />
            <div className="absolute -bottom-16 left-[30%] w-56 h-56 rounded-full bg-white/[0.03] pointer-events-none" />

            <div className="relative z-10 flex items-center justify-between gap-6 px-6 py-3.5">

                {/* ── KIRI: Logo ── */}
                <div className="flex items-center gap-3 shrink-0">
                    <img
                        src="/images/logo-ftmm.png"
                        alt="Logo FTMM"
                        className="h-12 w-auto object-contain drop-shadow-md"
                        /* Fallback kalau logo belum ada */
                        onError={(e) => { e.currentTarget.style.display = 'none'; }}
                    />
                </div>

                {/* ── TENGAH: Lokasi + Cuaca ── */}
                <div className="flex flex-col items-center gap-1.5 flex-1 min-w-0">

                    {/* Location pill */}
                    <div className="bg-white/15 border border-white/20 backdrop-blur-sm px-5 py-1.5 rounded-full">
                        <span className="text-white/90 font-semibold text-sm tracking-wide">
                            {customTitle || 'Gedung Nano • Fakultas Teknologi Maju dan Multidisiplin'}
                        </span>
                    </div>

                    {/* Weather strip */}
                    {weather && weatherInfo && !weatherError && (
                        <div className="flex items-center gap-3 px-4 py-1.5 rounded-full bg-white/10 border border-white/15">

                            {/* Cuaca + ikon */}
                            <div className={`flex items-center gap-1.5 text-white/90 ${weatherInfo.colorClass}`}>
                                {weatherInfo.icon}
                                <span className="text-xs font-semibold text-white/90">{weather.description}</span>
                            </div>
                            <div className="w-px h-3 bg-white/20" />

                            {/* Suhu */}
                            <div className="flex items-center gap-1 text-white">
                                <Thermometer size={12} className="text-white/60" />
                                <span className="text-sm font-bold">{weather.temp}°C</span>
                                <span className="text-[11px] text-white/50">/ {weather.feelsLike}°C</span>
                            </div>
                            <div className="w-px h-3 bg-white/20" />

                            {/* Kelembaban */}
                            <div className="flex items-center gap-1 text-white/80">
                                <Droplets size={12} className="text-white/60" />
                                <span className="text-xs">{weather.humidity}%</span>
                            </div>
                            <div className="w-px h-3 bg-white/20" />

                            {/* Angin */}
                            <div className="flex items-center gap-1 text-white/80">
                                <Wind size={12} className="text-white/60" />
                                <span className="text-xs">{weather.windspeed} km/h</span>
                            </div>
                            <div className="w-px h-3 bg-white/20" />

                            <span className="text-[10px] text-white/40 font-mono uppercase tracking-widest">Surabaya</span>
                        </div>
                    )}

                    {weatherError && (
                        <div className="text-[10px] text-white/30 font-mono">cuaca tidak tersedia</div>
                    )}
                </div>

                {/* ── KANAN: Jam Digital ── */}
                <div className="text-right shrink-0">
                    <div className="text-4xl font-bold text-white tracking-tight font-mono leading-none">
                        {pad(time.getHours())}:{pad(time.getMinutes())}:{pad(time.getSeconds())} WIB
                    </div>
                    <div className="text-white/60 text-sm font-medium mt-1 uppercase tracking-wide">
                        {time.toLocaleDateString('id-ID', {
                            weekday: 'long',
                            year:    'numeric',
                            month:   'long',
                            day:     'numeric',
                        })}
                    </div>
                </div>

            </div>
        </div>
    );
};

export default Header;