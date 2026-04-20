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
      title="KETERSEDIAAN RUANG"
      icon={<DoorOpen className="w-5 h-5" />}
      className="h-full"
    >
      {totalRooms === 0 ? (
        <div className="flex-1 flex flex-col items-center justify-center text-ink-muted h-full">
          <Minus className="w-10 h-10 mb-2 opacity-20" />
          <p className="text-xs font-mono uppercase tracking-widest">Tidak ada data ruangan</p>
        </div>
      ) : (
        <div className="flex flex-col h-full gap-3">

          {/* Ringkasan stat */}
          <div className="shrink-0 flex items-center gap-3 px-1">
            <div className="flex items-center gap-1.5 text-xs text-ink-muted font-mono">
              <DoorOpen size={13} />
              <span>{totalRooms} ruangan</span>
            </div>
            <div className="w-px h-3 bg-surface-border" />
            <div className="flex items-center gap-1.5 text-xs font-mono text-ftmmBlue-600">
              <span className="w-2 h-2 rounded-full bg-ftmmBlue-400 animate-pulse" />
              {totalKosong} tersedia
            </div>
            <div className="w-px h-3 bg-surface-border" />
            <div className="flex items-center gap-1.5 text-xs font-mono text-danger-600">
              <span className="w-2 h-2 rounded-full bg-danger-600" />
              {totalDipakai} digunakan
            </div>
          </div>

          {/* Grid ruangan */}
          <div className="flex-1 min-h-0 overflow-y-auto scrollbar-hide pb-1">
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
              {data.map((room) => {
                const isOccupied = room.status === 'dipakai';

                return (
                  <div
                    key={room.id}
                    className={`
                      relative flex flex-col gap-1.5 p-3 rounded-xl border transition-all duration-300
                      ${isOccupied
                        ? 'bg-danger-50 border-danger-400/30'
                        : 'bg-ftmmBlue-50 border-ftmmBlue-400/30'
                      }
                    `}
                    style={{ minHeight: '5.5rem' }}
                  >
                    {/* Accent bar kiri */}
                    <div className={`absolute left-0 top-0 bottom-0 w-0.5 rounded-l-xl ${
                      isOccupied ? 'bg-danger-600' : 'bg-ftmmBlue-600'
                    }`} />

                    {/* Nama ruang */}
                    <div className="flex items-start justify-between gap-1.5">
                      <span
                        className={`font-bold text-sm leading-snug line-clamp-2 flex-1 min-w-0 ${
                          isOccupied ? 'text-danger-800' : 'text-ftmmBlue-800'
                        }`}
                        title={room.nama}
                      >
                        {room.nama}
                      </span>

                      {/* Badge kapasitas */}
                      <div className={`shrink-0 flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono ${
                        isOccupied
                          ? 'bg-danger-50 text-danger-600 border border-danger-400/30'
                          : 'bg-ftmmBlue-50 text-ftmmBlue-600 border border-ftmmBlue-400/30'
                      }`}>
                        <Users size={10} />
                        {room.kapasitas}
                      </div>
                    </div>

                    {/* Separator */}
                    <div className={`border-t ${
                      isOccupied ? 'border-danger-400/20' : 'border-ftmmBlue-400/20'
                    }`} />

                    {/* Status */}
                    {isOccupied ? (
                      <div className="flex flex-col gap-0.5">
                        <span className="text-[9px] uppercase tracking-widest text-danger-600/70 font-bold">
                          Sedang dipakai:
                        </span>
                        <span
                          className="text-xs text-ink-secondary font-medium line-clamp-2 leading-snug"
                          title={room.current_event || 'Kegiatan'}
                        >
                          {room.current_event || 'Kegiatan'}
                        </span>
                      </div>
                    ) : (
                      <div className="flex items-center gap-1.5 text-ftmmBlue-600">
                        <span className="w-1.5 h-1.5 rounded-full bg-ftmmBlue-400 animate-pulse shrink-0" />
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