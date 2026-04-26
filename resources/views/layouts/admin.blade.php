<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>FTMM-Nexus (LayananTerpadu-FTMM)</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

    {{-- 1. Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- 2. Font Awesome 6 (lebih lengkap dari v5) --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    {{-- 3. DataTables — versi BS5 --}}
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css" rel="stylesheet">

    {{-- 4. Plugins --}}
    {{-- Select2 dengan tema BS5 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    {{-- Tempus Dominus v6 — pengganti bootstrap-datetimepicker yang BS5-native --}}
    <link href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.7.7/dist/css/tempus-dominus.min.css" rel="stylesheet">

    {{-- Dropzone --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" rel="stylesheet">

    {{-- 5. CoreUI v4 — harus setelah Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/css/coreui.min.css" rel="stylesheet">

    {{-- 6. Custom CSS — selalu paling bawah --}}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    @yield('styles')
    @stack('styles')
</head>


<body class="sidebar-enable sidebar-fixed">

    {{-- Sidebar dimuat via partial --}}
    @include('partials.menu')

    {{-- Wrapper utama konten --}}
    <div class="wrapper d-flex flex-column min-vh-100">

        {{-- ═══════ HEADER ═══════ --}}
        <header class="header header-sticky p-0">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between w-100 h-100 py-2">
                    
                    <div class="d-flex align-items-center">
                        {{-- Toggler mobile --}}
                        <button class="header-toggler d-lg-none me-3" type="button" id="sidebarToggleMobile" aria-label="Toggle sidebar">
                            <i class="fas fa-bars"></i>
                        </button>

                        {{-- Brand mobile --}}
                        <a class="header-brand d-lg-none" href="#">FTMM-Nexus</a>

                        {{-- Toggler desktop --}}
                        <button class="header-toggler d-none d-lg-inline-flex me-3 align-items-center justify-content-center" 
                                type="button" id="sidebarToggleDesktop" aria-label="Toggle sidebar"
                                style="width:32px; height:32px; border-radius:8px; transition: background 0.2s;">
                            <i class="fas fa-angles-left" id="sidebarToggleIcon" style="transition: transform 0.3s ease; font-size: 0.95rem;"></i>
                        </button>
                    </div>

                    <ul class="header-nav ms-auto">
                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link p-0" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                                    <div class="avatar-circle">
                                        @if(Auth::user()->photo)
                                            <img src="{{ asset('storage/'.Auth::user()->photo) }}" alt="{{ Auth::user()->name }}">
                                        @else
                                            @php
                                                $names = explode(' ', trim(Auth::user()->name));
                                                $initials = strtoupper(
                                                    substr($names[0], 0, 1) .
                                                    (isset($names[1]) ? substr($names[1], 0, 1) : '')
                                                );
                                            @endphp
                                            <div class="avatar-initial">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                    </div>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end pt-0 shadow-sm">
                                    <div class="dropdown-header border-bottom py-2 text-center">
                                        <strong class="text-primary">{{ Auth::user()->name }}</strong><br>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                    </div>

                                    <a class="dropdown-item border-bottom" href="{{ route('profile.password.edit') }}">
                                        <i class="fas fa-user me-2 text-primary"></i>Profil
                                    </a>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}" 
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2 text-danger"></i>Keluar
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endauth
                    </ul>

                </div>
            </div>
        </header>

        {{-- ═══════ KONTEN UTAMA ═══════ --}}
        <div class="body flex-grow-1">
            <main class="container-fluid px-4 py-3">

                @yield('content')

            </main>
        </div>

        {{-- ═══════ FOOTER ═══════ --}}
        <style>
            /* Animasi detak jantung */
            .heart-pulse {
                display: inline-block;
                animation: pulse 1.5s infinite;
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                10% { transform: scale(1.2); }
                20% { transform: scale(1); }
                30% { transform: scale(1.2); }
                40% { transform: scale(1); }
                100% { transform: scale(1); }
            }

            /* Kustomisasi link USI FTMM biar lebih rapi pas di-hover */
            .footer-link {
                color: #495057;
                text-decoration: none;
                transition: color 0.3s ease;
            }
            .footer-link:hover {
                color: #0d6efd; /* Ganti dengan warna tema website lo */
            }
        </style>

        <footer class="footer px-4 py-3 border-top bg-light mt-auto">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 gap-2">
                
                <span class="text-secondary small fw-medium text-center text-md-start">
                    Dibuat dengan <span class="heart-pulse text-danger">❤️</span> dan kopi FamilyMart.
                </span>
                
                <span class="text-secondary small text-center text-md-end">
                    &copy; {{ date('Y') }} 
                    <strong>
                        <a href="#" class="footer-link">USI FTMM</a>
                    </strong>
                </span>
                
            </div>
        </footer>

    </div>

    {{-- Form logout tersembunyi --}}
    <form id="logoutform" action="{{ route('logout') }}" method="POST" class="d-none">
        {{ csrf_field() }}
    </form>


    {{-- ═══════════════════════════════════════════════════════
         SCRIPTS — urutan penting
         1. jQuery           (harus pertama)
         2. Bootstrap 5      (popper sudah bundled)
         3. CoreUI v4 JS     (harus setelah Bootstrap 5)
         4. Plugins
         5. main.js          (custom, harus paling akhir)
    ══════════════════════════════════════════════════════════ --}}

    {{-- 1. jQuery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- 2. Bootstrap 5 --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- 3. CoreUI v4 --}}
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/js/coreui.bundle.min.js"></script>

    {{-- 4. Plugins --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.7.7/dist/js/tempus-dominus.min.js"></script>

    {{-- DataTables BS5 --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

    {{-- Dropzone --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

    {{-- CKEditor --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

    {{-- 5. Custom JS — harus paling akhir --}}
    <script src="{{ asset('js/main.js') }}"></script>


    {{-- ═══════ DATATABLES GLOBAL CONFIG ═══════ --}}
    <script>
    $(function () {
        let copyButtonTrans  = '{{ trans('global.datatables.copy') }}';
        let csvButtonTrans   = '{{ trans('global.datatables.csv') }}';
        let excelButtonTrans = '{{ trans('global.datatables.excel') }}';
        let pdfButtonTrans   = '{{ trans('global.datatables.pdf') }}';
        let printButtonTrans = '{{ trans('global.datatables.print') }}';
        let colvisButtonTrans = '{{ trans('global.datatables.colvis') }}';

        // CATATAN: buttons default di sini HANYA sebagai fallback untuk tabel sederhana.
        // Tabel yang punya buttons kustom sendiri (seperti index kegiatan) akan override ini.
        // dom: 'Bfrtip' wajib ada agar DataTables tahu di mana harus inject tombol ke DOM.
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'copy',   className: 'btn-secondary btn-sm', text: copyButtonTrans,   exportOptions: { columns: ':visible' } },
                { extend: 'csv',    className: 'btn-secondary btn-sm', text: csvButtonTrans,    exportOptions: { columns: ':visible' } },
                { extend: 'excel',  className: 'btn-secondary btn-sm', text: excelButtonTrans,  exportOptions: { columns: ':visible' } },
                { extend: 'pdf',    className: 'btn-secondary btn-sm', text: pdfButtonTrans,    exportOptions: { columns: ':visible' } },
                { extend: 'print',  className: 'btn-secondary btn-sm', text: printButtonTrans,  exportOptions: { columns: ':visible' } },
                { extend: 'colvis', className: 'btn-secondary btn-sm', text: colvisButtonTrans }
            ]
        });

        $.fn.dataTable.ext.classes.sPageButton = '';
    });

    window.getStandardDtButtons = function (options) {
        options = options || {};
        let selectAllBtn  = '{{ trans('global.select_all') }}';
        let selectNoneBtn = '{{ trans('global.deselect_all') }}';

        let buttons = [
            { extend: 'selectAll',  text: '<i class="fas fa-check-double me-1"></i> ' + selectAllBtn,  className: 'btn-primary btn-sm' },
            { extend: 'selectNone', text: '<i class="fas fa-times me-1"></i> ' + selectNoneBtn, className: 'btn-primary btn-sm' }
        ];

        if (options.copyAction && typeof options.copyAction === 'function') {
            buttons.push({ text: '<i class="fas fa-copy me-1"></i> Copy', className: 'btn-secondary btn-sm', enabled: false, action: options.copyAction });
        } else {
            buttons.push({ extend: 'copy', text: '<i class="fas fa-copy me-1"></i> Copy', className: 'btn-secondary btn-sm', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } });
        }

        buttons.push(
            { extend: 'csv',    text: '<i class="fas fa-file-export me-1"></i> CSV',    className: 'btn-secondary btn-sm', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } },
            { extend: 'excel',  text: '<i class="fas fa-file-excel me-1"></i> Excel',  className: 'btn-secondary btn-sm', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } },
            { extend: 'pdf',    text: '<i class="fas fa-file-pdf me-1"></i> PDF',      className: 'btn-secondary btn-sm', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } },
            { extend: 'print',  text: '<i class="fas fa-print me-1"></i> Cetak',       className: 'btn-secondary btn-sm', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } },
            { extend: 'colvis', text: '<i class="fas fa-columns me-1"></i> Kolom',     className: 'btn-secondary btn-sm' }
        );

        return buttons;
    };
    </script>


    {{-- ═══════ UTILITIES ═══════ --}}
    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Yakin hapus?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. Notifikasi Sukses (Auto-close)
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{!! session('success') !!}',
                showConfirmButton: false,
                timer: 2500 // Hilang otomatis dalam 2.5 detik
            });
        @endif

        // 2. Notifikasi Error / Gagal (Custom Pesan)
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{!! session('error') !!}',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tutup'
            });
        @endif

        // 3. Notifikasi Validasi Form Error
        @if($errors->any())
            @if(session('saran_ruangan') && session('saran_ruangan')->isNotEmpty())
                {{-- Khusus error bentrok: tampilkan dengan saran ruangan --}}
                Swal.fire({
                    title: 'Ruangan Tidak Tersedia!',
                    icon: 'warning',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Pilih Ruangan Lain',
                    html: `
                        <p>Bentrok dengan kegiatan: <strong>{{ session('bentrok_kegiatan') }}</strong></p>
                        <hr>
                        <p class="mb-2 text-start"><strong>🏫 Ruangan alternatif yang tersedia:</strong></p>
                        <ul class="text-start ps-3">
                            @foreach(session('saran_ruangan') as $saran)
                                <li>
                                    <strong>{{ $saran->nama }}</strong>
                                    @if($saran->kapasitas) &mdash; Kapasitas: {{ $saran->kapasitas }} org @endif
                                    @if($saran->lokasi) &mdash; {{ $saran->lokasi }} @endif
                                </li>
                            @endforeach
                        </ul>
                    `
                });
            @elseif(session('bentrok_kegiatan'))
                {{-- Bentrok tapi tidak ada ruangan alternatif --}}
                Swal.fire({
                    title: 'Ruangan Tidak Tersedia!',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Coba Lagi',
                    html: `
                        <p>Bentrok dengan kegiatan: <strong>{{ session('bentrok_kegiatan') }}</strong></p>
                        <p class="text-muted">Tidak ada ruangan alternatif yang tersedia pada waktu tersebut. Silahkan ganti waktu kegiatan!</p>
                    `
                });
            @else
                {{-- Error validasi biasa (bukan bentrok) --}}
                Swal.fire({
                    title: 'Gagal!',
                    text: '{!! $errors->first() !!}',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Coba Lagi'
                });
            @endif
        @endif

        // 4. Notifikasi Warning (Peringatan)
        @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: '{!! session('warning') !!}',
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Tutup'
            });
        @endif

        // 5. Notifikasi Info
        @if(session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: '{!! session('info') !!}',
                confirmButtonColor: '#0dcaf0',
                confirmButtonText: 'Tutup'
            });
        @endif
        
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Jam realtime di header (jika ada elemen #current-time)
        const el = document.getElementById('current-time');
        if (!el) return;
        const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        function tick() {
            const n = new Date();
            el.innerText = `${days[n.getDay()]}, ${n.getDate()} ${months[n.getMonth()]} ${n.getFullYear()} ${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}:${String(n.getSeconds()).padStart(2,'0')}`;
        }
        setInterval(tick, 1000);
        tick();
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn  = document.getElementById('sidebarToggleDesktop');
        const icon = document.getElementById('sidebarToggleIcon');
        const sidebar = document.getElementById('sidebar');

        if (!btn || !icon || !sidebar) return;

        function updateIcon() {
            const isNarrow = sidebar.classList.contains('sidebar-narrow');
            // Narrow = sidebar mengecil = panah menunjuk kanan (expand)
            icon.style.transform = isNarrow ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        // Update saat tombol diklik
        btn.addEventListener('click', function () {
            // Beri jeda kecil agar CoreUI selesai toggle class-nya dulu
            setTimeout(updateIcon, 50);
        });

        // Cek kondisi awal saat halaman load
        updateIcon();

        // Observer: jaga-jaga kalau class berubah dari luar (misal CoreUI restore dari localStorage)
        new MutationObserver(updateIcon).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    });
    </script>

    @yield('scripts')
    <style>
        .fab-wrapper {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* State Sembunyi (Ngintip) - Aktif secara default */
        .fab-wrapper.is-hidden {
            transform: translateX(45px); 
            opacity: 0.6;
        }
        
        /* Memberikan sedikit efek hover saat sembunyi agar user tahu itu tombol */
        .fab-wrapper.is-hidden:hover {
            transform: translateX(35px);
            opacity: 1;
        }

        /* Tombol Sembunyi (Panah) - Diperbesar & dipindah ke tengah kiri */
        .fab-hide-btn {
            position: absolute;
            top: 50%;
            left: -20px;
            transform: translateY(-50%); /* Posisi vertikal di tengah robot */
            width: 32px;
            height: 32px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #6c757d;
            box-shadow: -2px 0px 8px rgba(0,0,0,0.15); /* Shadow diarahkan ke kiri */
            cursor: pointer;
            z-index: 100; /* Pastikan posisinya paling depan */
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .fab-hide-btn:hover { 
            background: #f8f9fa; 
            color: #741847;
            /* Override transform biar translateY(-50%) gak ketimpa */
            transform: translateY(-50%) scale(1.1); 
        }

        /* HILANGKAN tombol panah kalau robot lagi sembunyi (ngintip). 
        User cukup klik badan robotnya untuk mengeluarkan. */
        .fab-wrapper.is-hidden .fab-hide-btn {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .fab-menu {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            opacity: 0;
            visibility: hidden;
            position: absolute;
            bottom: 75px; 
            right: 0;
            transform: translateY(20px) scale(0.8);
            transform-origin: bottom right;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .fab-wrapper.active .fab-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .mascot-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            /* Tambahkan drop-shadow putih yang lumayan tebal */
            filter: drop-shadow(0px 0px 8px rgba(255, 255, 255, 1)); 
        }

        .fab-item {
            background-color: #ffffff;
            color: #333;
            padding: 10px 18px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            text-decoration: none;
            /* Tambahan: Kasih border default warna abu-abu terang */
            border: 2px solid #e9ecef; 
            white-space: nowrap;
        }

        .fab-item:hover { 
            transform: translateX(-5px); 
            background-color: #f8f9fa; 
        }
        
        /* Warna border berubah sesuai fungsi pas di-hover */
        .fab-item.helpdesk:hover { border-color: #0d6efd; color: #0d6efd; }
        .fab-item.ticketing:hover { border-color: #fd7e14; color: #fd7e14; }

        .fab-main-btn {
            width: 60px;
            height: 60px;
            background: #ffffff;
            border: 2px solid #741847; 
            box-shadow: 0 6px 15px rgba(116, 24, 71, 0.3);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            position: relative;
        }

        /* --- CSS UNTUK TOOLTIP "Butuh bantuan?" --- */
        .fab-tooltip {
            position: absolute;
            right: 70px; /* Posisi di sebelah kiri tombol */
            top: 50%;
            transform: translateY(-50%);
            background-color: #333333;
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            pointer-events: none; /* Biar gak ganggu klik */
        }

        /* Segitiga kecil (panah) yang nunjuk ke maskot */
        .fab-tooltip::after {
            content: '';
            position: absolute;
            right: -5px;
            top: 50%;
            transform: translateY(-50%);
            border-width: 5px 0 5px 6px;
            border-style: solid;
            border-color: transparent transparent transparent #333333;
        }

        /* Saat mode hidden (ngintip), tooltip tetap muncul tapi kita sesuaikan posisinya */
        .fab-wrapper.is-hidden:hover .fab-tooltip {
            opacity: 1 !important;
            visibility: visible !important;
            right: 75px; /* Kita dorong lebih ke kiri sedikit karena tombolnya lagi geser ke kanan */
            background-color: #741847; /* Opsional: Ubah warna tooltip pas hidden biar kontras */
        }

        /* Hilangkan tooltip HANYA saat menu bantuan sedang terbuka (active) */
        .fab-wrapper.active .fab-tooltip {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        .fab-icon { display: inline-block; animation: botHover 3s ease-in-out infinite; }
        @keyframes botHover {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }

        /* Rotasi panah saat tersembunyi */
        .fab-wrapper.is-hidden #toggleHide i { transform: rotate(180deg); }
    </style>

    <div class="fab-wrapper is-hidden" id="supportFab">
        <div class="fab-hide-btn" id="toggleHide" title="Tampilkan">
            <i class="fas fa-chevron-right"></i>
        </div>

        <div class="fab-menu">
            <a href="#" class="fab-item helpdesk">
                <span>Helpdesk Admin</span> <i class="fas fa-headset text-primary"></i>
            </a>
            <a href="https://ticketing.ftmm.ac.id" target="_blank" class="fab-item ticketing">
                <span>Ticketing Support</span> <i class="fas fa-ticket-alt text-warning"></i>
            </a>
        </div>

        <button class="fab-main-btn" id="mainBtn">
            <span class="fab-tooltip">Butuh bantuan?</span>
            
            <img src="{{ asset('images/mascot.png') }}" 
                class="fab-icon mascot-img" 
                id="mascotIcon" 
                data-normal="{{ asset('images/mascot.png') }}" 
                data-active="{{ asset('images/mascot2.png') }}" 
                alt="Maskot FTMM">
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('supportFab');
            const mainBtn = document.getElementById('mainBtn');
            const mascot = document.getElementById('mascotIcon');
            const toggleHide = document.getElementById('toggleHide'); 

            function updateMascotPose() {
                if (wrapper.classList.contains('active')) {
                    mascot.src = mascot.getAttribute('data-active');
                } else {
                    mascot.src = mascot.getAttribute('data-normal');
                }
            }

            // Aksi saat maskot diklik
            mainBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                
                if(wrapper.classList.contains('is-hidden')) {
                    wrapper.classList.remove('is-hidden');
                    mascot.src = mascot.getAttribute('data-normal');
                } else {
                    wrapper.classList.toggle('active');
                    updateMascotPose(); 
                }
            });

            // Aksi saat tombol panah sembunyi diklik
            if(toggleHide) {
                toggleHide.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    
                    wrapper.classList.add('is-hidden');
                    wrapper.classList.remove('active'); // Pastikan menu tertutup
                    
                    // Kembalikan pose ke normal saat sembunyi
                    mascot.src = mascot.getAttribute('data-normal'); 
                });
            }

            // Reset pose jika klik di luar area
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    wrapper.classList.remove('active');
                    updateMascotPose();
                }
            });
        });
    </script>
</body>
</html>
