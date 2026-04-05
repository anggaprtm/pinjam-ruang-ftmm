import React from 'react';
import GlassPanel from './GlassPanel';
import { DoorOpen, Users, Minus } from 'lucide-react';
import AutoScrollList from './AutoScrollList';

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
  const totalRooms   = data.length;
  const totalKosong  = data.filter(r => r.status === 'kosong').length;
  const totalDipakai = data.filter(r => r.status === 'dipakai').length;

  return (
    <GlassPanel
      title={`KETERSEDIAAN RUANG`}
      icon={<DoorOpen className="w-6 h-6 text-emerald-400" />}
      className="h-full bg-navy-900/80 border-white/10"
    >
      {totalRooms === 0 ? (
        <div className="flex-1 flex flex-col items-center justify-center text-white/20 h-full">
          <Minus className="w-10 h-10 mb-2 opacity-30" />
          <p className="text-xs font-mono uppercase tracking-widest">Tidak ada data ruangan</p>
        </div>
      ) : (
        <div className="flex flex-col h-full gap-3">

          {/* ── Ringkasan stat ── */}
          <div className="shrink-0 flex items-center gap-3">
            {/* Total */}
            <div className="flex items-center gap-1.5 text-xs text-white/50 font-mono">
              <DoorOpen size={13} className="text-white/30" />
              <span>{totalRooms} ruangan</span>
            </div>
            <div className="w-px h-3 bg-white/10" />
            {/* Tersedia */}
            <div className="flex items-center gap-1.5 text-xs font-mono text-emerald-400">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
              {totalKosong} tersedia
            </div>
            <div className="w-px h-3 bg-white/10" />
            {/* Dipakai */}
            <div className="flex items-center gap-1.5 text-xs font-mono text-rose-400">
              <span className="w-2 h-2 rounded-full bg-rose-400" />
              {totalDipakai} digunakan
            </div>
          </div>

          {/* ── Grid ruangan ── */}
          {/*
            Maksimal 4 kolom agar cell cukup lebar di 4K.
            Min-height card pakai rem (ikut font scaling).
            Nama ruang pakai line-clamp-2 bukan truncate → wrap ke bawah, tidak dipotong.
          */}
          <div className="flex-1 min-h-0 overflow-y-auto pr-1 pb-1">
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
              {data.map((room) => {
                const isOccupied = room.status === 'dipakai';

                return (
                  <div
                    key={room.id}
                    className={`
                      relative flex flex-col gap-1.5 p-3 rounded-xl border transition-all duration-300
                      ${isOccupied
                        ? 'bg-rose-900/20 border-rose-500/30'
                        : 'bg-emerald-900/10 border-emerald-500/20'
                      }
                    `}
                    style={{ minHeight: '5.5rem' }}
                  >
                    {/* Accent bar kiri */}
                    <div className={`absolute left-0 top-0 bottom-0 w-0.5 rounded-l-xl ${
                      isOccupied ? 'bg-rose-500' : 'bg-emerald-500'
                    }`} />

                    {/* Nama ruang — line-clamp-2 supaya wrap, tidak terpotong */}
                    <div className="flex items-start justify-between gap-1.5">
                      <span
                        className={`font-bold text-sm leading-snug line-clamp-2 flex-1 min-w-0 ${
                          isOccupied ? 'text-rose-100' : 'text-emerald-100'
                        }`}
                        title={room.nama}
                      >
                        {room.nama}
                      </span>

                      {/* Badge kapasitas */}
                      <div className={`shrink-0 flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono ${
                        isOccupied ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400'
                      }`}>
                        <Users size={10} />
                        {room.kapasitas}
                      </div>
                    </div>

                    {/* Separator */}
                    <div className={`border-t ${isOccupied ? 'border-rose-500/20' : 'border-emerald-500/15'}`} />

                    {/* Status */}
                    {isOccupied ? (
                      <div className="flex flex-col gap-0.5">
                        <span className="text-[9px] uppercase tracking-widest text-rose-400/70 font-bold">
                          Sedang dipakai:
                        </span>
                        <span
                          className="text-xs text-white/80 font-medium line-clamp-2 leading-snug"
                          title={room.current_event || 'Kegiatan'}
                        >
                          {room.current_event || 'Kegiatan'}
                        </span>
                      </div>
                    ) : (
                      <div className="flex items-center gap-1.5 text-emerald-400/90">
                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0" />
                        <span className="text-[11px] font-bold tracking-wide">TERSEDIA</span>
                      </div>
                    )}
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