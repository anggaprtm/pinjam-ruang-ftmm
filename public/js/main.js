$(document).ready(function () {
    window._token = $('meta[name="csrf-token"]').attr('content');

    moment.locale('id');

    // =================== PERUBAHAN DIMULAI DI SINI ===================

    // 1. Inisialisasi semua tooltip, tapi jangan langsung aktifkan semuanya.
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // 2. Buat fungsi untuk mengaktifkan/menonaktifkan tooltip berdasarkan kondisi sidebar.
    function manageTooltips() {
        const sidebarIsMinimized = document.getElementById('sidebar').classList.contains('c-sidebar-minimized');
        
        tooltipList.forEach(tooltip => {
            if (sidebarIsMinimized) {
                tooltip.enable(); // Jika sidebar minimized, AKTIFKAN tooltip
            } else {
                tooltip.disable(); // Jika sidebar maximized, NONAKTIFKAN tooltip
            }
        });
    }

    // 3. Jalankan fungsi ini saat halaman pertama kali dimuat.
    manageTooltips();

    // =================== AKHIR PERUBAHAN ===================


    // Event handler untuk tombol toggle sidebar
    $('.c-header-toggler.mfs-3.d-md-down-none').click(function (e) {
        $('#sidebar').toggleClass('c-sidebar-minimized');

        // Simpan status baru ke localStorage
        if ($('#sidebar').hasClass('c-sidebar-minimized')) {
            localStorage.setItem('sidebar_minimized', 'true');
        } else {
            localStorage.removeItem('sidebar_minimized');
        }
        
        // 4. Jalankan kembali fungsi setiap kali sidebar di-toggle.
        manageTooltips();

        setTimeout(function () {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        }, 400);
    });


    // --- Sisa kode Anda (tidak perlu diubah) ---

    $('.date').datetimepicker({
        format: 'D MMM YYYY',
        locale: 'id',
        icons: {
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right'
        }
    });

    $('.datetime').datetimepicker({
        minDate: moment(),
        format: 'D MMM YYYY HH:mm',
        locale: 'id',
        sideBySide: true,
        icons: {
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right'
        },
        stepping: 1
    });

    $('.timepicker').datetimepicker({
        format: 'HH:mm',
        icons: {
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right'
        }
    });

    $('#waktu_mulai').on('dp.change', function(e) {
        var waktuMulai = e.date;
        $('#waktu_selesai').data("DateTimePicker").minDate(waktuMulai);
    });

    $('#waktu_selesai').on('dp.change', function(e) {
        var waktuMulai = $('#waktu_mulai').data("DateTimePicker").date();
        var waktuSelesai = e.date;

        if (waktuSelesai.isBefore(waktuMulai)) {
            alert("Waktu selesai tidak boleh lebih awal dari waktu mulai.");
            $('#waktu_selai').data("DateTimePicker").clear();
        }
    });

    $('.select-all').click(function () {
        let $select2 = $(this).parent().siblings('.select2')
        $select2.find('option').prop('selected', 'selected')
        $select2.trigger('change')
    });
    
    $('.deselect-all').click(function () {
        let $select2 = $(this).parent().siblings('.select2')
        $select2.find('option').prop('selected', '')
        $select2.trigger('change')
    });

    $('.select2').select2();

    $('.treeview').each(function () {
        var shouldExpand = false
        $(this).find('li').each(function () {
            if ($(this).hasClass('active')) {
                shouldExpand = true
            }
        })
        if (shouldExpand) {
            $(this).addClass('active')
        }
    });
});