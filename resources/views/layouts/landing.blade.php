<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LayananSarpras FTMM</title>

    {{-- CSS Libraries (Sama seperti Admin) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: "Nunito", "Segoe UI", arial; 
               background-color: #f8f9fa; }
        
        .text-primary {
            color: #741847 !important; 
        }

        .navbar-landing {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .navbar-brand { font-weight: 700; color: #741847; font-size: 1.5rem; }
        
        .hero-section {
            position: relative;
            /* Pastikan gambar ada di public/images/ */
            background: url('{{ asset("images/gedung-ftmms.JPG") }}') no-repeat center center/cover;
            padding: 120px 0 160px 0;
            color: white;
            text-align: center;
        }
        .hero-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(122, 21, 35, 0.8), rgba(13, 110, 253, 0.4));
        }
        .hero-content { position: relative; z-index: 2; }

            /* --- HERO SHORTCUTS (GLASS STYLE) --- */
        .hero-shortcuts .service-card {
            background: rgba(255, 255, 255, 0.15); /* Transparan */
            backdrop-filter: blur(10px);           /* Efek Blur */
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white; /* Teks jadi putih */
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        .hero-shortcuts .service-card:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .hero-shortcuts .service-title { color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
        .hero-shortcuts .service-desc { color: rgba(255, 255, 255, 0.8); }
        .hero-shortcuts .service-icon {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        
        .search-container { margin-top: -80px; position: relative; z-index: 10; }
        .glass-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .room-card {
            border: none; border-radius: 12px; overflow: hidden;
            transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            background: #fff;
        }
        .room-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .room-card img { height: 200px; object-fit: cover; }
        
        .btn-primary-custom {
            background-color: #741847; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; color: white;
        }
        .btn-primary-custom:hover { background-color: #741847; color: white; }

                /* --- SHORTCUT MENU --- */
        .service-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            padding: 25px 20px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            height: 100%;
            cursor: pointer;
            text-decoration: none;
            display: block;
            position: relative;
            overflow: hidden;
        }
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(13, 110, 253, 0.15);
            border-color: rgba(13, 110, 253, 0.3);
        }
        .service-icon {
            width: 64px; height: 64px;
            background: rgba(13, 110, 253, 0.1);
            color: #741847;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 15px auto;
            transition: all 0.3s ease;
        }
        .service-card:hover .service-icon {
            background: #741847; color: #ffffff; transform: scale(1.1) rotate(-5deg);
        }
        .service-title { font-weight: 700; color: #212529; font-size: 1.1rem; margin-bottom: 5px; }
        .service-desc { color: #6c757d; font-size: 0.85rem; margin: 0; line-height: 1.4; }

        /* --- SOP SECTION --- */
        .sop-section { background-color: #f8f9fa; padding: 70px 0; border-top: 1px solid #e9ecef; }
        .sop-card {
            background: white;
            border-left: 5px solid #741847;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 15px; transition: 0.2s;
            text-decoration: none;
        }
        .sop-card:hover { background: #eef6ff; transform: translateX(5px); }
    </style>
</head>
<body>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-landing fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-building me-2"></i> LayananSarpras
            </a>
            <div class="ms-auto">
                @auth
                    <a href="{{ route('admin.home') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary-custom btn-sm shadow-sm">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- CONTENT --}}
    @yield('content')

    {{-- FOOTER --}}
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; {{ date('Y') }} FTMM Universitas Airlangga.</p>
        </div>
    </footer>

    {{-- SCRIPTS WAJIB --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- INISIALISASI DATEPICKER LANGSUNG DISINI --}}
    <script>
        $(document).ready(function () {
            $('.datetime').datetimepicker({
                format: 'YYYY-MM-DD HH:mm', // Format Wajib agar lolos validasi Controller
                locale: 'en',
                sideBySide: true,
                icons: {
                    up: 'fas fa-chevron-up',
                    down: 'fas fa-chevron-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    time: 'fas fa-clock',
                    date: 'fas fa-calendar'
                }
            });
        });
    </script>

    @yield('scripts')
</body>
</html>