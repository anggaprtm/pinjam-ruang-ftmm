@extends('layouts.admin')
@section('content')

@php
    $startHour = 7;
    $endHour = 20; // Diperpanjang sampai jam 21:00 agar full
    $totalHours = $endHour - $startHour;
    
    // Kita set lebar 1 jam = 100px (Bisa diatur sesuka hati)
    // Jadi total lebar canvas timeline = 14 jam x 100px = 1400px
    $hourWidth = 120; 
    $totalWidth = $totalHours * $hourWidth;
@endphp

<style>
    .monitoring-container {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 670px; /* Batasi tinggi agar bisa scroll */
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        position: relative;
    }

    /* HEADER JAM */
    .timeline-header {
        display: flex;
        border-bottom: 2px solid #e3e6f0;
        min-width: {{ $totalWidth + 150 }}px; /* 150px untuk nama ruangan */
        position: sticky;
        top: 0;
        z-index: 100;
        background: white;
    }
    .header-spacer {
        width: 150px;
        flex-shrink: 0;
        background: #f8f9fa;
        border-right: 2px solid #e3e6f0;
        position: sticky;
        left: 0;
        z-index: 110;
    }
    .header-time-track {
        display: flex;
        flex-grow: 1;
    }
    .header-slot {
        width: {{ $hourWidth }}px; /* Lebar FIX per jam */
        flex-shrink: 0;
        padding: 10px 5px;
        font-weight: bold;
        color: #6e707e;
        border-left: 1px dashed #e3e6f0;
        text-align: left;
        font-size: 0.85rem;
    }

    /* BARIS RUANGAN */
    .room-row {
        display: flex;
        min-width: {{ $totalWidth + 150 }}px;
        border-bottom: 1px solid #f1f3f5;
        height: 70px; /* Tinggi baris jadwal */
        position: relative;
    }
    .room-name {
        width: 150px;
        flex-shrink: 0;
        background: white;
        border-right: 2px solid #e3e6f0;
        display: flex;
        align-items: center;
        padding: 0 10px;
        font-weight: 600;
        font-size: 0.9rem; /* Ukuran font lebih kecil */
        color: #6e707e;
        position: sticky;
        left: 0;
        z-index: 20;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }
    
    /* AREA TIMELINE (CANVAS) */
    .room-timeline {
        position: relative;
        flex-grow: 1;
        background-size: {{ $hourWidth }}px 100%;
        background-image: linear-gradient(to right, transparent 99%, #f1f3f5 1%); 
    }
    
    /* Garis bantu awal jam (border kiri putus-putus) */
    .room-timeline::before {
        content: '';
        position: absolute;
        top: 0; bottom: 0; left: 0; right: 0;
        background-size: {{ $hourWidth }}px 100%;
        background-image: linear-gradient(to right, #e3e6f0 1px, transparent 1px);
        z-index: 0;
        pointer-events: none;
    }

    /* BLOK JADWAL */
    .schedule-block {
        position: absolute;
        top: 5px;
        bottom: 5px;
        border-radius: 4px;
        font-size: 0.75rem;
        color: white;
        padding: 4px 8px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        z-index: 10;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.2s, z-index 0.2s;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .schedule-block:hover {
        transform: scale(1.02);
        z-index: 50;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    /* WARNA PRODI */
    .bg-TI { background-color: #4e73df; } 
    .bg-TRKB { background-color: #1cc88a; } 
    .bg-TSD { background-color: #36b9cc; }
    .bg-TE { background-color: #f6c23e; color: #444; } 
    .bg-RN { background-color: #e74a3b; }
    .bg-default { background-color: #858796; }
</style>

<div class="card shadow mb-4 border-0">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            Visualisasi Jadwal Ruangan
        </h6>
        
        <form action="{{ route('admin.jadwal-perkuliahan.monitoring') }}" method="GET" class="d-flex align-items-center">
            <label class="me-2 small fw-bold text-muted mb-0">HARI:</label>
            <select name="hari" class="form-select form-select-sm shadow-none" onchange="this.form.submit()" style="width: 140px;">
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                    <option value="{{ $h }}" {{ $selectedHari == $h ? 'selected' : '' }}>{{ $h }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card-body p-0">
        @if($ruangans->isEmpty())
            <div class="text-center py-5">
                <img src="https://img.icons8.com/clouds/100/000000/calendar.png" alt="Empty" class="mb-3">
                <h5 class="text-gray-500">Tidak ada jadwal kuliah pada hari {{ $selectedHari }}</h5>
                <p class="small text-muted">Semua ruangan kosong atau tidak ada data semester aktif.</p>
            </div>
        @else
            <div class="monitoring-container">
                
                {{-- HEADER JAM --}}
                <div class="timeline-header">
                    <div class="header-spacer">
                        <div class="p-3 small fw-bold text-muted"></div>
                    </div>
                    <div class="header-time-track">
                        @for($i = $startHour; $i < $endHour; $i++)
                            <div class="header-slot">
                                {{ sprintf('%02d:00', $i) }}
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- LOOP RUANGAN --}}
                @foreach($ruangans as $ruangan)
                    <div class="room-row">
                        <div class="room-name">
                            {{ $ruangan->nama }}
                        </div>
                        
                        <div class="room-timeline">
                            @foreach($ruangan->jadwalPerkuliahan as $jadwal)
                                @php
                                    $start = \Carbon\Carbon::parse($jadwal->waktu_mulai);
                                    $end = \Carbon\Carbon::parse($jadwal->waktu_selesai);
                                    
                                    // PERBAIKAN LOGIC MATEMATIKA PIXEL
                                    // 1. Konversi waktu ke total menit sejak midnight (00:00)
                                    $startTotalMinutes = $start->hour * 60 + $start->minute;
                                    $endTotalMinutes = $end->hour * 60 + $end->minute;
                                    
                                    // 2. Hitung offset dari jam mulai timeline (startHour)
                                    $timelineStartMinutes = $startHour * 60;
                                    
                                    // 3. Hitung posisi relatif terhadap timeline
                                    $relativeStartMinutes = $startTotalMinutes - $timelineStartMinutes;
                                    $durationMinutes = $endTotalMinutes - $startTotalMinutes;
                                    
                                    // 4. Konversi ke Pixel dengan presisi tinggi
                                    // Formula: (menit / 60) * pixel_per_jam
                                    $leftPos = ($relativeStartMinutes / 60) * $hourWidth;
                                    $widthPos = ($durationMinutes / 60) * $hourWidth;

                                    // Warna berdasarkan Program Studi
                                    $prodiClass = 'bg-default';
                                    if(str_contains($jadwal->program_studi, 'TI')) $prodiClass = 'bg-TI';
                                    elseif(str_contains($jadwal->program_studi, 'RK')) $prodiClass = 'bg-TRKB';
                                    elseif(str_contains($jadwal->program_studi, 'SD')) $prodiClass = 'bg-TSD';
                                    elseif(str_contains($jadwal->program_studi, 'TE')) $prodiClass = 'bg-TE';
                                    elseif(str_contains($jadwal->program_studi, 'RN')) $prodiClass = 'bg-RN';
                                @endphp

                                <div class="schedule-block {{ $prodiClass }}" 
                                     style="left: {{ $leftPos }}px; width: {{ $widthPos }}px;"
                                     data-bs-toggle="tooltip" 
                                     data-bs-html="true"
                                     title="<b>{{ $jadwal->mata_kuliah }}</b><br>{{ $start->format('H:i') }} - {{ $end->format('H:i') }}<br>{{ $jadwal->dosen }}">
                                    
                                    <div class="fw-bold">{{ $jadwal->kode_matkul }}</div>
                                    <div style="font-size: 10px; opacity: 0.9;">{{ Str::limit($jadwal->mata_kuliah, 45) }} ({{ $jadwal->program_studi }})</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card-footer bg-white small text-muted d-flex justify-content-between">
        <div>
            <i class="fas fa-info-circle me-1"></i> Menampilkan ruangan yang memiliki jadwal aktif saja.
        </div>
        <div class="d-flex justify-content-center align-items-center gap-2">
            <span class="badge bg-TI">TI</span>
            <span class="badge bg-TRKB">TRKB</span>
            <span class="badge bg-TSD">TSD</span>
            <span class="badge bg-TE">TE</span>
            <span class="badge bg-RN">RN</span>
        </div>


    </div>
</div>

@section('scripts')
@parent
<script>
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endsection

@endsection