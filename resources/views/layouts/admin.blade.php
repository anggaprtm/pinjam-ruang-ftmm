<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LayananSarpras FTMM</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" rel="stylesheet" />

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/select/1.3.0/css/select.dataTables.min.css" rel="stylesheet" />

    <!-- Plugins -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.0/css/perfect-scrollbar.min.css" rel="stylesheet" />

    <!-- CoreUI -->
    <link href="https://unpkg.com/@coreui/coreui@3.2/dist/css/coreui.min.css" rel="stylesheet" />

    <!-- Custom CSS (HARUS PALING BAWAH) -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />

    @yield('styles')
    @stack('styles')
</head>


<body class="c-app">
    @include('partials.menu')
    <div class="sidebar-backdrop"></div>
    <script>
        (function() {
            if (localStorage.getItem('sidebar_minimized') === 'true') {
                var sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('c-sidebar-minimized');
                }
            }
        })();
    </script>
    <div class="c-wrapper">
        <header class="c-header c-header-fixed px-3">
            <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
                <i class="fas fa-fw fa-bars"></i>
            </button>

            <a class="c-header-brand d-lg-none" href="#">LayananSarpras FTMM</a>

            <button class="c-header-toggler mfs-3 d-md-down-none" type="button" responsive="true">
                <i class="fas fa-fw fa-bars"></i>
            </button>

            <ul class="c-header-nav ml-auto">
                @if(count(config('panel.available_languages', [])) > 1)
                    <li class="c-header-nav-item dropdown d-md-down-none">
                        <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            {{ strtoupper(app()->getLocale()) }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            @foreach(config('panel.available_languages') as $langLocale => $langName)
                                <a class="dropdown-item" href="{{ url()->current() }}?change_language={{ $langLocale }}">{{ strtoupper($langLocale) }} ({{ $langName }})</a>
                            @endforeach
                        </div>
                    </li>
                @endif


            </ul>
        </header>

        <div class="c-body">
            <main class="c-main">
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="row mb-2">
                            <div class="col-lg-12">
                                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                            </div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="row mb-2">
                            <div class="col-lg-12">
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    @endif
                    @yield('content')

                </div>

            </main>

            <footer class="c-footer">
                <div>
                    <span>Dibuat dengan ❤️ dan kopi FamilyMart.</span>
                </div>
                <div class="mfs-auto">
                    <span>&copy; {{ date('Y') }} <strong>FTMM Universitas Airlangga</strong></span>
                </div>
            </footer>

            <form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </div>
    </div>
    
    <!-- Floating Calendar Button -->
    <button id="floating-calendar-btn" class="btn btn-primary btn-lg" type="button" data-bs-toggle="modal" data-bs-target="#calendarModal" style="position: fixed; bottom: 35px; right: 20px; z-index: 1050; border-radius: 50%; width: 60px; height: 60px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
        <i class="fas fa-calendar-alt"></i>
    </button>

    <!-- Calendar Modal -->
    <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calendarModalLabel">Kalender Hari Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Embed Google Calendar -->
                    <div class="ratio ratio-16x9">
                        <iframe src="https://calendar.google.com/calendar/embed?src=id.indonesian%23holiday%40group.v.calendar.google.com&src=93dbfe7f13693cb27cafb0ec37d538ee11c7ac8def40063e6c163f35fb0387cb%40group.calendar.google.com&ctz=Asia%2FJakarta" style="border: 0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.5.0/perfect-scrollbar.min.js"></script>
    <script src="https://unpkg.com/@coreui/coreui@3.2/dist/js/coreui.min.js"></script>
    <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script src="//cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
    <script src="//cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/16.0.0/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        $(function() {
    let copyButtonTrans = '{{ trans('global.datatables.copy') }}'
    let csvButtonTrans = '{{ trans('global.datatables.csv') }}'
    let excelButtonTrans = '{{ trans('global.datatables.excel') }}'
    let pdfButtonTrans = '{{ trans('global.datatables.pdf') }}'
    let printButtonTrans = '{{ trans('global.datatables.print') }}'
    let colvisButtonTrans = '{{ trans('global.datatables.colvis') }}'
    let selectAllButtonTrans = '{{ trans('global.select_all') }}'
    let selectNoneButtonTrans = '{{ trans('global.deselect_all') }}'

    let languages = {
        'id': 'https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json'
    };

    $.extend(true, $.fn.dataTable.Buttons.defaults.dom.button, { className: 'btn' })
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
        url: languages['{{ app()->getLocale() }}']
        },
        columnDefs: [{
            orderable: false,
            className: 'select-checkbox',
            targets: 0
        }, {
            orderable: false,
            searchable: false,
            targets: -1
        }, {
        targets: 4, 
        type: 'date' 
        }, {
        targets: 5, 
        type: 'date' 
        }],
        select: {
        style:      'multi+shift',
        selector: 'td:first-child'
        },
        order: [],
        scrollX: true,
        pageLength: 50,
        dom: 'lBfrtip<"actions">',
        buttons: [
        {
            extend: 'selectAll',
            className: 'btn-primary',
            text: selectAllButtonTrans,
            exportOptions: {
            columns: ':visible'
            },
            action: function(e, dt) {
            e.preventDefault()
            dt.rows().deselect();
            dt.rows({ search: 'applied', page: 'current' }).select();
            }
        },
        {
            extend: 'selectNone',
            className: 'btn-primary',
            text: selectNoneButtonTrans,
            exportOptions: {
            columns: ':visible'
            }
        },
        {
            extend: 'copy',
            className: 'btn-default',
            text: copyButtonTrans,
            exportOptions: {
            columns: ':visible'
            }
        },
        {
            extend: 'csv',
            className: 'btn-default',
            text: csvButtonTrans,
            exportOptions: {
            columns: ':visible'
            }
        },
        {
            extend: 'excel',
            className: 'btn-default',
            text: excelButtonTrans,
            exportOptions: {
            columns: ':visible'
            }
        },
        {
            extend: 'pdf',
            className: 'btn-default',
            text: pdfButtonTrans,
            exportOptions: {
            columns: ':visible'
            }
        },
        {
            extend: 'print',
            className: 'btn-default',
            text: printButtonTrans,
            exportOptions: {
            columns: ':visible'
            }
        },
        {
            extend: 'colvis',
            className: 'btn-default',
            text: colvisButtonTrans,
            exportOptions: {
            columns: ':visible'
            }
        }
        ]
    });

    $.fn.dataTable.ext.classes.sPageButton = '';
    });

    // Expose a helper to get a standardized set of DataTables buttons.
    // Use: let dtButtons = getStandardDtButtons();
    window.getStandardDtButtons = function(options) {
        options = options || {};
        // default translations (same variables from above closure)
        let copyBtn = '{{ trans('global.datatables.copy') }}';
        let csvBtn = '{{ trans('global.datatables.csv') }}';
        let excelBtn = '{{ trans('global.datatables.excel') }}';
        let pdfBtn = '{{ trans('global.datatables.pdf') }}';
        let printBtn = '{{ trans('global.datatables.print') }}';
        let colvisBtn = '{{ trans('global.datatables.colvis') }}';
        let selectAllBtn = '{{ trans('global.select_all') }}';
        let selectNoneBtn = '{{ trans('global.deselect_all') }}';

        let buttons = [];
        buttons.push(
            { extend: 'selectAll', text: '<i class="fas fa-check-double me-2"></i> ' + selectAllBtn, className: 'btn-primary' },
            { extend: 'selectNone', text: '<i class="fas fa-times me-2"></i> ' + selectNoneBtn, className: 'btn-primary' }
        );

        // Copy button: allow caller to pass a custom action function in options.copyAction
        if (options.copyAction && typeof options.copyAction === 'function') {
            buttons.push({
                text: '<i class="fas fa-copy me-2"></i> ' + copyBtn,
                className: 'btn-secondary',
                enabled: false,
                action: options.copyAction
            });
        } else {
            buttons.push({ extend: 'copy', text: '<i class="fas fa-copy me-2"></i> ' + copyBtn, className: 'btn-secondary', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } });
        }

        // Exports (disabled until selection)
        buttons.push(
            { extend: 'csv', text: '<i class="fas fa-file-export me-2"></i> CSV', className: 'btn-secondary', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } },
            { extend: 'excel', text: '<i class="fas fa-file-excel me-2"></i> Excel', className: 'btn-secondary', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } },
            { extend: 'pdf', text: '<i class="fas fa-file-pdf me-2"></i> PDF', className: 'btn-secondary', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } },
            { extend: 'print', text: '<i class="fas fa-print me-2"></i> Cetak', className: 'btn-secondary', enabled: false, exportOptions: { columns: ':visible', modifier: { selected: true } } }
        );

        // Column visibility
        buttons.push({ extend: 'colvis', text: '<i class="fas fa-columns me-2"></i> ' + colvisBtn, className: 'btn-secondary' });

        return buttons;
    };

    </script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: "Yakin hapus?",
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("delete-form-" + id).submit();
                }
            });
        }
    </script>
    <script>
        @if(session('success'))
            Swal.fire({
                title: "Berhasil!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonColor: "#3085d6",
                confirmButtonText: "OK"
            });
        @endif
    </script>
    <script>
        @if ($errors->any())
            Swal.fire({
                title: "Gagal!",
                text: "{{ $errors->first() }}",
                icon: "error",
                confirmButtonColor: "#d33",
                confirmButtonText: "Coba Lagi"
            });
        @endif
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectElement = document.querySelector('select[name="item"]');
            if (selectElement) { // Check if the element exists
                selectElement.addEventListener('invalid', function (e) {
                if (e.target.value === "") {
                    e.target.setCustomValidity("Silakan pilih item terlebih dahulu");
                }
                });
                selectElement.addEventListener('input', function (e) {
                e.target.setCustomValidity("");
                });
            }
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function updateTime() {
                const now = new Date();
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                const day = days[now.getDay()];
                const date = now.getDate();
                const month = months[now.getMonth()];
                const year = now.getFullYear();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const seconds = now.getSeconds().toString().padStart(2, '0');

                const currentTimeString = `${day}, ${date} ${month} ${year} ${hours}:${minutes}:${seconds}`;
                
                // FIX: Check if the element exists before trying to update it
                const currentTimeEl = document.getElementById('current-time');
                if (currentTimeEl) {
                    currentTimeEl.innerText = currentTimeString;
                }
            }

            setInterval(updateTime, 1000);
            updateTime(); // Panggilan awal untuk mengatur waktu segera
        });
    </script>
    
    @yield('scripts')
    
    <!-- No more custom calendar scripts needed, Bootstrap handles the modal popup via data attributes -->
</body>

</html>

