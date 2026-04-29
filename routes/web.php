<?php

use Illuminate\Support\Facades\Route;

// === Import controller FQCN (Laravel 11)
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RuanganController;
use App\Http\Controllers\Admin\BarangController;
use App\Http\Controllers\Admin\JadwalPerkuliahanController;
use App\Http\Controllers\Admin\JadwalPerkuliahanTemplateExportController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\SystemCalendarController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\CalendarViewController;
use App\Http\Controllers\Admin\MobilController;
use App\Http\Controllers\Admin\HariLiburController; 
use App\Http\Controllers\Admin\KioskController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Admin\RiwayatPerjalananController;
use App\Http\Controllers\Admin\PermintaanKegiatanController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\BotSettingController;
use App\Http\Controllers\Admin\PeriodeJamKerjaController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\TendikController;
use App\Http\Controllers\Admin\DisplayConfigController;
use App\Http\Controllers\Admin\DisplayContentController;
use App\Http\Controllers\Admin\DisplayScheduleController;
use App\Http\Controllers\Admin\DeviceCommandController;
use App\Http\Controllers\Admin\AgendaFakultasController;
use App\Http\Controllers\Admin\AsetFakultasController;
use App\Http\Controllers\Admin\LemburKegiatanController;
use App\Http\Controllers\Admin\SuratUndanganController;
use App\Http\Controllers\Admin\SuratTugasController;
use App\Http\Controllers\Admin\ProductivityController;
use App\Http\Controllers\Admin\CentralTicketController;
use App\Http\Controllers\Admin\ProductivityRoutineController;
use App\Http\Controllers\Admin\JadwalWfhController;


Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('book-ruang-landing', [LandingController::class, 'bookRuang'])->middleware(['auth'])->name('landing.bookRuang');

Route::get('/dashboard', function () {
    return redirect()->route('admin.home'); 
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }
    return redirect()->route('admin.home');
});

// Kiosk Mode Vertical Signage (Lt.10)
Route::get('kiosk', [KioskController::class, 'index'])->name('kiosk');
Route::get('api/kiosk/events', [KioskController::class, 'events'])->middleware(['auth'])->name('api.kiosk.events');

// === Grup ADMIN (prefix + name + middleware=auth)
Route::middleware(['auth'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Kegiatan
    Route::get('kegiatan/template', [KegiatanController::class, 'downloadTemplate'])->name('kegiatan.template');
    Route::post('kegiatan/import', [KegiatanController::class, 'import'])->name('kegiatan.import');
    Route::delete('kegiatan/destroy', [KegiatanController::class, 'massDestroy'])->name('kegiatan.massDestroy');
    Route::resource('kegiatan', KegiatanController::class);
    Route::patch('kegiatan/{kegiatan}/status', [KegiatanController::class, 'updateStatus'])
        ->name('kegiatan.updateStatus') // jangan "admin.kegiatan..." karena sudah ada as('admin.')
        ->middleware('role.verification');
    Route::get('kegiatan/{kegiatan}/edit-surat-izin', [KegiatanController::class, 'editSuratIzin'])->name('kegiatan.editSuratIzin');
    Route::patch('kegiatan/{kegiatan}/update-surat-izin', [KegiatanController::class, 'updateSuratIzin'])->name('kegiatan.updateSuratIzin');
    Route::post('kegiatan/{kegiatan}/pinjam-barang', [KegiatanController::class, 'pinjamBarang'])->name('kegiatan.pinjamBarang');
    Route::post('kegiatan/{kegiatan}/kembalikan-barang/{barang}', [KegiatanController::class, 'kembalikanBarang'])->name('kegiatan.kembalikanBarang');

    // Permissions
    Route::delete('permissions/destroy', [PermissionsController::class, 'massDestroy'])->name('permissions.massDestroy');
    Route::resource('permissions', PermissionsController::class);

    // Roles
    Route::delete('roles/destroy', [RolesController::class, 'massDestroy'])->name('roles.massDestroy');
    Route::resource('roles', RolesController::class);

    // Users
    Route::delete('users/destroy', [UsersController::class, 'massDestroy'])->name('users.massDestroy');
    Route::resource('users', UsersController::class);

    // Ruangan
    Route::delete('ruangan/destroy', [RuanganController::class, 'massDestroy'])->name('ruangan.massDestroy');
    Route::resource('ruangan', RuanganController::class);
    Route::patch('ruangan/{id}/toggle', [RuanganController::class, 'toggle'])->name('ruangan.toggle');
    Route::post('ruangan/storeMedia', [RuanganController::class, 'storeMedia'])->name('ruangan.storeMedia');

    // Barangs
    Route::get('barangs/master', [BarangController::class, 'master'])->name('barangs.master');
    Route::delete('barangs/destroy', [BarangController::class, 'massDestroy'])->name('barangs.massDestroy');
    Route::resource('barangs', BarangController::class);

    // Jadwal Perkuliahan
    Route::get('jadwal-perkuliahan/monitoring', [JadwalPerkuliahanController::class, 'monitoring'])->name('jadwal-perkuliahan.monitoring');
    Route::get('jadwal-perkuliahan/template', [JadwalPerkuliahanTemplateExportController::class, 'export'])->name('jadwal-perkuliahan.template');
    Route::delete('jadwal-perkuliahan/destroy', [JadwalPerkuliahanController::class, 'massDestroy'])->name('jadwal-perkuliahan.massDestroy');
    Route::resource('jadwal-perkuliahan', JadwalPerkuliahanController::class);
    Route::post('jadwal-perkuliahan/import', [JadwalPerkuliahanController::class, 'import'])->name('jadwal-perkuliahan.import');
    Route::resource('semesters', 'Admin\SemesterController');

    // Kalender & Booking
    Route::get('kalender', [SystemCalendarController::class, 'index'])->name('systemCalendar');
    Route::get('cari-ruang', [BookingsController::class, 'cariRuang'])->name('cariRuang');
    Route::post('book-ruang', [BookingsController::class, 'bookRuang'])->name('bookRuang');
    
    Route::delete('mobils/destroy', [MobilController::class, 'massDestroy'])->name('mobils.massDestroy');
    Route::resource('mobils', MobilController::class);
    Route::delete('riwayat-perjalanan/mass-destroy', [RiwayatPerjalananController::class, 'massDestroy'])
        ->name('riwayat-perjalanan.massDestroy');
    Route::resource('riwayat-perjalanan', RiwayatPerjalananController::class)->parameters([
        'riwayat-perjalanan' => 'riwayat_perjalanan' // Memastikan parameter binding benar
        ]);
    Route::patch('riwayat-perjalanan/{riwayat_perjalanan}/selesai', [RiwayatPerjalananController::class, 'selesaikan'])->name('riwayat-perjalanan.selesaikan');
    Route::patch('riwayat-perjalanan/{riwayat_perjalanan}/mulai', [RiwayatPerjalananController::class, 'mulaiJalan'])->name('riwayat-perjalanan.mulai');
    Route::post('riwayat-perjalanan/bbm', [RiwayatPerjalananController::class, 'storeBbm'])->name('riwayat-perjalanan.storeBbm');
    Route::delete('riwayat-perjalanan/bbm/{id}', [RiwayatPerjalananController::class, 'destroyBbm'])->name('riwayat-perjalanan.destroyBbm');

    // Statistik (hanya untuk admin/home_access)
    Route::get('statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])
        ->name('statistics.index');
    Route::get('statistics/export-excel', [App\Http\Controllers\Admin\StatisticsController::class, 'exportExcel'])
        ->name('statistics.exportExcel');
    // Route Permintaan Kegiatan
    Route::post(
        'permintaan-kegiatan/{permintaan_kegiatan}/proses-konsumsi',
        [PermintaanKegiatanController::class, 'prosesKonsumsi']
    )->name('permintaan-kegiatan.prosesKonsumsi');

    Route::resource('permintaan-kegiatan', PermintaanKegiatanController::class);
    Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::resource('periode-jam-kerja', PeriodeJamKerjaController::class);
    Route::resource('hari-libur', HariLiburController::class)->except(['show', 'edit', 'update']);
    Route::get('bot-setting', [BotSettingController::class, 'index'])->name('bot-setting.index');
    Route::post('bot-setting', [BotSettingController::class, 'update'])->name('bot-setting.update');
    Route::post('absensi/sync', [AbsensiController::class, 'sync'])->name('absensi.sync');
    Route::get('absensi/rekap-telat', [AbsensiController::class, 'rekapTelat'])->name('absensi.rekap-telat');
    Route::get('absensi/rekap-lembur', [AbsensiController::class, 'rekapLembur'])->name('absensi.rekap-lembur');

    Route::resource('dosen', DosenController::class);
    Route::resource('tendik', TendikController::class);
    Route::patch('display-config/{id}/toggle', [DisplayConfigController::class, 'toggle'])->name('display-config.toggle');
    Route::resource('display-config', DisplayConfigController::class);
    Route::post('display-content', [DisplayContentController::class, 'store'])->name('display-content.store');
    Route::delete('display-content/{id}', [DisplayContentController::class, 'destroy'])->name('display-content.destroy');
    Route::post('display-content/reorder', [DisplayContentController::class, 'reorder'])->name('display-content.reorder');
    Route::post('display-schedule', [DisplayScheduleController::class, 'store'])->name('display-schedule.store');
    Route::delete('display-schedule/{id}', [DisplayScheduleController::class, 'destroy'])->name('display-schedule.destroy');
    Route::get('device-command', [DeviceCommandController::class, 'index'])->name('device-command.index');
    Route::post('device-command', [DeviceCommandController::class, 'store'])->name('device-command.store');
    Route::resource('agenda-fakultas', AgendaFakultasController::class)->parameters(['agenda-fakultas' => 'agendaFakultas']);
    Route::delete('aset-fakultas/destroy', [AsetFakultasController::class, 'massDestroy'])->name('aset-fakultas.massDestroy');
    Route::get('aset-fakultas/import', [AsetFakultasController::class, 'importForm'])->name('aset-fakultas.import.form');
    Route::post('aset-fakultas/import', [AsetFakultasController::class, 'import'])->name('aset-fakultas.import');
    Route::get('aset-fakultas/export-pdf', [AsetFakultasController::class, 'exportPdf'])->name('aset-fakultas.export-pdf');
    Route::post('aset-fakultas/export-zip', [AsetFakultasController::class, 'exportZip'])->name('aset-fakultas.export-zip');
    Route::post('aset-fakultas/mass-move', [AsetFakultasController::class, 'massMove'])->name('aset-fakultas.mass-move');
    Route::resource('aset-fakultas', AsetFakultasController::class)->parameters(['aset-fakultas' => 'asetFakultas']);
    Route::resource('lembur-kegiatan', LemburKegiatanController::class)->names('lembur-kegiatan');
    Route::resource('jadwal-wfh', JadwalWfhController::class)->names('jadwal-wfh');
    Route::prefix('lembur-kegiatan/{lemburKegiatan}')->name('admin.lembur-kegiatan.')->group(function () {
        Route::post('assign',         [LemburKegiatanController::class, 'assignPegawai'])->name('assign');
        Route::post('remove-pegawai', [LemburKegiatanController::class, 'removePegawai'])->name('remove-pegawai');
    });
    Route::get('lembur-kegiatan-rekap-keuangan', [LemburKegiatanController::class, 'rekapKeuangan'])
        ->name('lembur-kegiatan.rekap-keuangan');

    Route::get('central-tickets', [CentralTicketController::class, 'index'])->name('central-tickets.index');
    Route::get('central-tickets/{id}', [CentralTicketController::class, 'show'])->name('central-tickets.show');
    Route::post('central-tickets/{id}/reply', [CentralTicketController::class, 'storeReply'])->name('central-tickets.reply');
    Route::delete('/central-tickets/{id}', [CentralTicketController::class, 'destroy'])->name('central-tickets.destroy');
    
    // ──────────────────────────────────────
    // SURAT UNDANGAN
    // ──────────────────────────────────────
    Route::prefix('surat-undangan')->name('surat-undangan.')->group(function () {
    
        // Rute kustom SEBELUM resource (agar tidak bentrok)
        Route::post('preview',           [SuratUndanganController::class, 'preview'])->name('preview');
        Route::get('{suratUndangan}/download', [SuratUndanganController::class, 'download'])->name('download');
    
        // Resource: index, create, store, edit, update, destroy
        Route::resource('/', SuratUndanganController::class)
            ->parameters(['' => 'suratUndangan'])
            ->except(['show']);
    });
    
    // ──────────────────────────────────────
    // SURAT TUGAS
    // ──────────────────────────────────────
    Route::prefix('surat-tugas')->name('surat-tugas.')->group(function () {
        Route::delete('destroy', [SuratTugasController::class, 'massDestroy'])->name('massDestroy');
        // ── Custom routes SEBELUM resource ──
        Route::post('preview',                        [SuratTugasController::class, 'preview'])->name('preview');
        Route::get('{suratTugas}/download',           [SuratTugasController::class, 'download'])->name('download');
        Route::post('{suratTugas}/update-nomor',      [SuratTugasController::class, 'updateNomor'])->name('update-nomor');
    
        // ── Resource ──
        Route::resource('/', SuratTugasController::class)
            ->parameters(['' => 'suratTugas'])
            ->except(['show']);
    });

    // ==========================================
    // PRODUCTIVITY WORKSPACE ROUTES
    // ==========================================
    Route::prefix('productivity')->name('productivity.')->group(function () {
        Route::get('/', [ProductivityController::class, 'index'])->name('index');
        Route::post('/tasks', [ProductivityController::class, 'storeTask'])->name('tasks.store');
        Route::patch('/tasks/{id}/status', [ProductivityController::class, 'updateTaskStatus'])->name('tasks.status');
        Route::delete('/tasks/{id}', [ProductivityController::class, 'destroyTask'])->name('tasks.destroy');
        Route::patch('/tasks/{id}',           [ProductivityController::class, 'updateTask'])->name('tasks.update');
        Route::patch('/tasks/{id}/archive',   [ProductivityController::class, 'archiveTask'])->name('tasks.archive');
        Route::patch('/tasks/{id}/unarchive', [ProductivityController::class, 'unarchiveTask'])->name('tasks.unarchive');
        
        Route::post('/notes', [ProductivityController::class, 'storeNote'])->name('notes.store');
        Route::delete('/notes/{id}', [ProductivityController::class, 'destroyNote'])->name('notes.destroy');
        
        Route::post('/habits', [ProductivityController::class, 'storeHabit'])->name('habits.store');
        Route::post('/habits/{id}/toggle', [ProductivityController::class, 'toggleHabit'])->name('habits.toggle');
        Route::delete('/habits/{id}', [ProductivityController::class, 'destroyHabit'])->name('habits.destroy');
        Route::post('/settings', [ProductivityController::class, 'updateSettings'])->name('settings.update');
        Route::patch('/notes/{id}', [ProductivityController::class, 'updateNote'])->name('notes.update');
        Route::post('/tasks/{task}/subtasks', [ProductivityController::class, 'storeSubTask']);
        Route::patch('/tasks/{task}/subtasks/{subtask}/toggle', [ProductivityController::class, 'toggleSubTask']);
        Route::delete('/tasks/{task}/subtasks/{subtask}', [ProductivityController::class, 'destroySubTask']);
        Route::post('/tasks/{task}/attachments', [ProductivityController::class, 'storeAttachment']);
        Route::delete('/tasks/{task}/attachments/{attachment}', [ProductivityController::class, 'destroyAttachment']);
        Route::post('/tasks/{task}/comments', [ProductivityController::class, 'storeComment']);
        Route::get('/routine', [ProductivityRoutineController::class, 'index'])->name('routine.index');
        Route::post('/routine', [ProductivityRoutineController::class, 'store'])->name('routine.store');
        Route::post('/routine/{task}/submit', [ProductivityRoutineController::class, 'submitLog'])->name('routine.submit');
        Route::put('/routine/{task}', [ProductivityRoutineController::class, 'update'])->name('routine.update');
        Route::patch('/routine/log/{log}/verify', [ProductivityRoutineController::class, 'verifyLog'])->name('routine.verify');
    });

    // API Holidays
    Route::get('api/holidays', [CalendarViewController::class, 'getHolidays'])->name('api.holidays');
});

Route::get('/dashboard-signage', function () {
    return view('signage');
});
Route::get('/signage-dekan', function () {
    return view('signage-dekan');
});

// === Grup PROFILE (Change Password) dengan pengecekan file controller (sesuai rute lama)
Route::middleware(['auth'])->prefix('profile')->as('profile.')->group(function () {
    if (class_exists(ChangePasswordController::class)) {
        Route::get('password', [ChangePasswordController::class, 'edit'])->name('password.edit');
        Route::post('password', [ChangePasswordController::class, 'update'])->name('password.update');
        Route::post('profile', [ChangePasswordController::class, 'updateProfile'])->name('password.updateProfile');
        Route::post('profile/destroy', [ChangePasswordController::class, 'destroy'])->name('password.destroyProfile');
    }
});

// === Rute auth dari Breeze (gantikan Auth::routes())
require __DIR__.'/auth.php';
