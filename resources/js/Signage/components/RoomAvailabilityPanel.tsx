import React from 'react';
import GlassPanel from './GlassPanel';
import { DoorOpen, Users, Minus } from 'lucide-react';

interface RoomData {
  id: number;
  nama: string;
  kapasitas: number;
  status: 'kosong' | 'dipakai';
  current_event?: string | null;
}

interface Props {
  data: RoomData[];
}

const RoomAvailabilityPanel: React.FC<Props> = ({ data }) => {
  const totalRooms = data.length;

  return (
    <GlassPanel 
      title={`LIVE KETERSEDIAAN RUANG (${totalRooms})`} 
      icon={<DoorOpen className="w-6 h-6 text-emerald-400" />} 
      className="h-full bg-navy-900/80 border-white/10"
    >
      
      {totalRooms === 0 ? (
        <div className="flex-1 flex flex-col items-center justify-center text-white/20 h-full">
          <Minus className="w-10 h-10 mb-2 opacity-30" />
          <p className="text-xs font-mono">TIDAK ADA DATA RUANGAN</p>
        </div>
      ) : (
        <div className="flex-1 flex flex-col overflow-hidden h-full">
          
          {/* Legend Minimalis */}
          <div className="flex items-center gap-3 mb-3 shrink-0 px-1 border-b border-white/5 pb-2">
            <div className="flex items-center gap-1.5 text-[10px] xl:text-xs font-mono text-white/60">
              <span className="w-3 h-3 rounded-sm bg-emerald-500/20 border border-emerald-500/50"></span>
              <span>Tersedia</span>
            </div>
            <div className="flex items-center gap-1.5 text-[10px] xl:text-xs font-mono text-white/60">
              <span className="w-3 h-3 rounded-sm bg-rose-500/20 border border-rose-500/50 relative overflow-hidden">
                <span className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTAgNDBsNDAtNDBIMzBMMCAzMHYxMHptNDAgMEw0MCAzMCAwIDQwaDQwIiBzdHJva2U9InJnYmEoMjQ0LCA2MywgOTQsIDAuMDUpIiBzdHJva2U9IndpZHRoPSIyIiBmaWxsPSJub25lIi8+PC9zdmc+')] opacity-20"></span>
              </span>
              <span>Digunakan</span>
            </div>
          </div>

          {/* 🔥 GRID LOCKED MAX 6 KOLOM 🔥 */}
          <div className="flex-1 overflow-hidden min-h-0 pr-1 pb-1">
            <div className="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2.5 xl:gap-3">
              
              {data.map((room) => {
                const isOccupied = room.status === 'dipakai';
                
                return (
                  <div 
                    key={room.id} 
                    className={`relative p-2.5 xl:p-3 rounded-lg flex flex-col justify-between border transition-all duration-300 min-h-[64px] xl:min-h-[72px] ${
                      isOccupied 
                        ? 'bg-rose-900/20 border-rose-500/30 overflow-hidden' 
                        : 'bg-emerald-900/10 border-emerald-500/20'
                    }`}
                  >
                    {/* Pattern Background Minimalis */}
                    {isOccupied && (
                      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTAgNDBsNDAtNDBIMzBMMCAzMHYxMHptNDAgMEw0MCAzMCAwIDQwaDQwIiBzdHJva2U9InJnYmEoMjQ0LCA2MywgOTQsIDAuMDIpIiBzdHJva2U9IndpZHRoPSIyIiBmaWxsPSJub25lIi8+PC9zdmc+')] opacity-30 pointer-events-none"></div>
                    )}

                    {/* Row 1: Nama Ruang & Kapasitas */}
                    <div className="flex items-start justify-between z-10 w-full gap-2 mb-1.5">
                      
                      {/* Teks diperbesar ke text-sm agar terbaca di TV, truncate tetap ada sebagai pengaman untuk nama ruang yang super panjang */}
                      <span className={`font-bold text-xs xl:text-sm tracking-tight truncate flex-1 min-w-0 ${isOccupied ? 'text-rose-100' : 'text-emerald-100'}`} title={room.nama}>
                        {room.nama}
                      </span>
                      
                      {/* Badge Kapasitas */}
                      <div className={`px-1.5 py-0.5 rounded flex items-center gap-1.5 text-[9px] xl:text-[10px] shrink-0 ${
                        isOccupied ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400'
                      }`}>
                         <Users size={11} className="opacity-70" />
                         <span className='font-mono font-medium'>{room.kapasitas}</span>
                      </div>
                    </div>

                    {/* Separator Minimalis */}
                    <div className={`border-t z-10 w-full ${isOccupied ? 'border-rose-500/20' : 'border-emerald-500/20'}`}></div>

                    {/* Row 2: Status Block */}
                    <div className="z-10 w-full mt-1.5 overflow-hidden">
                      {isOccupied ? (
                        <div className="flex flex-col">
                          <span className="text-[8px] xl:text-[9px] uppercase tracking-widest text-rose-400/80 font-bold block mb-0.5 truncate">
                            Berlangsung:
                          </span>
                          {/* Nama acara diamankan dengan truncate block agar tidak merusak layout ke bawah */}
                          <span className="text-[10px] xl:text-[12px] text-white/90 font-medium truncate block w-full" title={room.current_event || 'Kegiatan'}>
                            {room.current_event || 'Kegiatan'}
                          </span>
                        </div>
                      ) : (
                        <div className="flex items-center gap-1.5 text-emerald-400/80 pt-0.5 xl:pt-1">
                          <span className="w-1.5 h-1.5 xl:w-2 xl:h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                          <span className="text-[10px] xl:text-[11px] font-bold tracking-wide">TERSEDIA</span>
                        </div>
                      )}
                    </div>

                  </div>
                );
              })}
            </div>
          </div>

        </div>
      )}
    </GlassPanel>
  );
};

export default RoomAvailabilityPanel;