<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vertical Signage - Ruang Sidang</title>
    
    {{-- 1. Gunakan Tailwind via CDN agar styling mirip React App tanpa build step --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- 2. Font Inter & Roboto Mono (Supaya modern) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Roboto+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    
    {{-- 3. Icon (FontAwesome) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; /* Navy-900 */
            color: white;
            overflow: hidden; /* Hilangkan scrollbar browser */
        }
        
        /* Custom Scrollbar Hide */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Glassmorphism Classes */
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        /* Animasi Marquee Vertical */
        @keyframes marquee-vertical {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }
        .animate-scroll {
            animation: marquee-vertical 40s linear infinite;
        }
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Background Gradient seperti di React */
        .bg-gradient-radial {
            background: radial-gradient(circle at center, transparent 0%, rgba(5,10,20,0.8) 100%);
        }
    </style>

    <script>
        // Konfigurasi Tailwind Custom Colors (Mirip design sistem kamu)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 900: '#0f172a', 950: '#020617' },
                        electric: { 400: '#2dd4bf', 500: '#14b8a6' }, // Teal/Cyan accent
                    },
                    fontFamily: {
                        mono: ['Roboto Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-screen w-screen relative selection:bg-electric-500 selection:text-white">

    {{-- BACKGROUND (Sama persis dengan React App) --}}
    <div class="absolute inset-0 z-0 scale-105"
         style="background-image: url('https://picsum.photos/1080/1920?grayscale&blur=2'); background-size: cover; background-position: center; filter: blur(8px) brightness(0.4);">
    </div>
    <div class="absolute inset-0 bg-navy-900/70 z-0 mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-gradient-radial z-0 pointer-events-none"></div>

    {{-- MAIN CONTENT (Vertical Layout) --}}
    <div class="relative z-10 flex flex-col h-full p-8 gap-6">

        {{-- 1. HEADER --}}
        <header class="shrink-0 glass-panel rounded-3xl p-6 flex flex-col items-center justify-center text-center border-b-4 border-electric-500">
            <h1 class="text-4xl font-extrabold tracking-wider text-white mb-2">RUANG SIDANG LT. 10</h1>
            <p class="text-electric-400 text-lg font-mono tracking-widest uppercase">Agenda Rapat Hari Ini</p>
            
            {{-- Clock --}}
            <div class="mt-6 flex flex-col items-center">
                <div id="clock-time" class="text-8xl font-black font-mono leading-none tracking-tight text-white drop-shadow-lg">00:00</div>
                <div id="clock-date" class="text-2xl text-gray-400 font-medium mt-2 uppercase tracking-wide">...</div>
            </div>
        </header>

        {{-- 2. LIST CONTENT (AUTO SCROLL) --}}
        <main class="flex-1 min-h-0 relative rounded-3xl overflow-hidden glass-panel border-t border-white/10">
            {{-- Header Kecil dalam Panel --}}
            <div class="absolute top-0 left-0 w-full p-4 bg-navy-900/80 backdrop-blur-md border-b border-white/5 z-20 flex justify-between items-center">
                <span class="text-sm font-bold text-gray-400 uppercase tracking-widest"><i class="fas fa-list-ul me-2"></i> Daftar Kegiatan</span>
                <span class="text-xs px-2 py-1 rounded bg-electric-500/10 text-electric-400 border border-electric-500/20 animate-pulse-slow">
                    LIVE UPDATE
                </span>
            </div>

            {{-- Container Scroll --}}
            <div id="list-container" class="h-full pt-16 pb-4 px-4 overflow-hidden relative">
                {{-- Data akan diinject via JS disini --}}
                <div id="loading-state" class="flex flex-col items-center justify-center h-full text-white/30">
                    <i class="fas fa-circle-notch fa-spin fa-3x mb-4"></i>
                    <p class="text-xl animate-pulse">Memuat Jadwal...</p>
                </div>
            </div>
            
            {{-- Gradient Masking (Efek pudar atas bawah) --}}
            <div class="absolute top-14 left-0 w-full h-12 bg-gradient-to-b from-navy-900 via-navy-900/80 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-full h-12 bg-gradient-to-t from-navy-900 via-navy-900/80 to-transparent z-10 pointer-events-none"></div>
        </main>

        {{-- 3. FOOTER / RUNNING TEXT --}}
        <footer class="shrink-0 glass-panel rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-electric-500/20 flex items-center justify-center text-electric-400 shrink-0 border border-electric-500/50">
                <i class="fas fa-info text-xl"></i>
            </div>
            <div class="flex-1 overflow-hidden relative h-6">
                <div class="absolute whitespace-nowrap animate-[marquee_15s_linear_infinite] text-lg font-medium text-gray-300">
                    Selamat Datang di Fakultas Teknologi Maju dan Multidisiplin • Harap menjaga ketenangan selama rapat berlangsung • Dilarang makan dan minum di dalam ruang sidang • Gunakan fasilitas dengan bijak.
                </div>
            </div>
        </footer>

    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        // --- 1. CONFIGURATION ---
        const API_URL = "{{ route('api.signage.index') }}"; // Route API yang sudah kamu buat
        const FILTER_ROOM = "Lt. 10"; // Keyword Ruangan (Case insensitive nanti)
        const FILTER_TYPE = "Rapat";  // Jenis Kegiatan
        const REFRESH_INTERVAL = 60000; // 60 Detik

        // --- 2. CLOCK LOGIC ---
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            
            document.getElementById('clock-time').innerText = timeStr;
            document.getElementById('clock-date').innerText = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- 3. DATA FETCHING & RENDERING ---
        async function fetchSignageData() {
            try {
                const response = await fetch(API_URL);
                const json = await response.json();
                
                // Ambil data sidang_rapat dari API
                // Struktur API kamu: { sidang_rapat: [...] }
                const rawData = json.sidang_rapat || [];

                // FILTERING:
                // 1. Ruangan mengandung kata "Lt. 10" (atau "R. Sidang Lt. 10")
                // 2. Jenis Kegiatan adalah "Rapat"
                const filteredData = rawData.filter(item => {
                    const roomName = (item.room || "").toLowerCase();
                    const activityType = (item.jenis || "").toLowerCase();
                    
                    return roomName.includes(FILTER_ROOM.toLowerCase()) && 
                           activityType.includes(FILTER_TYPE.toLowerCase());
                });

                renderList(filteredData);

            } catch (error) {
                console.error("Gagal ambil data:", error);
                // Opsional: Tampilkan pesan error di layar jika perlu
            }
        }

        function renderList(data) {
            const container = document.getElementById('list-container');
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-gray-500 opacity-60">
                        <i class="fas fa-calendar-times fa-4x mb-4"></i>
                        <p class="text-2xl font-bold">TIDAK ADA JADWAL RAPAT</p>
                        <p class="text-lg">Ruangan Kosong Hari Ini</p>
                    </div>
                `;
                return;
            }

            // Generate HTML Card Item
            const itemsHtml = data.map(item => `
                <div class="mb-4 p-5 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-electric-500/30 transition-all group relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-electric-500"></div>
                    
                    <div class="flex flex-col gap-3 pl-2">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2 text-electric-400 font-mono font-bold text-xl bg-electric-500/10 px-3 py-1 rounded-lg border border-electric-500/20">
                                <i class="far fa-clock"></i>
                                ${item.time}
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${getStatusColor(item.status)}">
                                ${item.status}
                            </span>
                        </div>

                        <h3 class="text-2xl font-bold text-white leading-tight group-hover:text-electric-400 transition-colors line-clamp-2">
                            ${item.title}
                        </h3>

                        <div class="flex flex-col gap-1 pt-2 border-t border-white/5 mt-1">
                            <div class="flex items-center gap-2 text-gray-300 text-sm">
                                <i class="fas fa-user-tie w-5 text-center text-electric-500"></i>
                                <span class="font-medium truncate">PIC: ${item.pic || '-'}</span>
                            </div>
                            </div>
                    </div>
                </div>
            `).join('');

            // LOGIC AUTO SCROLL (Marquee Vertical)
            // Jika item sedikit, tampilkan statis. Jika banyak (> 3), buat scroll looping.
            if (data.length > 3) {
                // Duplikat konten untuk seamless looping
                const wrapper = `
                    <div class="animate-scroll hover:[animation-play-state:paused]">
                        ${itemsHtml}
                        ${itemsHtml} </div>
                `;
                container.innerHTML = wrapper;
            } else {
                container.innerHTML = `<div class="flex flex-col">${itemsHtml}</div>`;
            }
        }

        // Helper untuk warna status
        function getStatusColor(status) {
            status = (status || "").toLowerCase();
            if (status === 'occupied' || status === 'berlangsung') return 'bg-red-500/20 text-red-400 border border-red-500/30';
            if (status === 'finished' || status === 'selesai') return 'bg-gray-700 text-gray-400 border border-gray-600';
            return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'; // Reserved / Terjadwal
        }

        // Add custom keyframe for footer marquee
        const styleSheet = document.createElement("style");
        styleSheet.innerText = `
            @keyframes marquee {
                0% { transform: translateX(100%); }
                100% { transform: translateX(-100%); }
            }
        `;
        document.head.appendChild(styleSheet);

        // --- 4. INIT ---
        fetchSignageData();
        setInterval(fetchSignageData, REFRESH_INTERVAL);

    </script>
</body>
</html>