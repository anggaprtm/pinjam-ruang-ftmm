$(document).ready(function () {
    window._token = $('meta[name="csrf-token"]').attr('content')
  
    moment.locale('id');
  
    $('.date').datetimepicker({
      format: 'D MMM YYYY',
      locale: 'id',
      icons: {
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right'
      }
    })
  
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
    })
  
    $('.timepicker').datetimepicker({
      format: 'HH:mm',
      icons: {
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right'
      }
    })

    $('#waktu_mulai').on('dp.change', function(e) {
      var waktuMulai = e.date;
      $('#waktu_selesai').data("DateTimePicker").minDate(waktuMulai); // Atur waktu minimum untuk waktu_selesai
   });

    $('#waktu_selesai').on('dp.change', function(e) {
      var waktuMulai = $('#waktu_mulai').data("DateTimePicker").date();
      var waktuSelesai = e.date;

      if (waktuSelesai.isBefore(waktuMulai)) {
          alert("Waktu selesai tidak boleh lebih awal dari waktu mulai.");
          $('#waktu_selesai').data("DateTimePicker").clear(); // Mengosongkan input waktu selesai
      }
    });
  
    $('.select-all').click(function () {
      let $select2 = $(this).parent().siblings('.select2')
      $select2.find('option').prop('selected', 'selected')
      $select2.trigger('change')
    })
    $('.deselect-all').click(function () {
      let $select2 = $(this).parent().siblings('.select2')
      $select2.find('option').prop('selected', '')
      $select2.trigger('change')
    })
  
    $('.select2').select2()
  
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
    })
  
    $('.c-header-toggler.mfs-3.d-md-down-none').click(function (e) {
      $('#sidebar').toggleClass('c-sidebar-lg-show');
  
      setTimeout(function () {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      }, 400);
    });
  
  })
