import React, { useState, useEffect } from 'react';

// 1. Definisikan tipe data props yang diterima
interface HeaderProps {
    customTitle?: string; // Tanda tanya (?) artinya boleh kosong/optional
}

// 2. JANGAN LUPA: Tambahkan ({ customTitle }) disini agar variabelnya bisa dipakai
const Header: React.FC<HeaderProps> = ({ customTitle }) => {
    
    // Logic jam digital biar jalan real-time
    const [time, setTime] = useState(new Date());

    useEffect(() => {
        const timer = setInterval(() => setTime(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    return (
        <div className="flex justify-between items-center mb-6 relative z-20">
            {/* LOGO KIRI */}
            <div className="flex items-center gap-4">
                {/* Logo Image */}
                <img 
                    src="/images/logo-ftmm.png" // Pastikan nama file sesuai
                    alt="Logo FTMM"
                    className="h-16 w-auto object-contain drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]"
                />
            </div>

            {/* TENGAH: LOKASI (DYNAMIC DARI URL) */}
            <div className="bg-navy-900/50 backdrop-blur-md border border-white/10 px-6 py-2 rounded-full shadow-2xl">
                <span className="text-electric-400 font-mono text-base tracking-wider flex items-center gap-2">
                    {/* Disini variabel customTitle dipanggil. Kalau kosong, pakai default. */}
                    {customTitle || "Gedung Nano • Fakultas Teknologi Maju dan Multidisiplin"}
                </span>
            </div>

            {/* KANAN: JAM DIGITAL */}
            <div className="text-right">
                <div className="text-4xl font-bold text-white tracking-tight font-mono leading-none">
                    {String(time.getHours()).padStart(2, '0')}:
                    {String(time.getMinutes()).padStart(2, '0')}:
                    {String(time.getSeconds()).padStart(2, '0')} WIB
                </div>
                <div className="text-blue-300 text-sm font-medium mt-1 uppercase tracking-wide">
                    {time.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                </div>
            </div>
        </div>
    );
};

export default Header;