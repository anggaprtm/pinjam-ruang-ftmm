/**
 * main.js — CoreUI v4 + Bootstrap 5 + jQuery
 */

$(document).ready(function () {

    window._token = $('meta[name="csrf-token"]').attr('content');
    moment.locale('id');

    /* =========================================================================
       SIDEBAR TOGGLE
       CoreUI v4 handle dropdown nav-group secara native via data-coreui="navigation".
       Kita hanya perlu handle: narrow toggle + mobile show/hide + localStorage.
    ========================================================================= */

    const $sidebar   = $('#sidebar');
    const NARROW_KEY = 'sidebar_narrow';

    const isMobile = () => window.innerWidth < 992;

    // Restore state narrow di desktop
    if (!isMobile() && localStorage.getItem(NARROW_KEY) === 'true') {
        $sidebar.addClass('sidebar-narrow');
    }

    // Toggle desktop (hamburger kiri header)
    $('#sidebarToggleDesktop').on('click', function () {
        $sidebar.toggleClass('sidebar-narrow');
        localStorage.setItem(NARROW_KEY, $sidebar.hasClass('sidebar-narrow') ? 'true' : 'false');
        setTimeout(function () {
            if ($.fn.DataTable) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            }
        }, 350);
    });

    // Toggle mobile
    $('#sidebarToggleMobile').on('click', function () {
        const isOpen = $sidebar.hasClass('show');
        if (!isOpen) {
            $sidebar.addClass('show');
            $('<div id="sidebarBackdrop"></div>').appendTo('body').on('click', closeMobileSidebar);
        } else {
            closeMobileSidebar();
        }
    });

    function closeMobileSidebar() {
        $sidebar.removeClass('show');
        $('#sidebarBackdrop').remove();
    }

    // Tutup sidebar mobile saat link diklik
    $sidebar.on('click', '.nav-link:not(.nav-group-toggle)', function () {
        if (isMobile()) closeMobileSidebar();
    });

    const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });


    /* =========================================================================
       DATETIME PICKER — Tempus Dominus v6
    ========================================================================= */

    if (typeof tempusDominus !== 'undefined') {

        document.querySelectorAll('.date').forEach(function (el) {
            if (el._tdp) return;
            el._tdp = new tempusDominus.TempusDominus(el, {
                display: { viewMode: 'calendar', components: { clock: false } },
                localization: { locale: 'id', format: 'dd MMM yyyy' }
            });
        });

        document.querySelectorAll('.datetime').forEach(function (el) {
            if (el._tdp) return;
            el._tdp = new tempusDominus.TempusDominus(el, {
                restrictions: { minDate: new tempusDominus.DateTime() },
                display: { sideBySide: true, components: { seconds: false } },
                localization: { locale: 'id', format: 'dd MMM yyyy HH:mm' }
            });
        });

        document.querySelectorAll('.timepicker').forEach(function (el) {
            if (el._tdp) return;
            el._tdp = new tempusDominus.TempusDominus(el, {
                display: { viewMode: 'clock', components: { calendar: false, seconds: false } },
                localization: { format: 'HH:mm' }
            });
        });

        // Validasi waktu mulai < waktu selesai
        const elMulai   = document.getElementById('waktu_mulai');
        const elSelesai = document.getElementById('waktu_selesai');
        if (elMulai && elSelesai) {
            elMulai.addEventListener(tempusDominus.Namespace.events.change, function (e) {
                if (e.detail.date) elSelesai._tdp?.updateOptions({ restrictions: { minDate: e.detail.date } });
            });
            elSelesai.addEventListener(tempusDominus.Namespace.events.change, function (e) {
                const pickedMulai = elMulai._tdp?.dates?.picked?.[0];
                if (pickedMulai && e.detail.date && e.detail.date < pickedMulai) {
                    alert('Waktu selesai tidak boleh lebih awal dari waktu mulai.');
                    elSelesai._tdp?.clear();
                }
            });
        }
    }


    /* =========================================================================
       SELECT2
    ========================================================================= */

    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        $('.select-all').on('click', function () {
            const $s = $(this).parent().siblings('.select2');
            $s.find('option').prop('selected', true).end().trigger('change');
        });
        $('.deselect-all').on('click', function () {
            const $s = $(this).parent().siblings('.select2');
            $s.find('option').prop('selected', false).end().trigger('change');
        });
    }


    /* =========================================================================
       TREEVIEW
    ========================================================================= */

    $('.treeview').each(function () {
        if ($(this).find('li.active').length) $(this).addClass('active');
    });

});