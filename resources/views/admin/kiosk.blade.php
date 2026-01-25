<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Ruang Sidang</title>

    {{-- Font Google --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- RESET --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #020617; /* Very Dark Navy */
            color: white;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 30px;
            gap: 25px;
        }

        /* --- BACKGROUND (Gedung Fakultas) --- */
        .bg-layer { position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: -1; }
        .bg-image {
            /* Placeholder Gedung Modern */
            background-image: url('https://images.unsplash.com/photo-1554469384-e58fac16e23a?q=80&w=1000&auto=format&fit=crop');
            background-size: cover; background-position: center;
            /* Filter biar teks kebaca jelas */
            filter: blur(8px) brightness(0.4) contrast(1.1);
            transform: scale(1.05);
        }
        /* Overlay Gradient biar makin elegan */
        .bg-gradient { 
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.95)); 
        }

        /* --- GLASS PANEL STYLE --- */
        .glass-panel {
            background: rgba(30, 41, 59, 0.7); /* Slate-800 transparan */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            border-radius: 24px;
        }

        /* --- HEADER --- */
        header {
            flex-shrink: 0;
            padding: 30px;
            text-align: center;
            border-bottom: 5px solid #14b8a6; /* Teal Accent */
            display: flex; flex-direction: column; align-items: center;
        }
        h1 { font-size: 3rem; font-weight: 900; letter-spacing: 0.05em; margin-bottom: 5px; text-transform: uppercase; color: #ffffff; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .subtitle { color: #2dd4bf; font-size: 1.3rem; font-family: 'Roboto Mono', monospace; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600; }
        
        .clock-container { margin-top: 20px; display: flex; flex-direction: column; align-items: center; }
        #clock-time { font-size: 6rem; font-weight: 800; font-family: 'Roboto Mono', monospace; line-height: 1; text-shadow: 0 4px 15px rgba(0,0,0,0.5); color: #f1f5f9; }
        #clock-date { font-size: 1.5rem; color: #94a3b8; font-weight: 500; margin-top: 10px; text-transform: uppercase; letter-spacing: 2px; }

        /* --- MAIN LIST AREA --- */
        main {
            flex: 1;
            overflow: hidden;
            position: relative;
            display: flex; flex-direction: column;
        }

        .list-header {
            padding: 20px 30px;
            background: rgba(15, 23, 42, 0.6);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex; justify-content: space-between; align-items: center;
            flex-shrink: 0;
        }

        #list-container {
            flex: 1;
            padding: 30px;
            overflow: hidden; /* Hidden karena kita pakai animasi marquee */
            position: relative;
        }

        /* --- CARD STYLE (Anti Nabrak) --- */
        .event-card {
            margin-bottom: 24px;
            padding: 25px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            display: flex; flex-direction: column; 
            gap: 15px; /* Jarak aman antar elemen dalam kartu */
            transition: transform 0.3s ease;
        }
        
        /* Garis indikator kiri */
        .event-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: #64748b;
        }
        
        /* Warna-warni berdasarkan waktu */
        .card-today { background: linear-gradient(90deg, rgba(20,184,166,0.1) 0%, rgba(0,0,0,0) 100%); }
        .card-today::before { background: #14b8a6; box-shadow: 0 0 15px #14b8a6; } /* Teal */
        
        .card-tomorrow::before { background: #f59e0b; } /* Amber */
        .card-future::before { background: #6366f1; } /* Indigo */

        /* Baris Atas Kartu */
        .card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .date-badge {
            font-size: 0.85rem; font-weight: 800; text-transform: uppercase; padding: 6px 12px; border-radius: 6px; letter-spacing: 0.05em;
        }
        .badge-today { background: #14b8a6; color: #0f172a; }
        .badge-tomorrow { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-future { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); }

        .status-badge {
            padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        }
        .status-reserved { background: rgba(255,255,255,0.1); color: #cbd5e1; }
        .status-occupied { background: #be123c; color: white; box-shadow: 0 0 10px #be123c; animation: pulse 2s infinite; }

        /* Judul Kartu */
        .card-title { 
            font-size: 1.8rem; font-weight: 700; line-height: 1.25; color: #ffffff;
            /* Pastikan judul panjang tidak merusak layout */
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        /* Baris Bawah Kartu */
        .card-bottom {
            display: flex; justify-content: space-between; align-items: flex-end;
            padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto; /* Dorong ke bawah */
        }
        
        .time-display {
            display: flex; align-items: center; gap: 10px;
            font-family: 'Roboto Mono', monospace; font-size: 1.5rem; font-weight: 700; color: #e2e8f0;
        }
        .time-display i { color: #14b8a6; }

        .pic-display {
            text-align: right;
        }
        .pic-label { font-size: 0.7rem; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 2px; }
        .pic-name { font-size: 1rem; font-weight: 600; color: #14b8a6; display: flex; align-items: center; justify-content: flex-end; gap: 8px; }

        /* --- FOOTER --- */
        footer {
            flex-shrink: 0; padding: 20px; border-radius: 20px; display: flex; align-items: center; gap: 20px;
        }
        .info-icon {
            width: 50px; height: 50px; border-radius: 50%;
            background: rgba(20, 184, 166, 0.2); color: #2dd4bf;
            display: flex; justify-content: center; align-items: center;
            border: 2px solid rgba(20, 184, 166, 0.5); font-size: 1.5rem;
        }
        .marquee-container { flex: 1; overflow: hidden; position: relative; height: 30px; }
        .marquee-text {
            position: absolute; white-space: nowrap; font-size: 1.2rem; color: #cbd5e1;
            animation: marquee 25s linear infinite; line-height: 30px;
        }

        /* --- ANIMATIONS --- */
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
        @keyframes scroll-vertical { 0% { transform: translateY(0); } 100% { transform: translateY(-50%); } }
        
        .animate-scroll { animation: scroll-vertical 45s linear infinite; }
        .animate-scroll:hover { animation-play-state: paused; }
    </style>
</head>
<body>

    <div class="bg-layer bg-image"></div>
    <div class="bg-layer bg-gradient"></div>

    {{-- HEADER --}}
    <header class="glass-panel">
        <h1>RUANG SIDANG LT. 10</h1>
        <p class="subtitle">Agenda Rapat & Pertemuan</p>
        <div class="clock-container">
            <div id="clock-time">00:00</div>
            <div id="clock-date">...</div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="glass-panel">
        <div class="list-header">
            <span style="font-weight: 700; color: #cbd5e1; letter-spacing: 1px; text-transform: uppercase;">
                <i class="fas fa-calendar-alt me-2"></i> Jadwal Mendatang
            </span>
            <span style="font-size:0.75rem; padding:5px 10px; background:rgba(20,184,166,0.2); color:#2dd4bf; border-radius:6px; font-weight:700;">
                LIVE UPDATE
            </span>
        </div>

        <div id="list-container">
            <div id="loading-state" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:rgba(255,255,255,0.3);">
                <i class="fas fa-circle-notch fa-spin fa-3x" style="margin-bottom: 20px;"></i>
                <p style="font-size: 1.4rem;">Memuat Data...</p>
            </div>
        </div>
        
        <div style="position: absolute; top: 75px; left: 0; width: 100%; height: 60px; background: linear-gradient(to bottom, rgba(30,41,59,1), transparent); pointer-events: none;"></div>
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 60px; background: linear-gradient(to top, rgba(30,41,59,1), transparent); pointer-events: none;"></div>
    </main>

    {{-- FOOTER --}}
    <footer class="glass-panel">
        <div class="info-icon"><i class="fas fa-info"></i></div>
        <div class="marquee-container">
            <div class="marquee-text">
                Selamat Datang di Fakultas Teknologi Maju dan Multidisiplin • Harap menjaga ketenangan selama rapat berlangsung • Dilarang makan dan minum di dalam ruang sidang.
            </div>
        </div>
    </footer>

    {{-- JAVASCRIPT --}}
    <script>
        // Gunakan Route baru yang kita buat di step 2
        const API_URL = "{{ route('api.signage.verticalData', ['room' => 'Lt. 10']) }}";
        const REFRESH_INTERVAL = 30000; // 30 Detik

        // 1. JAM DIGITAL
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            
            document.getElementById('clock-time').innerText = timeStr;
            document.getElementById('clock-date').innerText = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 2. FETCH DATA DARI CONTROLLER
        async function fetchSignageData() {
            try {
                const response = await fetch(API_URL);
                const data = await response.json();
                renderList(data);
            } catch (error) {
                console.error("Gagal memuat data:", error);
            }
        }

        // 3. RENDER HTML
        function renderList(data) {
            const container = document.getElementById('list-container');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: rgba(255,255,255,0.4); text-align: center;">
                        <i class="fas fa-calendar-times fa-5x" style="margin-bottom: 24px; opacity: 0.6;"></i>
                        <p style="font-size: 2rem; font-weight: 700; color: white;">TIDAK ADA JADWAL</p>
                        <p style="font-size: 1.1rem; margin-top: 10px;">Ruang Sidang Kosong Hari Ini</p>
                    </div>
                `;
                return;
            }

            const itemsHtml = data.map(item => {
                let statusClass = 'status-reserved';
                let cardColorClass = 'card-' + (item.date_flag || 'future'); 
                let badgeColorClass = 'badge-' + (item.date_flag || 'future');

                if ((item.status || "").toLowerCase() === 'occupied') statusClass = 'status-occupied';

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
                        ${item.title}
                    </div>

                    <div class="card-bottom">
                        <div class="time-display">
                            <i class="far fa-clock"></i> ${item.time}
                        </div>
                        <div class="pic-display">
                            <div class="pic-label">Penanggung Jawab</div>
                            <div class="pic-name">
                                ${item.pic || '-'} <i class="fas fa-user-circle"></i>
                            </div>
                        </div>
                    </div>

                </div>
                `;
            }).join('');

            // Scroll Logic: Kalau lebih dari 3 item, aktifkan marquee vertical
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

        // Init
        fetchSignageData();
        setInterval(fetchSignageData, REFRESH_INTERVAL);
    </script>
</body>
</html>