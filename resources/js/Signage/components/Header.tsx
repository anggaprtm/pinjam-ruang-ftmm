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

// Open-Meteo WMO Weather Code → label & icon
const getWeatherInfo = (code: number): { label: string; icon: React.ReactNode; color: string } => {
    if (code === 0)                    return { label: 'Cerah',         icon: <Sun size={18} />,             color: 'text-yellow-300' };
    if (code <= 2)                     return { label: 'Berawan',       icon: <Cloud size={18} />,           color: 'text-slate-300'  };
    if (code <= 3)                     return { label: 'Mendung',       icon: <Cloud size={18} />,           color: 'text-slate-400'  };
    if (code <= 49)                    return { label: 'Berkabut',      icon: <Wind size={18} />,            color: 'text-slate-400'  };
    if (code <= 59)                    return { label: 'Gerimis',       icon: <CloudRain size={18} />,       color: 'text-blue-300'   };
    if (code <= 69)                    return { label: 'Hujan',         icon: <CloudRain size={18} />,       color: 'text-blue-400'   };
    if (code <= 79)                    return { label: 'Salju',         icon: <CloudSnow size={18} />,       color: 'text-cyan-200'   };
    if (code <= 84)                    return { label: 'Hujan Lebat',   icon: <CloudRain size={18} />,       color: 'text-blue-500'   };
    if (code <= 99)                    return { label: 'Badai Petir',   icon: <CloudLightning size={18} />, color: 'text-yellow-400' };
    return                                    { label: 'Tidak diketahui', icon: <Cloud size={18} />,         color: 'text-slate-400'  };
};

const Header: React.FC<HeaderProps> = ({ customTitle }) => {
    const [time, setTime]       = useState(new Date());
    const [weather, setWeather] = useState<WeatherData | null>(null);
    const [weatherError, setWeatherError] = useState(false);

    // Update jam setiap detik
    useEffect(() => {
        const timer = setInterval(() => setTime(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    // Fetch cuaca dari Open-Meteo (Surabaya: lat=-7.2575, lon=112.7521)
    useEffect(() => {
        const fetchWeather = async () => {
            try {
                const url = 'https://api.open-meteo.com/v1/forecast'
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
        // Refresh cuaca setiap 10 menit
        const iv = setInterval(fetchWeather, 10 * 60 * 1000);
        return () => clearInterval(iv);
    }, []);

    const weatherInfo = weather ? getWeatherInfo(weather.weatherCode) : null;

    return (
        <div className="flex justify-between items-center mb-2 relative z-20 gap-4">

            {/* ── KIRI: Logo ── */}
            <div className="flex items-center gap-4 shrink-0">
                <img
                    src="/images/logo-ftmm.png"
                    alt="Logo FTMM"
                    className="h-14 w-auto object-contain drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]"
                />
            </div>

            {/* ── TENGAH: Lokasi + Cuaca ── */}
            <div className="flex flex-col items-center gap-1.5 flex-1 min-w-0">
                {/* Location pill */}
                <div className="bg-navy-900/50 backdrop-blur-md border border-white/10 px-5 py-1.5 rounded-full shadow-xl">
                    <span className="text-electric-400 font-mono text-sm tracking-wider">
                        {customTitle || 'Gedung Nano • Fakultas Teknologi Maju dan Multidisiplin'}
                    </span>
                </div>

                {/* Weather strip */}
                {weather && weatherInfo && !weatherError && (
                    <div className="flex items-center gap-4 px-4 py-1 rounded-full bg-white/5 border border-white/8 backdrop-blur-sm">
                        {/* Icon + kondisi */}
                        <div className={`flex items-center gap-1.5 ${weatherInfo.color}`}>
                            {weatherInfo.icon}
                            <span className="text-xs font-semibold">{weather.description}</span>
                        </div>

                        <div className="w-px h-3 bg-white/20" />

                        {/* Suhu */}
                        <div className="flex items-center gap-1 text-white">
                            <Thermometer size={13} className="text-orange-400" />
                            <span className="text-sm font-bold">{weather.temp}°C</span>
                            <span className="text-[11px] text-white/40">/ feels {weather.feelsLike}°C</span>
                        </div>

                        <div className="w-px h-3 bg-white/20" />

                        {/* Kelembaban */}
                        <div className="flex items-center gap-1 text-white/70">
                            <Droplets size={13} className="text-blue-400" />
                            <span className="text-xs">{weather.humidity}%</span>
                        </div>

                        <div className="w-px h-3 bg-white/20" />

                        {/* Angin */}
                        <div className="flex items-center gap-1 text-white/70">
                            <Wind size={13} className="text-slate-400" />
                            <span className="text-xs">{weather.windspeed} km/h</span>
                        </div>

                        {/* Kota */}
                        <div className="w-px h-3 bg-white/20" />
                        <span className="text-[10px] text-white/30 font-mono uppercase tracking-widest">Surabaya</span>
                    </div>
                )}

                {/* Fallback kalau cuaca gagal load */}
                {weatherError && (
                    <div className="text-[10px] text-white/20 font-mono">cuaca tidak tersedia</div>
                )}
            </div>

            {/* ── KANAN: Jam Digital ── */}
            <div className="text-right shrink-0">
                <div className="text-4xl font-bold text-white tracking-tight font-mono leading-none">
                    {String(time.getHours()).padStart(2, '0')}:
                    {String(time.getMinutes()).padStart(2, '0')}:
                    {String(time.getSeconds()).padStart(2, '0')} WIB
                </div>
                <div className="text-blue-300 text-sm font-medium mt-1 uppercase tracking-wide">
                    {time.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year:    'numeric',
                        month:   'long',
                        day:     'numeric',
                    })}
                </div>
            </div>

        </div>
    );
};

export default Header;