<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Ruang Sidang</title>

    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- RESET --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            color: white;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 25px;
            gap: 20px;
        }

        /* --- BACKGROUND GEDUNG (FIXED VISIBILITY) --- */
        .bg-layer { position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: -1; }
        .bg-image {
            /* URL Gambar Gedung yang lebih terang */
            background-image: url('https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=1920&auto=format&fit=crop');
            background-size: cover; background-position: center;
            /* Filter brightness dinaikkan agar lebih terang */
            filter: brightness(0.7) contrast(1.1);
            transform: scale(1.02);
        }
        /* Gradient ditipiskan drastis agar gambar belakang terlihat jelas */
        .bg-gradient { 
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.3), rgba(15, 23, 42, 0.8)); 
        }

        /* --- GLASS PANEL --- */
        .glass-panel {
            background: rgba(15, 23, 42, 0.6); /* Panel sedikit lebih transparan */
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            border-radius: 20px;
        }

        /* --- HEADER --- */
        header {
            flex-shrink: 0;
            padding: 15px;
            text-align: center;
            border-bottom: 4px solid #14b8a6; /* Teal */
            display: flex; flex-direction: column; align-items: center;
        }
        .subtitle { 
            color: #2dd4bf; font-size: 1.2rem; font-family: 'Roboto Mono', monospace; 
            letter-spacing: 0.3em; text-transform: uppercase; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }
        
        .clock-container { margin-top: 10px; display: flex; flex-direction: column; align-items: center; }
        #clock-time { font-size: 5.5rem; font-weight: 800; font-family: 'Roboto Mono', monospace; line-height: 1; color: #fff; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        #clock-date { font-size: 1.2rem; color: #94a3b8; font-weight: 500; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }

        /* --- MAIN LIST --- */
        main {
            flex: 1;
            display: flex; flex-direction: column;
            overflow: hidden;
        }

        .list-header {
            padding: 15px 25px;
            background: rgba(15, 23, 42, 0.8);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex; justify-content: space-between; align-items: center;
            flex-shrink: 0;
            border-radius: 20px 20px 0 0;
        }

        /* Container Kartu */
        #list-container {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px; 
            height: 100%;
        }

        /* --- CARD STYLE --- */
        .event-card {
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 16px 20px;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .event-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: #64748b;
            border-top-left-radius: 12px; border-bottom-left-radius: 12px;
        }
        
        /* Warna Kartu */
        .card-today { background: linear-gradient(90deg, rgba(20,184,166,0.2) 0%, rgba(30,41,59,0.6) 100%); border: 1px solid rgba(20,184,166,0.4); }
        .card-today::before { background: #14b8a6; box-shadow: 0 0 10px #14b8a6; } 
        
        .card-tomorrow::before { background: #f59e0b; }
        .card-future::before { background: #6366f1; }

        /* Baris Atas */
        /* ===== CARD TOP SECTION ===== */
        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .date-badge { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 4px 8px; border-radius: 4px; letter-spacing: 0.05em; }
        .badge-today { background: #14b8a6; color: #0f172a; }
        .badge-tomorrow { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-future { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); }

        .status-badge { padding: 4px 8px; border-radius: 50px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .status-reserved { background: rgba(255,255,255,0.1); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .status-occupied { background: #be123c; color: white; animation: pulse 2s infinite; }

        /* Judul (FIXED SIZE & WRAPPING) */
        .card-title { 
            font-size: 1.1rem; /* Ukuran diperkecil */
            font-weight: 700; 
            line-height: 1.3; /* Jarak antar baris jika wrap */
            color: #ffffff;
            margin: 8px 0;
            /* Mengizinkan teks untuk wrap ke baris baru */
            word-wrap: break-word;
            /* Hapus properti yang memaksa satu baris */
            /* white-space: nowrap; overflow: hidden; text-overflow: ellipsis; */
        }

        /* Baris Bawah */
        /* ===== CARD BOTTOM SECTION ===== */
        .card-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .time-display { display: flex; align-items: center; gap: 8px; font-family: 'Roboto Mono', monospace; font-size: 1.1rem; font-weight: 700; color: #e2e8f0; }
        .time-display i { color: #14b8a6; }

        .pic-display { text-align: right; }
        .pic-label { font-size: 0.6rem; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        .pic-name { font-size: 0.8rem; font-weight: 600; color: #2dd4bf; display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

        /* --- FOOTER --- */
        footer {
            flex-shrink: 0; padding: 15px; border-radius: 16px; display: flex; align-items: center; gap: 15px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .info-icon {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(20, 184, 166, 0.2); color: #2dd4bf;
            display: flex; justify-content: center; align-items: center;
            border: 1px solid rgba(20, 184, 166, 0.5); font-size: 1rem;
        }
        .marquee-container { flex: 1; overflow: hidden; position: relative; height: 24px; }
        .marquee-text {
            position: absolute; white-space: nowrap; font-size: 1rem; color: #cbd5e1;
            animation: marquee 25s linear infinite; line-height: 24px;
        }

        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
    </style>
</head>
<body>

    <div class="bg-layer bg-image"></div>
    <div class="bg-layer bg-gradient"></div>

    {{-- HEADER --}}
    <header class="glass-panel">
        <p class="subtitle">AGENDA RAPAT & PERTEMUAN</p>
        <div class="clock-container">
            <div id="clock-time">00:00</div>
            <div id="clock-date">...</div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="glass-panel">
        <div class="list-header">
            <span style="font-weight: 700; color: #e2e8f0; letter-spacing: 1px; text-transform: uppercase;">
                <i class="fas fa-list-ul me-2"></i> 5 Agenda Terdekat
            </span>
            <span style="font-size:0.7rem; padding:4px 8px; background:rgba(20,184,166,0.2); color:#2dd4bf; border-radius:4px; font-weight:700;">
                LIVE
            </span>
        </div>

        <div id="list-container">
            <div id="loading-state" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:rgba(255,255,255,0.3);">
                <i class="fas fa-circle-notch fa-spin fa-2x" style="margin-bottom: 15px;"></i>
                <p>Memuat Data...</p>
            </div>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer>
        <div class="info-icon"><i class="fas fa-info"></i></div>
        <div class="marquee-container">
            <div class="marquee-text">
                Selamat Datang di Fakultas Teknologi Maju dan Multidisiplin~
            </div>
        </div>
    </footer>

    {{-- JAVASCRIPT --}}
    <script>
        const API_URL = "{{ route('api.signage.verticalData', ['room' => 'Lt. 10']) }}"; 
        const REFRESH_INTERVAL = 30000; 

        // JAM
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
                const data = await response.json();
                renderList(data);
            } catch (error) {
                console.error("Gagal memuat data:", error);
            }
        }

        // RENDER
        function renderList(data) {
            const container = document.getElementById('list-container');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: rgba(255,255,255,0.4); text-align: center;">
                        <i class="fas fa-mug-hot fa-4x" style="margin-bottom: 20px; opacity: 0.6;"></i>
                        <p style="font-size: 1.5rem; font-weight: 700; color: white;">RUANGAN KOSONG</p>
                        <p style="margin-top: 5px;">Tidak ada agenda terdekat</p>
                    </div>
                `;
                return;
            }

            // Batasi 5 item
            const limitedData = data.slice(0, 5);

            const itemsHtml = limitedData.map(item => {
                let statusClass = 'status-reserved';
                let cardColorClass = 'card-' + (item.date_flag || 'future'); 
                let badgeColorClass = 'badge-' + (item.date_flag || 'future');

                if ((item.status || "").toLowerCase() === 'occupied') statusClass = 'status-occupied';

                // Fallback jika judul kosong
                const titleText = item.title ? item.title : '(Tanpa Judul)';

                return `
                <div class="event-card ${cardColorClass}">
                    
                    <div class="card-top">
                        <div class="date-badge ${badgeColorClass}">
                            ${item.date_label || 'AGENDA'}
                        </div>
                        <span class="status-badge ${statusClass}">
                            ${item.status === 'Occupied' ? '● BERLANGSUNG' : item.status}
                        </span>
                    </div>

                    <div class="card-title">
                        ${titleText}
                    </div>

                    <div class="card-bottom">
                        <div class="time-display">
                            <i class="far fa-clock"></i> ${item.time}
                        </div>
                    </div>

                </div>
                `;
            }).join('');

            container.innerHTML = itemsHtml;
        }

        fetchSignageData();
        setInterval(fetchSignageData, REFRESH_INTERVAL);
    </script>
</body>
</html>