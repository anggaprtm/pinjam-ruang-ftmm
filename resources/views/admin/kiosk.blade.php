<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vertical Signage - Ruang Sidang</title>

    {{-- 1. Font (Opsional, kalau gak ada internet dia pake font bawaan) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Roboto+Mono:wght@500&display=swap" rel="stylesheet">
    
    {{-- 2. Icon FontAwesome (Pastikan Kiosk ada Internet, kalau tidak harus download fontnya lokal) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- RESET & BASE --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; /* Navy background fallback */
            color: white;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 20px;
            gap: 20px;
        }

        /* --- BACKGROUND --- */
        .bg-layer {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: -1;
        }
        .bg-image {
            background-image: url('https://picsum.photos/1080/1920?grayscale&blur=2'); /* Ganti dengan URL lokal jika offline */
            background-size: cover; background-position: center;
            filter: blur(8px) brightness(0.4);
            transform: scale(1.05);
        }
        .bg-overlay { background: rgba(15, 23, 42, 0.7); mix-blend-mode: multiply; }
        .bg-gradient { background: radial-gradient(circle at center, transparent 0%, rgba(5,10,20,0.8) 100%); }

        /* --- COMPONENTS --- */
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px); /* Support Safari/Old Chrome */
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        /* HEADER */
        header {
            flex-shrink: 0;
            border-radius: 24px;
            padding: 24px;
            text-align: center;
            border-bottom: 4px solid #14b8a6; /* Electric Teal */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 8px; text-transform: uppercase; }
        .subtitle { color: #2dd4bf; font-size: 1.125rem; font-family: 'Roboto Mono', monospace; letter-spacing: 0.1em; text-transform: uppercase; }
        
        .clock-container { margin-top: 20px; }
        #clock-time { font-size: 5rem; font-weight: 900; font-family: 'Roboto Mono', monospace; line-height: 1; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        #clock-date { font-size: 1.5rem; color: #9ca3af; font-weight: 500; margin-top: 8px; text-transform: uppercase; }

        /* MAIN CONTENT */
        main {
            flex: 1;
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .list-header {
            position: absolute; top: 0; left: 0; width: 100%;
            padding: 16px 20px;
            background: rgba(15, 23, 42, 0.9);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            z-index: 10;
            display: flex; justify-content: space-between; align-items: center;
        }
        .live-badge {
            font-size: 0.75rem; padding: 4px 8px; border-radius: 4px;
            background: rgba(20, 184, 166, 0.1); color: #2dd4bf; border: 1px solid rgba(20, 184, 166, 0.2);
            animation: pulse 2s infinite;
        }

        /* SCROLL CONTAINER */
        #list-container {
            height: 100%;
            padding: 70px 20px 20px 20px; /* Top padding biar gak ketutup header */
            overflow: hidden; /* Hide scrollbar */
        }

        /* CARD STYLE */
        .event-card {
            margin-bottom: 16px;
            padding: 20px;
            border-radius: 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
            display: flex; flex-direction: column; gap: 10px;
        }
        .event-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: #14b8a6;
        }
        .card-header-row { display: flex; justify-content: space-between; align-items: flex-start; }
        .time-badge {
            display: flex; align-items: center; gap: 8px;
            color: #2dd4bf; font-family: 'Roboto Mono', monospace; font-weight: 700; font-size: 1.25rem;
            background: rgba(20, 184, 166, 0.1); padding: 4px 12px; border-radius: 8px;
            border: 1px solid rgba(20, 184, 166, 0.2);
        }
        .status-badge {
            padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        }
        .status-reserved { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-occupied { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        
        .card-title { font-size: 1.5rem; font-weight: 700; line-height: 1.2; color: white; }
        .card-pic { display: flex; align-items: center; gap: 8px; color: #d1d5db; font-size: 0.9rem; margin-top: 5px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; }

        /* FOOTER */
        footer {
            flex-shrink: 0;
            padding: 16px;
            border-radius: 16px;
            display: flex; align-items: center; gap: 16px;
        }
        .info-icon {
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(20, 184, 166, 0.2); color: #2dd4bf;
            display: flex; justify-content: center; align-items: center;
            border: 1px solid rgba(20, 184, 166, 0.5); font-size: 1.25rem;
        }
        .marquee-container { flex: 1; overflow: hidden; position: relative; height: 30px; }
        .marquee-text {
            position: absolute; white-space: nowrap;
            font-size: 1.125rem; color: #d1d5db;
            animation: marquee 20s linear infinite;
        }

        /* ANIMATION */
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
        @keyframes scroll-vertical { 0% { transform: translateY(0); } 100% { transform: translateY(-50%); } }
        
        .animate-scroll { animation: scroll-vertical 40s linear infinite; }

        /* UTILITY */
        .text-center { text-align: center; }
        .flex-col { display: flex; flex-direction: column; }
        .hidden { display: none; }
    </style>
</head>
<body>

    {{-- BACKGROUND LAYERS --}}
    <div class="bg-layer bg-image"></div>
    <div class="bg-layer bg-overlay"></div>
    <div class="bg-layer bg-gradient"></div>

    {{-- HEADER --}}
    <header class="glass-panel">
        <h1>RUANG SIDANG LT. 10</h1>
        <p class="subtitle">Agenda Rapat Hari Ini</p>
        <div class="clock-container">
            <div id="clock-time">00:00</div>
            <div id="clock-date">...</div>
        </div>
    </header>

    {{-- MAIN LIST --}}
    <main class="glass-panel">
        <div class="list-header">
            <span style="font-weight: 700; color: #9ca3af; letter-spacing: 1px; text-transform: uppercase;">
                <i class="fas fa-list-ul"></i> Daftar Kegiatan
            </span>
            <span class="live-badge">LIVE UPDATE</span>
        </div>

        <div id="list-container">
            <div id="loading-state" class="text-center" style="margin-top: 50px; color: rgba(255,255,255,0.3);">
                <i class="fas fa-circle-notch fa-spin fa-3x" style="margin-bottom: 20px;"></i>
                <p style="font-size: 1.2rem;">Memuat Jadwal...</p>
            </div>
        </div>
        
        <div style="position: absolute; top: 60px; left: 0; width: 100%; height: 40px; background: linear-gradient(to bottom, rgba(15,23,42,0.9), transparent); pointer-events: none;"></div>
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 40px; background: linear-gradient(to top, rgba(15,23,42,0.9), transparent); pointer-events: none;"></div>
    </main>

    {{-- FOOTER --}}
    <footer class="glass-panel">
        <div class="info-icon">
            <i class="fas fa-info"></i>
        </div>
        <div class="marquee-container">
            <div class="marquee-text">
                Selamat Datang di Fakultas Teknologi Maju dan Multidisiplin • Harap menjaga ketenangan selama rapat berlangsung • Dilarang makan dan minum di dalam ruang sidang • Gunakan fasilitas dengan bijak.
            </div>
        </div>
    </footer>

    {{-- JAVASCRIPT --}}
    <script>
        const API_URL = "{{ route('api.signage.index') }}"; 
        const FILTER_ROOM = "Lt. 10"; 
        const FILTER_TYPE = "Rapat";
        const REFRESH_INTERVAL = 30000; 

        // CLOCK
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            
            document.getElementById('clock-time').innerText = timeStr;
            document.getElementById('clock-date').innerText = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // FETCH DATA
        async function fetchSignageData() {
            try {
                const response = await fetch(API_URL);
                const json = await response.json();
                const rawData = json.sidang_rapat || [];

                const filteredData = rawData.filter(item => {
                    const roomName = (item.room || "").toLowerCase();
                    const activityType = (item.jenis || "").toLowerCase();
                    return roomName.includes(FILTER_ROOM.toLowerCase()) && 
                           activityType.includes(FILTER_TYPE.toLowerCase());
                });

                renderList(filteredData);
            } catch (error) {
                console.error("Error:", error);
            }
        }

        function renderList(data) {
            const container = document.getElementById('list-container');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: rgba(255,255,255,0.4); text-align: center;">
                        <i class="fas fa-calendar-check fa-4x" style="margin-bottom: 20px;"></i>
                        <p style="font-size: 2rem; font-weight: 700;">RUANGAN KOSONG</p>
                        <p>Tidak ada jadwal rapat untuk hari ini</p>
                    </div>
                `;
                return;
            }

            const itemsHtml = data.map(item => {
                let statusClass = 'status-reserved';
                if ((item.status || "").toLowerCase() === 'occupied') statusClass = 'status-occupied';

                return `
                <div class="event-card">
                    <div class="card-header-row">
                        <div class="time-badge">
                            <i class="far fa-clock"></i> ${item.time}
                        </div>
                        <span class="status-badge ${statusClass}">
                            ${item.status}
                        </span>
                    </div>
                    <div class="card-title">${item.title}</div>
                    <div class="card-pic">
                        <i class="fas fa-user-tie" style="color: #14b8a6;"></i>
                        <span>PIC: ${item.pic || '-'}</span>
                    </div>
                </div>
                `;
            }).join('');

            if (data.length > 3) {
                container.innerHTML = `
                    <div class="animate-scroll">
                        ${itemsHtml}
                        ${itemsHtml}
                    </div>
                `;
            } else {
                container.innerHTML = `<div style="display:flex; flex-direction:column;">${itemsHtml}</div>`;
            }
        }

        fetchSignageData();
        setInterval(fetchSignageData, REFRESH_INTERVAL);
    </script>
</body>
</html>