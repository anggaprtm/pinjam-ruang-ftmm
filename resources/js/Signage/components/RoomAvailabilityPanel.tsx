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
          <div className="flex items-center gap-3 mb-2 shrink-0 px-1 border-b border-white/5 pb-1.5">
            <div className="flex items-center gap-1.5 text-[10px] font-mono text-white/50">
              <span className="w-2.5 h-2.5 rounded-sm bg-emerald-500/20 border border-emerald-500/50"></span>
              <span>Tersedia</span>
            </div>
            <div className="flex items-center gap-1.5 text-[10px] font-mono text-white/50">
              <span className="w-2.5 h-2.5 rounded-sm bg-rose-500/20 border border-rose-500/50 relative overflow-hidden">
                <span className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTAgNDBsNDAtNDBIMzBMMCAzMHYxMHptNDAgMEw0MCAzMCAwIDQwaDQwIiBzdHJva2U9InJnYmEoMjQ0LCA2MywgOTQsIDAuMDUpIiBzdHJva2U9IndpZHRoPSIyIiBmaWxsPSJub25lIi8+PC9zdmc+')] opacity-20"></span>
              </span>
              <span>Digunakan</span>
            </div>
          </div>

          {/* 🔥 4K OPTIMIZED GRID 🔥 */}
          {/* Lebar grid disesuaikan s/d 2xl (TV Size) dengan gap lebih rapat */}
          <div className="flex-1 overflow-hidden min-h-0 pr-1 pb-1">
            <div className="grid grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7 2xl:grid-cols-8 gap-2">
              
              {data.map((room) => {
                const isOccupied = room.status === 'dipakai';
                
                return (
                  <div 
                    key={room.id} 
                    // Padding dan gap card dikurangi agar compact secara vertikal
                    className={`relative p-2 rounded-lg flex flex-col justify-center border transition-all duration-300 min-h-[56px] ${
                      isOccupied 
                        ? 'bg-rose-900/20 border-rose-500/30 overflow-hidden' 
                        : 'bg-emerald-900/10 border-emerald-500/20'
                    }`}
                  >
                    {/* Pattern Background Minimalis */}
                    {isOccupied && (
                      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTAgNDBsNDAtNDBIMzBMMCAzMHYxMHptNDAgMEw0MCAzMCAwIDQwaDQwIiBzdHJva2U9InJnYmEoMjQ0LCA2MywgOTQsIDAuMDIpIiBzdHJva2U9IndpZHRoPSIyIiBmaWxsPSJub25lIi8+PC9zdmc+')] opacity-30 pointer-events-none"></div>
                    )}

                    {/* Row 1: Nama Ruang & Kapasitas (Slim) */}
                    <div className="flex items-center justify-between z-10 w-full gap-1 mb-1">
                      {/* Truncate dihapus ganti whitespace-nowrap agar nama (GC-6.01) tidak terpotong elipsis */}
                      <span className={`font-bold text-xs xl:text-[13px] tracking-tight whitespace-nowrap ${isOccupied ? 'text-rose-100' : 'text-emerald-100'}`}>
                        {room.nama}
                      </span>
                      
                      {/* Kapasitas diganti jadi bentuk horizontal pill agar hemat space */}
                      <div className={`px-1.5 py-0.5 rounded flex items-center gap-1 text-[9px] shrink-0 ${
                        isOccupied ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400'
                      }`}>
                         <Users size={10} className="opacity-70" />
                         <span className='font-mono font-medium'>{room.kapasitas}</span>
                      </div>
                    </div>

                    {/* Separator Minimalis */}
                    <div className={`border-t z-10 w-full ${isOccupied ? 'border-rose-500/20' : 'border-emerald-500/20'}`}></div>

                    {/* Row 2: Status Block (Extra Compact) */}
                    <div className="z-10 w-full mt-1">
                      {isOccupied ? (
                        <div className="flex flex-col">
                          <span className="text-[8px] uppercase tracking-widest text-rose-400/80 font-bold block">Berlangsung:</span>
                          <span className="text-[10px] xl:text-xs text-white/90 font-medium line-clamp-1 leading-tight" title={room.current_event || 'Kegiatan'}>
                            {room.current_event || 'Kegiatan'}
                          </span>
                        </div>
                      ) : (
                        <div className="flex items-center gap-1.5 text-emerald-400/80 pt-0.5">
                          <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                          <span className="text-[10px] font-bold tracking-wide">TERSEDIA</span>
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