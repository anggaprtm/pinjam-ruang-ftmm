// types.ts
export interface AgendaItem {
    title: string;
    time: string;
    room: string;
    pic: string; // Bisa nama dosen atau nama peminjam
    type: 'kuliah' | 'kegiatan';
}

export interface ApiResponse {
    jadwal_kuliah_hari_ini: AgendaItem[];
    kegiatan_mendatang: AgendaItem[];
}