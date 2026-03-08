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
use App\Http\Controllers\Admin\SikApplicationController;
use App\Http\Controllers\Admin\SikVerificationFlowController;
use App\Http\Controllers\Admin\OrmawaProgramPlanController;
use App\Http\Controllers\Admin\OrmawaController;
use App\Http\Controllers\Admin\JenisOrmawaController;



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

    Route::resource('dosen', DosenController::class);
    Route::resource('tendik', TendikController::class);

    // Master Ormawa & Proker
    Route::resource('jenis-ormawas', JenisOrmawaController::class)->except(['show'])->parameters(['jenis-ormawas' => 'jenisOrmava']);
    Route::resource('ormawas-master', OrmawaController::class)->except(['show']);
    Route::resource('ormawa-plans', OrmawaProgramPlanController::class)->except(['show'])->parameters(['ormawa-plans' => 'ormawaPlan']);
    Route::post('ormawa-plans/{ormawaPlan}/items', [OrmawaProgramPlanController::class, 'storeItem'])->name('ormawa-plans.items.store');
    Route::put('ormawa-plans/{ormawaPlan}/items/{item}', [OrmawaProgramPlanController::class, 'updateItem'])->name('ormawa-plans.items.update');
    Route::delete('ormawa-plans/{ormawaPlan}/items/{item}', [OrmawaProgramPlanController::class, 'destroyItem'])->name('ormawa-plans.items.destroy');
    Route::post('ormawa-plans/{ormawaPlan}/import-items', [OrmawaProgramPlanController::class, 'importItems'])->name('ormawa-plans.items.import');
    Route::get('ormawa-plans-template', [OrmawaProgramPlanController::class, 'downloadTemplate'])->name('ormawa-plans.template');

    // SIK Proker Ormawa
    Route::resource('sik-flows', SikVerificationFlowController::class)->except(['show'])->parameters(['sik-flows' => 'sikFlow']);

    Route::get('sik/active-prokers', [SikApplicationController::class, 'activeProgramItems'])->name('sik.activeProkers');
    Route::get('sik-applications', [SikApplicationController::class, 'index'])->name('sik.index');
    Route::get('sik-applications/create', [SikApplicationController::class, 'create'])->name('sik.create');
    Route::get('sik-applications/{sikApplication}', [SikApplicationController::class, 'show'])->name('sik.show');
<<<<<<< HEAD
=======
    Route::get('sik-applications/{sikApplication}/edit', [SikApplicationController::class, 'edit'])->name('sik.edit');
    Route::put('sik-applications/{sikApplication}', [SikApplicationController::class, 'update'])->name('sik.update');
>>>>>>> origin/codex/understand-application-architecture-and-business-flow-4inpv8
    Route::post('sik-applications', [SikApplicationController::class, 'store'])->name('sik.store');
    Route::post('sik-applications/{sikApplication}/process-step', [SikApplicationController::class, 'processStep'])->name('sik.processStep');
    Route::post('sik-applications/{sikApplication}/amendments', [SikApplicationController::class, 'requestAmendment'])->name('sik.amendments.request');
    Route::post('sik-applications/{sikApplication}/amendments/toggle-access', [SikApplicationController::class, 'toggleAmendmentAccess'])->name('sik.amendments.toggleAccess');
    Route::post('sik-applications/{sikApplication}/amendments/{amendment}/process', [SikApplicationController::class, 'processAmendment'])->name('sik.amendments.process');
    Route::post('sik-applications/{sikApplication}/issue', [SikApplicationController::class, 'issue'])->name('sik.issue');

    // API Holidays
    Route::get('api/holidays', [CalendarViewController::class, 'getHolidays'])->name('api.holidays');
});

Route::get('/dashboard-signage', function () {
    return view('signage');
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
