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
    id: string;
    room: string;
    title: string;
    time: string;
    status: 'Occupied' | 'Reserved' | 'Available';
    // Persiapan buat nanti (Optional dulu)
    student?: string;
    supervisor?: string; // Dosen Pembimbing
    examiner?: string;   // Dosen Penguji
}

export interface ApiResponse {
    jadwal_kuliah_hari_ini: AgendaItem[];
    kegiatan_mendatang: AgendaItem[];
}