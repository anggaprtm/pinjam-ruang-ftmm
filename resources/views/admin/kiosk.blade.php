<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Ruang Sidang</title>
    <meta name="signage-api-key" content="{{ config('services.signage.key') }}">

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
            background-image: url("{{ asset('images/gedung-ftmms.JPG') }}");
            background-size: cover; background-position: center; background-repeat: no-repeat;
	    width: 100%;
	    height: 100%;
	    position: absolute;
            filter: brightness(0.7) contrast(1.1);
        }
        /* Gradient ditipiskan drastis agar gambar belakang terlihat jelas */
        .bg-gradient { 
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.3), rgba(15, 23, 42, 0.8)); 
        }

        /* --- GLASS PANEL --- */
        .glass-panel {
            background: rgba(15, 23, 42, 0.6); /* Panel sedikit lebih transparan */
            backdrop-filter: blur(15px);
            margin-bottom: 12px;
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
	        margin-bottom: 12px;
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
        
        .date-badge { font-size: 0.9rem; font-weight: 800; text-transform: uppercase; padding: 4px 8px; border-radius: 4px; letter-spacing: 0.05em; }
        .badge-today { background: #14b8a6; color: #0f172a; }
        .badge-tomorrow { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-future { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); }

        .status-badge { padding: 4px 8px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .status-reserved { background: rgba(255,255,255,0.1); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .status-occupied { background: #be123c; color: white; animation: pulse 2s infinite; }
        .status-finished { background: rgba(8, 143, 76, 0.44); color: white; }


        /* Judul (FIXED SIZE & WRAPPING) */
        .card-title { 
            font-size: 1.3rem; /* Ukuran diperkecil */
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
        
        .time-display { display: flex; align-items: center; font-family: 'Roboto Mono', monospace; font-size: 1.3rem; font-weight: 700; color: #e2e8f0; }
        .time-display i { color: #14b8a6; margin-right: 13px; }

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
            position: absolute; white-space: nowrap; font-size: 1.3rem; color: #cbd5e1;
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
        <p class="subtitle">GEDUNG NANO • R. SIDANG LT. 10</p>
        <div class="clock-container">
            <div id="clock-time">00:00</div>
            <div id="clock-date">...</div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="glass-panel">
        <div class="list-header">
            <span style="font-weight: 700; color: #e2e8f0; letter-spacing: 1px; text-transform: uppercase;">
                <i class="fas fa-list-ul me-2"></i> Agenda Terdekat
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
                • Selamat Datang di Fakultas Teknologi Maju dan Multidisiplin • Beraksi Dalam Kolaborasi •
            </div>
        </div>
    </footer>

    {{-- JAVASCRIPT --}}
    <script>
    /* =========================================================
    CONFIG
    ========================================================= */
    var API_URL = "{{ route('api.signage.verticalData', [
        'signage_key' => config('services.signage.key')
    ]) }}";

    var REFRESH_INTERVAL = 30000;

    /* =========================================================
    CLOCK (TV SAFE)
    ========================================================= */
    function updateClock() {
        var now = new Date();

        var h = ('0' + now.getHours()).slice(-2);
        var m = ('0' + now.getMinutes()).slice(-2);
        var s = ('0' + now.getSeconds()).slice(-2);

        var clockTime = document.getElementById('clock-time');
        if (clockTime) {
            clockTime.innerHTML = h + ':' + m + ':' + s;
        }

        var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        var dateStr =
            days[now.getDay()] + ', ' +
            now.getDate() + ' ' +
            months[now.getMonth()] + ' ' +
            now.getFullYear();

        var clockDate = document.getElementById('clock-date');
        if (clockDate) {
            clockDate.innerHTML = dateStr;
        }
    }

    setInterval(updateClock, 1000);
    updateClock();

    /* =========================================================
    FETCH DATA (XMLHttpRequest - TV SAFE)
    ========================================================= */
    function fetchSignageData() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', API_URL, true);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        renderList(data);
                    } catch (e) {
                        showError('Format data tidak valid');
                    }
                } else {
                    showError('API error (' + xhr.status + ')');
                }
            }
        };

        xhr.onerror = function () {
            showError('Gagal menghubungi server');
        };

        xhr.send();
    }

    /* =========================================================
    RENDER LIST
    ========================================================= */
    function renderList(data) {
        var container = document.getElementById('list-container');
        if (!container) return;

        if (!data || data.length === 0) {
            container.innerHTML =
                '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:rgba(255,255,255,0.4);text-align:center;">' +
                    '<i class="fas fa-mug-hot fa-4x" style="margin-bottom:20px;opacity:0.6;"></i>' +
                    '<p style="font-size:1.5rem;font-weight:700;color:white;">RUANGAN KOSONG</p>' +
                    '<p style="margin-top:5px;">Tidak ada agenda terdekat</p>' +
                '</div>';
            return;
        }

        var html = '';
        var limit = data.length > 7 ? 7 : data.length;

        for (var i = 0; i < limit; i++) {
            var item = data[i];

            var dateFlag = item.date_flag ? item.date_flag : 'future';
            var cardClass = 'card-' + dateFlag;
            var badgeClass = 'badge-' + dateFlag;

            var statusClass = 'status-reserved';
            if (item.status && item.status.toLowerCase() === 'occupied') {
                statusClass = 'status-occupied';
            } else if (item.status && item.status.toLowerCase() === 'finished') {
                statusClass = 'status-finished';
            }

            var statusText = item.status || '';
            if (statusText === 'Occupied') statusText = '● BERLANGSUNG';
            else if (statusText === 'Reserved') statusText = 'DIJADWALKAN';
            else if (statusText === 'Finished') statusText = '✔ SELESAI';

            var title = item.title ? item.title : '(Tanpa Judul)';
            var time = item.time ? item.time : '--:--';

            html +=
                '<div class="event-card ' + cardClass + '">' +
                    '<div class="card-top">' +
                        '<div class="date-badge ' + badgeClass + '">' +
                            (item.date_label || 'AGENDA') +
                        '</div>' +
                        '<span class="status-badge ' + statusClass + '">' +
                            statusText +
                        '</span>' +
                    '</div>' +

                    '<div class="card-title">' + title + '</div>' +

                    '<div class="card-bottom">' +
                        '<div class="time-display">' +
                            '<i class="far fa-clock"></i> ' + time +
                        '</div>' +
                    '</div>' +
                '</div>';
        }

        container.innerHTML = html;
    }

    /* =========================================================
    ERROR HANDLER
    ========================================================= */
    function showError(msg) {
        var container = document.getElementById('list-container');
        if (!container) return;

        container.innerHTML =
            '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#fecaca;text-align:center;">' +
                '<i class="fas fa-triangle-exclamation fa-2x" style="margin-bottom:12px;"></i>' +
                '<p style="font-weight:700;">' + msg + '</p>' +
            '</div>';
    }

    /* =========================================================
    INIT
    ========================================================= */
    fetchSignageData();
    setInterval(fetchSignageData, REFRESH_INTERVAL);
    </script>

</body>
</html>
