<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LayananSarpras FTMM</title>

    {{-- ═══════════════════════════════════════════════════════
         CSS — urutan sangat penting, jangan diubah
         1. Bootstrap 5        (base)
         2. Font Awesome       (icons)
         3. DataTables         (pakai versi BS5)
         4. Plugins
         5. CoreUI v4          (harus SETELAH Bootstrap 5)
         6. custom.css         (harus PALING BAWAH)
    ══════════════════════════════════════════════════════════ --}}

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
            <div class="container-fluid border-bottom px-4">
                <div class="d-flex align-items-center justify-content-between w-100 h-100 py-2">
                    
                    <div class="d-flex align-items-center">
                        {{-- Toggler mobile --}}
                        <button class="header-toggler d-lg-none me-3" type="button" id="sidebarToggleMobile" aria-label="Toggle sidebar">
                            <i class="fas fa-bars"></i>
                        </button>

                        {{-- Brand mobile --}}
                        <a class="header-brand d-lg-none" href="#">LayananFTMM</a>

                        {{-- Toggler desktop --}}
                        <button class="header-toggler d-none d-lg-inline-block me-3" type="button" id="sidebarToggleDesktop" aria-label="Toggle sidebar">
                            <i class="fas fa-bars"></i>
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
                                    <div class="dropdown-header bg-light py-2 text-center">
                                        <strong class="text-primary">{{ Auth::user()->name }}</strong><br>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                    </div>

                                    <a class="dropdown-item" href="{{ route('profile.password.edit') }}">
                                        <i class="fas fa-user me-2"></i> Profil
                                    </a>

                                    <div class="dropdown-divider"></div>

                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
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

                @if(session('success'))
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')

            </main>
        </div>

        {{-- ═══════ FOOTER ═══════ --}}
        <footer class="footer px-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center py-2">
                <span class="text-muted small">Dibuat dengan ❤️ dan kopi FamilyMart.</span>
                <span class="text-muted small">&copy; {{ date('Y') }} <strong>FTMM Universitas Airlangga</strong></span>
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

    @if(session('success'))
    <script>
    Swal.fire({
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonColor: '#741847',
        confirmButtonText: 'OK'
    });
    </script>
    @endif

    @if($errors->any())
    <script>
    Swal.fire({
        title: 'Gagal!',
        text: '{{ $errors->first() }}',
        icon: 'error',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Coba Lagi'
    });
    </script>
    @endif

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

    @yield('scripts')

</body>
</html>
