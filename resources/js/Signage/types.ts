// resources/js/Signage/types.ts

export interface AgendaItem {
    id?: number;
    title: string;
    course_code?: string; // Tambahan buat Lectures
    time: string;
    room?: string;     // Kadang dipakai lectures
    location?: string; // Kadang dipakai events (kita samakan nanti logicnya)
    pic?: string;      // Lectures
    speaker?: string;  // Events
    
    // Field Baru untuk Events Panel
    date_day?: string;
    date_month?: string;
    category?: string;
    image?: string;
    
    type: 'kuliah' | 'kegiatan';
}


// TAMBAHKAN INI:
export interface Meeting {
    id: number;
    room: string;
    title: string;
    time: string;
    status: 'Occupied' | 'Reserved' | 'Finished';
    jenis: string;      // 'Rapat', 'Sidang Skripsi', dll
    pic: string;        // Nama Mahasiswa
    pembimbing?: string | null; // "Dr. A, Prof. B"
    penguji?: string | null;    // "Dr. C"
    date_flag?: 'today' | 'tomorrow' | 'future';   // untuk warna badge
    date_label?: string;  
}

export interface RoomAvailability {
    id: number;
    nama: string;
    kapasitas: number;
    status: 'kosong' | 'dipakai';
    current_event?: string | null;
}

export interface ApiResponse {
    jadwal_kuliah_hari_ini: AgendaItem[];
    jadwal_ujian: AgendaItem[];  
    kegiatan_mendatang: AgendaItem[];
    sidang_rapat: Meeting[]; // Tambahkan ini
    room_availability: RoomAvailability[];
}